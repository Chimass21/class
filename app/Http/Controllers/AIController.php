<?php

namespace App\Http\Controllers;


use App\Helpers\ContentGenerator;
use App\Helpers\CurriculumData;
use App\Helpers\JsonDb;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AIController extends Controller
{
    protected OpenAIService $ai;
    protected const MAX_RETRIES = 2;

    public function __construct(OpenAIService $ai)
    {
        $this->ai = $ai;
    }

    public function generateLessonPlan(Request $request)
    {
        try {
            $data = $request->validate([
                'subject' => 'required|string',
                'class' => 'required|string',
                'term' => 'required|string',
                'week' => 'required|integer|min:1|max:13',
                'topic' => 'required|string',
                'subTopic' => 'nullable|string',
                'schoolName' => 'nullable|string',
                'teacherName' => 'nullable|string',
                'duration' => 'nullable|string',
            ]);

            $user = Session::get('user');
            $teacherName = $data['teacherName'] ?? $user['name'] ?? 'Teacher';
            $schoolName = $data['schoolName'] ?? 'Covenant Foundation School';
            $duration = $data['duration'] ?? '40 Minutes';
            $ageRange = CurriculumData::getAgeRange($data['class']);
            $scheme = CurriculumData::getSchemeOfWork($data['subject'], $data['class'], $data['term']);

            $prompt = $this->buildLessonPlanPrompt(
                $data['subject'], $data['class'], $data['term'], $data['week'],
                $data['topic'], $schoolName, $teacherName, $duration, $ageRange, $scheme,
                $data['subTopic'] ?? ''
            );

            Log::info('AI Lesson Plan Request', [
                'subject' => $data['subject'],
                'class' => $data['class'],
                'topic' => $data['topic'],
                'subTopic' => $data['subTopic'] ?? '',
                'prompt_length' => strlen($prompt),
            ]);

            $response = $this->ai->generate($prompt, true);

            Log::info('AI Lesson Plan Response', [
                'response_length' => strlen($response),
                'response_preview' => substr($response, 0, 500),
            ]);

            if ($this->isRefusal($response)) {
                Log::warning('AI refused lesson plan request', ['topic' => $data['topic']]);
                return response()->json([
                    'success' => false,
                    'error' => 'The AI model declined to generate content for this topic. Please rephrase your topic or try a different subject.',
                ], 422);
            }

            $plan = json_decode($response, true);

            if (!is_array($plan) || empty($plan)) {
                $cleaned = $this->extractJson($response);
                if ($cleaned !== null) {
                    $plan = $cleaned;
                }
            }

            if (!is_array($plan) || empty($plan)) {
                Log::warning('AI returned non-JSON response for lesson plan', [
                    'response' => substr($response, 0, 1000),
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to generate a valid lesson plan. The AI response was not in the expected format. Please try again.' . $this->aiErrorSuffix(),
                ], 422);
            }

            if (!$this->isRelevantToTopic($plan, 'lesson_plan', $data['subject'], $data['topic'], $data['class'])) {
                Log::warning('Lesson plan rejected - not relevant to topic', [
                    'subject' => $data['subject'],
                    'topic' => $data['topic'],
                ]);

                if (self::MAX_RETRIES > 0) {
                    $retryResponse = $this->ai->generate($this->buildStrictRetryPrompt($prompt, $data['subject'], $data['topic'], $data['class']), true);

                    Log::info('AI Lesson Plan Retry Response', [
                        'response_length' => strlen($retryResponse),
                        'response_preview' => substr($retryResponse, 0, 500),
                    ]);

                    if ($this->isRefusal($retryResponse)) {
                        return response()->json([
                            'success' => false,
                            'error' => 'The AI model declined to generate content for this topic. Please rephrase your topic.',
                        ], 422);
                    }

                    $plan = json_decode($retryResponse, true);
                    if (!is_array($plan) || empty($plan)) {
                        $cleaned = $this->extractJson($retryResponse);
                        if ($cleaned !== null) {
                            $plan = $cleaned;
                        }
                    }

                    if (is_array($plan) && !empty($plan) && $this->isRelevantToTopic($plan, 'lesson_plan', $data['subject'], $data['topic'], $data['class'])) {
                        return $this->storeAndReturnLessonPlan($plan, $data, $user, $teacherName, $schoolName, $duration, $ageRange);
                    }
                }

                return response()->json([
                    'success' => false,
                    'error' => 'The generated lesson plan did not focus on the requested topic. Please try again with a more specific topic.',
                ], 422);
            }

            return $this->storeAndReturnLessonPlan($plan, $data, $user, $teacherName, $schoolName, $duration, $ageRange);

        } catch (\Exception $e) {
            Log::error('Lesson plan generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function generateLessonNote(Request $request)
    {
        try {
            $data = $request->validate([
                'subject' => 'required|string',
                'class' => 'nullable|string',
                'term' => 'nullable|string',
                'week' => 'nullable|integer|min:1|max:13',
                'topic' => 'required|string',
                'subTopic' => 'nullable|string',
                'difficulty' => 'nullable|string',
                'periods' => 'nullable|string',
                'subtopics' => 'nullable|string',
            ]);

            $user = Session::get('user');
            $data['class'] = $data['class'] ?? 'SS1';
            $data['term'] = $data['term'] ?? 'First Term';
            $data['week'] = $data['week'] ?? 1;
            $difficulty = $data['difficulty'] ?? 'Standard';
            $periods = $data['periods'] ?? '2 Periods';
            $ageRange = CurriculumData::getAgeRange($data['class']);
            $scheme = CurriculumData::getSchemeOfWork($data['subject'], $data['class'], $data['term']);
            $userSubtopics = $data['subtopics'] ?? '';

            // Try up to 2 times: first with full prompt, then with stricter retry
            for ($attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++) {
                if ($attempt === 0) {
                    $prompt = $this->buildLessonNotePrompt(
                        $data['subject'], $data['class'], $data['term'], $data['week'],
                        $data['topic'], $periods, $difficulty, $ageRange, $scheme, $userSubtopics
                    );
                } else {
                    $prompt = $this->buildStrictRetryPrompt($prompt, $data['subject'], $data['topic'], $data['class'], 'lesson_note');
                }

                Log::info("AI Lesson Note Request (attempt {$attempt})", [
                    'subject' => $data['subject'],
                    'class' => $data['class'],
                    'topic' => $data['topic'],
                    'prompt_length' => strlen($prompt),
                ]);

                $response = $this->ai->generate($prompt, true);

                Log::info("AI Lesson Note Response (attempt {$attempt})", [
                    'response_length' => strlen($response),
                    'response_preview' => substr($response, 0, 500),
                ]);

                if ($this->isRefusal($response)) {
                    Log::warning('AI refused lesson note request', ['topic' => $data['topic']]);
                    if ($attempt < self::MAX_RETRIES) continue;
                    return response()->json([
                        'success' => false,
                        'error' => 'The AI model declined to generate content for this topic. Please rephrase your topic or try a different subject.',
                    ], 422);
                }

                $note = json_decode($response, true);
                if (!is_array($note) || empty($note)) {
                    $cleaned = $this->extractJson($response);
                    if ($cleaned !== null) {
                        $note = $cleaned;
                    }
                }

                if (!is_array($note) || empty($note)) {
                    Log::warning("AI returned non-JSON for lesson note (attempt {$attempt})");
                    if ($attempt < self::MAX_RETRIES) continue;
                    $errorMsg = 'Failed to generate a valid lesson note. ';
                    if (empty(trim($response))) {
                        $errorMsg .= 'The AI service returned an empty response. Please check your API configuration and try again.';
                    } elseif (strlen($response) > 20000) {
                        $errorMsg .= 'The AI response was too large. Try a more specific topic.';
                    } else {
                        $errorMsg .= 'The AI response was not in the expected format. Please try again.';
                    }
                    return response()->json([
                        'success' => false,
                        'error' => $errorMsg . $this->aiErrorSuffix(),
                    ], 422);
                }

                if ($this->isRelevantToTopic($note, 'lesson_note', $data['subject'], $data['topic'], $data['class'])) {
                    return $this->storeAndReturnLessonNote($note, $data, $user, $periods, $difficulty, $ageRange);
                }

                Log::warning("Lesson note rejected - not relevant to topic (attempt {$attempt})", [
                    'subject' => $data['subject'],
                    'topic' => $data['topic'],
                ]);
            }

            // Final fallback: try simpler prompt without json mode
            try {
                $simplePrompt = "Write a detailed lesson note about \"{$data['topic']}\" in {$data['subject']} for {$data['class']}. "
                    . "Analyze the topic first, then choose headings that are naturally relevant. Do NOT force sections that don't belong. "
                    . "Include topic, introduction, and main HTML content as the body. Add definitions, examples, evaluation questions, key points ONLY if they add value. "
                    . "Use clear, globally relatable examples. Return ONLY valid JSON: "
                    . '{"topic":"...","introduction":"...","content":"FULL HTML — <h3>/<h4> headings chosen for this topic (2-3 pages)","sections":[{"heading":"...","content":"..."}],"evaluationQuestions":["..."],"keyPoints":["..."]}';
                $fallbackResponse = $this->ai->generate($simplePrompt, false, 8192, 0.7);
                if (!empty(trim($fallbackResponse))) {
                    $note = json_decode($fallbackResponse, true);
                    if (!is_array($note) || empty($note)) {
                        $cleaned = $this->extractJson($fallbackResponse);
                        if ($cleaned !== null) $note = $cleaned;
                    }
                    if (is_array($note) && !empty($note)) {
                        return $this->storeAndReturnLessonNote($note, $data, $user, $periods, $difficulty, $ageRange);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Lesson note fallback also failed', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => false,
                'error' => 'The generated lesson note did not focus on the requested topic. Please try again with a more specific topic.',
            ], 422);

        } catch (\Exception $e) {
            Log::error('Lesson note generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function generateQuestions(Request $request)
    {
        try {
            $data = $request->validate([
                'subject' => 'required|string',
                'topic' => 'required|string',
                'subTopic' => 'nullable|string',
                'class' => 'nullable|string',
                'classLevel' => 'nullable|string',
                'term' => 'nullable|string',
                'week' => 'nullable|integer',
                'count' => 'required|integer|min:1|max:200',
                'includeTheory' => 'nullable|boolean',
                'lessonNoteId' => 'nullable|string',
                'noteContent' => 'nullable|string',
                'difficulty' => 'nullable|string',
            ]);

            // Normalise frontend fields
            if (empty($data['class']) && !empty($data['classLevel'])) {
                $data['class'] = $data['classLevel'];
            }

            $lessonNoteContent = $data['noteContent'] ?? '';

            // Ensure every generation starts with a completely fresh context
            ContentGenerator::reset();

            $prompt = $this->buildQuestionsPrompt(
                $data['subject'], $data['topic'], $data['count'],
                $data['class'] ?? 'SS1', $data['term'] ?? 'First Term',
                $data['week'] ?? 1, $data['includeTheory'] ?? false, $lessonNoteContent,
                $data['subTopic'] ?? '', $data['difficulty'] ?? 'Standard'
            );

            Log::info('AI Questions Request', [
                'subject' => $data['subject'],
                'topic' => $data['topic'],
                'count' => $data['count'],
                'includeTheory' => $data['includeTheory'] ?? false,
                'hasLessonNote' => !empty($lessonNoteContent),
                'prompt_length' => strlen($prompt),
                'note_content_length' => strlen($lessonNoteContent),
            ]);

            $response = $this->ai->generate($prompt, true, 16384, 0.5);

            Log::info('AI Questions Response', [
                'response_length' => strlen($response),
                'response_preview' => substr($response, 0, 500),
            ]);

            if (empty(trim($response))) {
                Log::warning('AI returned empty response for questions', [
                    'has_lesson_note' => !empty($lessonNoteContent),
                    'prompt_length' => strlen($prompt),
                    'lesson_note_length' => strlen($lessonNoteContent),
                ]);
                return $this->fallbackToContentGenerator($data);
            }

            if ($this->isRefusal($response)) {
                Log::warning('AI refused questions request', [
                    'topic' => $data['topic'],
                    'has_lesson_note' => !empty($lessonNoteContent),
                ]);
                return $this->fallbackToContentGenerator($data);
            }

            $questions = json_decode($response, true);

            if (!is_array($questions) || empty($questions)) {
                $cleaned = $this->extractJson($response);
                if ($cleaned !== null) {
                    $questions = $cleaned;
                }
            }

            if (!is_array($questions) || empty($questions)) {
                Log::warning('AI returned non-JSON for questions', [
                    'response_length' => strlen($response),
                    'response_preview' => substr($response, 0, 3000),
                ]);
                return $this->fallbackToContentGenerator($data);
            }

            $questionsArray = $questions['objectives'] ?? $questions;
            $hasValidFormat = is_array($questionsArray) && !empty($questionsArray) && isset($questionsArray[0]);

            if (!$hasValidFormat) {
                // A truncated response may only have yielded a single complete
                // question object (or the top-level array never closed) — try
                // to salvage every complete question emitted before the cut-off.
                $salvaged = $this->extractAllQuestionObjects($response);
                if (count($salvaged) > 0) {
                    Log::warning('Salvaging questions from truncated AI response', [
                        'salvaged' => count($salvaged),
                        'requested' => $data['count'],
                    ]);
                    $questionsArray = $salvaged;
                    $hasValidFormat = true;
                }
            }

            if (!$hasValidFormat) {
                Log::warning('Questions rejected - invalid format', [
                    'decoded_structure' => is_array($questions) ? array_keys($questions) : 'not_array',
                ]);
                return $this->fallbackToContentGenerator($data);
            }

            $questionItems = isset($questions['objectives']) && is_array($questions['objectives'])
                ? $questions['objectives']
                : $questionsArray;

            // If the response is partial (e.g. the AI hit its output-token
            // limit), skip the expensive AI retry loop — retrying with the same
            // huge target count truncates again. Validate structure only; any
            // stem that does not literally mention the topic is later made
            // on-topic by the prepend mitigation below, preserving the AI's
            // real questions instead of discarding them.
            if (count($questionItems) < (int) $data['count']) {
                Log::warning('AI returned partial questions set', [
                    'received' => count($questionItems),
                    'requested' => (int) $data['count'],
                ]);
                $validated = $this->normalizeQuestionFields($questionItems);
                $validated = $this->filterValidQuestions($validated, $data['topic'], $data['subject'], true, 1) ?? [];
            } else {
                // Validate quality and retry if needed
                $validated = $this->validateAndRetryQuestions($questionItems, $prompt, $data, !empty($lessonNoteContent));
                if ($validated === null) {
                    return $this->fallbackToContentGenerator($data);
                }
            }
            $questionItems = $validated;

            // Top up to the requested count using the offline topic-anchored
            // generator so users always receive their full set of questions.
            $questionItems = $this->topUpQuestionsFromContentGenerator($questionItems, $data);
            if (empty($questionItems)) {
                return $this->fallbackToContentGenerator($data);
            }

            // Normalize field names from various AI output formats
            $questionItems = $this->normalizeQuestionFields($questionItems);

            // Topic relevance check — ensure question stems reference the topic
            $topicKeywords = array_filter(explode(' ', strtolower(trim($data['topic']))), fn($w) => strlen($w) > 2);
            if (empty($topicKeywords)) { $topicKeywords = [strtolower(trim($data['topic']))]; }
            $offTopicCount = 0;
            foreach ($questionItems as $q) {
                $qText = strtolower($q['question'] ?? '');
                $matches = 0;
                foreach ($topicKeywords as $kw) {
                    if (str_contains($qText, $kw)) { $matches++; }
                }
                if ($matches === 0) { $offTopicCount++; }
            }
            if ($offTopicCount > 0) {
                Log::warning("{$offTopicCount} off-topic questions detected — prepending topic to stems", [
                    'topic' => $data['topic'],
                    'total' => count($questionItems),
                ]);
                foreach ($questionItems as $i => $q) {
                    $qText = strtolower($q['question'] ?? '');
                    $matches = 0;
                    foreach ($topicKeywords as $kw) {
                        if (str_contains($qText, $kw)) { $matches++; }
                    }
                    if ($matches === 0) {
                        $questionItems[$i]['question'] = 'In the context of ' . $data['topic'] . ', ' . lcfirst(ltrim($q['question'] ?? '', '?.,;:!'));
                    }
                }
            }

            // Shuffle answers for better distribution
            $questionItems = $this->shuffleAnswers($questionItems);

            // Build response preserving theory questions if present
            $responseData = ['objectives' => $questionItems];
            if (!empty($questions['theoryQuestions'])) {
                $responseData['theoryQuestions'] = $questions['theoryQuestions'];
            }
            if (!empty($questions['essayQuestions'])) {
                $responseData['essayQuestions'] = $questions['essayQuestions'];
            }
            if (!empty($questions['structuredQuestions'])) {
                $responseData['structuredQuestions'] = $questions['structuredQuestions'];
            }

            $actualCount = count($questionItems);
            return response()->json([
                'success' => true,
                'questions' => $responseData,
                'count' => $data['count'],
                'message' => $actualCount . ' out of ' . $data['count'] . ' questions generated successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error('Questions generation failed, retrying with simpler prompt', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->fallbackToContentGenerator($data);
        }
    }

    public function saveGeneratedQuestions(Request $request)
    {
        try {
            $data = $request->validate([
                'subject' => 'required|string',
                'topic' => 'required|string',
                'subTopic' => 'nullable|string',
                'questions' => 'required|array',
                'questions.*.question' => 'required|string',
            ]);

            $questions = $request->input('questions');
            Log::info('Save questions request', [
                'subject' => $data['subject'],
                'topic' => $data['topic'],
                'question_count' => count($questions),
                'sample' => count($questions) > 0 ? array_keys($questions[0] ?? []) : 'empty',
            ]);

            // Normalize question field names before validation
            $questions = $this->normalizeQuestionFields($questions);

            // Validate questions — warn on quality issues but do NOT block save
            $validationErrors = $this->validateQuestionPool($questions, $data['topic'], $data['subject']);
            if (!empty($validationErrors)) {
                Log::warning('Save questions quality warnings (non-blocking)', [
                    'warnings' => $validationErrors,
                    'count' => count($questions),
                ]);
            }

            $user = Session::get('user');
            JsonDb::init();
            $db = JsonDb::get();

            $qsId = 'qs_' . uniqid();
            $qs = [
                'id' => $qsId,
                'teacherId' => $user['id'] ?? 'unknown',
                'source' => 'ai_generated',
                'sourceId' => null,
                'questions' => $questions,
                'subject' => $data['subject'],
                'topic' => $data['topic'],
                'subTopic' => $data['subTopic'] ?? '',
                'createdAt' => now()->toIso8601String(),
            ];
            $db['questionSets'][] = $qs;
            JsonDb::save($db);

            Log::info('Questions saved successfully', ['qsId' => $qsId, 'count' => count($questions)]);

            return response()->json(['success' => true, 'questionSetId' => $qsId, 'message' => 'Questions saved successfully.']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Save questions validation failed', ['errors' => $e->errors()]);
            return response()->json(['success' => false, 'error' => 'Validation error: ' . json_encode($e->errors())], 422);
        } catch (\Exception $e) {
            Log::error('Save questions failed with exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'error' => 'Failed to save questions: ' . $e->getMessage()], 500);
        }
    }

    public function convertQuestionsToExam(Request $request)
    {
        try {
            $data = $request->validate([
                'questionSetId' => 'required|string',
                'title' => 'nullable|string',
                'duration' => 'nullable|integer|min:1|max:180',
                'defaultMarks' => 'nullable|integer|min:1|max:100',
            ]);

            Log::info('Convert to CBT request', ['questionSetId' => $data['questionSetId']]);

            $user = Session::get('user');
            JsonDb::init();
            $db = JsonDb::get();

            $qs = null;
            foreach ($db['questionSets'] as $q) {
                if ($q['id'] === $data['questionSetId']) { $qs = $q; break; }
            }
            if (!$qs) {
                Log::error('Question set not found for conversion', ['id' => $data['questionSetId']]);
                return response()->json(['success' => false, 'error' => 'Question set not found.'], 404);
            }
            if (($user['role'] ?? '') !== 'admin' && ($qs['teacherId'] ?? '') !== ($user['id'] ?? '')) {
                return response()->json(['success' => false, 'error' => 'This question set is private to its owner'], 403);
            }

            // Get all questions — they were already normalized on save
            $allQuestions = $qs['questions'] ?? [];
            if (empty($allQuestions)) {
                Log::error('Question set has no questions', ['id' => $data['questionSetId']]);
                return response()->json(['success' => false, 'error' => 'Question set has no questions.'], 400);
            }

            // Try to find objective questions (have A/B/C/D options)
            $mcq = array_filter($allQuestions, fn($q) => isset($q['A']) || isset($q['B']) || isset($q['C']) || isset($q['D']) || isset($q['options']));
            $mcq = array_values($mcq);

            // If no objective questions found, use ALL questions as-is
            if (empty($mcq)) {
                Log::warning('No objective format detected, using all questions', ['count' => count($allQuestions)]);
                $mcq = $allQuestions;
            }

            $examId = 'exam_' . uniqid();
            $formattedQuestions = [];
            foreach ($mcq as $i => $q) {
                if (!is_array($q)) continue;
                $questionText = $q['question'] ?? $q['text'] ?? $q['stem'] ?? '';
                $formattedQuestions[] = [
                    'id' => $i + 1,
                    'question' => $questionText,
                    'optionA' => $q['A'] ?? $q['options']['A'] ?? $q['option_a'] ?? $q['optionA'] ?? '',
                    'optionB' => $q['B'] ?? $q['options']['B'] ?? $q['option_b'] ?? $q['optionB'] ?? '',
                    'optionC' => $q['C'] ?? $q['options']['C'] ?? $q['option_c'] ?? $q['optionC'] ?? '',
                    'optionD' => $q['D'] ?? $q['options']['D'] ?? $q['option_d'] ?? $q['optionD'] ?? '',
                    'correctAnswer' => $q['answer'] ?? $q['correctAnswer'] ?? $q['correct_answer'] ?? $q['correct'] ?? $q['ans'] ?? 'A',
                ];
            }

            $defaultMarks = $data['defaultMarks'] ?? 1;
            $totalMarks = count($formattedQuestions) * $defaultMarks;
            $duration = $data['duration'] ?? min(30, max(10, intdiv(count($formattedQuestions), 2)));

            $exam = [
                'id' => $examId,
                'title' => $data['title'] ?? ($qs['subject'] ?? 'Generated') . ' CBT Exam',
                'subject' => $qs['subject'] ?? 'General',
                'level' => 'Mixed',
                'topic' => $qs['topic'] ?? '',
                'subTopic' => $qs['subTopic'] ?? '',
                'duration' => $duration,
                'defaultMarks' => $defaultMarks,
                'totalMarks' => $totalMarks,
                'instructions' => 'Answer all questions. Each question carries ' . $defaultMarks . ' mark(s). No negative marking.',
                'questions' => $formattedQuestions,
                'creatorId' => $user['id'] ?? 'unknown',
                'creatorName' => $user['name'] ?? 'AI System',
                'isPublished' => false,
                'createdAt' => now()->toIso8601String(),
            ];

            $db['exams'][] = $exam;
            JsonDb::save($db);

            Log::info('CBT exam created successfully', [
                'examId' => $examId,
                'questionCount' => count($formattedQuestions),
                'duration' => $duration,
            ]);

            return response()->json([
                'success' => true,
                'exam' => $exam,
                'examId' => $examId,
                'message' => count($formattedQuestions) . ' questions converted to CBT exam format.',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Convert to CBT validation failed', ['errors' => $e->errors()]);
            return response()->json(['success' => false, 'error' => 'Validation error: ' . json_encode($e->errors())], 422);
        } catch (\Exception $e) {
            Log::error('Convert to CBT failed with exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'error' => 'Failed to convert to CBT: ' . $e->getMessage()], 500);
        }
    }

    public function getQuestionSets()
    {
        JsonDb::init();
        $db = JsonDb::get();
        $user = Session::get('user');
        $userId = $user['id'] ?? '';
        $sets = array_filter($db['questionSets'] ?? [], fn($qs) => $qs['teacherId'] === $userId);
        return response()->json(['questionSets' => array_values($sets)]);
    }

    public function getQuestionSet($id)
    {
        $user = Session::get('user');
        if (!$user) return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        JsonDb::init();
        $db = JsonDb::get();
        foreach ($db['questionSets'] as $qs) {
            if ($qs['id'] === $id) {
                if (($user['role'] ?? '') !== 'admin' && ($qs['teacherId'] ?? '') !== ($user['id'] ?? '')) {
                    return response()->json(['success' => false, 'error' => 'This question set is private to its owner'], 403);
                }
                return response()->json(['questionSet' => $qs]);
            }
        }
        return response()->json(['success' => false, 'error' => 'Question set not found.'], 404);
    }

    // --- PROMPT BUILDERS ---

    protected function buildLessonPlanPrompt($subject, $class, $term, $week, $topic, $schoolName, $teacherName, $duration, $ageRange, $scheme, $subTopic = ''): string
    {
        $weekScheme = '';
        $schemeSubtopics = [];
        foreach ($scheme as $s) {
            if (($s['week'] ?? 0) == $week) {
                $weekScheme = 'Scheme of Work topic: ' . ($s['topic'] ?? '') . '. Subtopics: ' . implode(', ', $s['subtopics'] ?? []);
                $schemeSubtopics = $s['subtopics'] ?? [];
                break;
            }
        }

        $userSubtopicsList = [];
        if (!empty($subTopic)) {
            $userSubtopicsList = array_map('trim', preg_split('/[,\n]+/', $subTopic));
            $userSubtopicsList = array_filter($userSubtopicsList);
        }

        $subtopicsInstruction = '';
        $objectiveSources = [];

        if (!empty($userSubtopicsList)) {
            $subtopicsInstruction = "\nUSER-PROVIDED SUB-TOPICS (each is ONE behavioural objective):\n";
            foreach ($userSubtopicsList as $i => $st) {
                $num = $i + 1;
                $subtopicsInstruction .= "  {$num}. {$st}\n";
                $objectiveSources[] = $st;
            }
            $subtopicsInstruction .= "\nGenerate exactly " . count($userSubtopicsList) . " behavioural objectives matching these sub-topics. Each objective becomes one lesson step and one evaluation question.";
        } elseif (!empty($schemeSubtopics)) {
            $subtopicsInstruction = "\nSCHEME SUB-TOPICS (each is ONE behavioural objective):\n";
            foreach ($schemeSubtopics as $i => $st) {
                $num = $i + 1;
                $subtopicsInstruction .= "  {$num}. {$st}\n";
                $objectiveSources[] = $st;
            }
        }

        $numSources = count($objectiveSources);
        $minSteps = max(4, $numSources);
        $maxSteps = max(6, $numSources + 2);
        $evalCount = $minSteps;
        $stepCountInstruction = "Generate {$minSteps}-{$maxSteps} behavioural objectives, {$minSteps}-{$maxSteps} corresponding lesson steps, and {$minSteps}-{$maxSteps} evaluation questions. The exact number depends on how many distinct sub-topics naturally arise from \"{$topic}\". Each sub-topic becomes one objective → one step → one evaluation question. Aim for at least 4 steps; use up to {$maxSteps} if the topic merits it.";

        $isMath = $this->categorizeSubject($subject) === 'math';
        $isStem = $this->categorizeSubject($subject) === 'stem';

        if ($isMath) {
            return $this->buildMathLessonPlanPrompt($subject, $class, $term, $week, $topic, $schoolName, $teacherName, $duration, $ageRange, $weekScheme, $subtopicsInstruction, $stepCountInstruction, $minSteps, $maxSteps);
        }

        $stemFormatting = '';
        if ($isStem) {
            $stemFormatting = "\n\nSCIENTIFIC NOTATION REQUIREMENTS (for Physics/Chemistry):\n"
                . "- Use <sup> for exponents: ms<sup>-1</sup>, ms<sup>-2</sup>, 10<sup>6</sup>\n"
                . "- Use <sub> for chemical formulae: H<sub>2</sub>O, CO<sub>2</sub>, H<sub>2</sub>SO<sub>4</sub>\n"
                . "- Use × for multiplication (not x), ÷ for division (not /)\n"
                . "- Use Greek letters: θ, ω, λ, μ, ρ, Δ where applicable\n"
                . "- Use →, ⇌ for chemical reaction arrows\n"
                . "- Format fractions with CSS — NEVER slanted slashes\n"
                . "- Include formula, substitution, and step-by-step calculation for any numerical problems\n"
                . "- Scientific notation: 6.02 × 10<sup>23</sup> (not E-notation)\n"
                . "- Show all SI units clearly: ms<sup>-1</sup>, kg, N, J, W, Pa";
        }

        return <<<PROMPT
You are a curriculum expert and professional lesson plan writer.

CRITICAL — You MUST write ONLY about the EXACT topic specified below. Do NOT change the topic or write about anything else.

TOPIC (do not deviate): {$topic}

CONTEXT:
- Subject: {$subject}
- Class: {$class} (Age range: {$ageRange})
- Term: {$term}
- Week: {$week}
- School: {$schoolName}
- Teacher: {$teacherName}
- Duration: {$duration}
{$weekScheme}
{$subtopicsInstruction}

Generate a COMPLETE, DETAILED LESSON PLAN in STRICT JSON format for "{$topic}" in {$subject} for {$class}.

CRITICAL — MUST FIT EXACTLY ONE A4 PAGE:
- Each behavioural objective: full detailed sentence.
- Each step: teacherActivities = 2-3 detailed sentences, learnerActivities = 2-3 detailed sentences.
- learningPoints = substantive paragraph.
- Evaluation: {$evalCount} numbered questions, each a full sentence.
- Summary and conclusion: 3-4 sentences each.
- Previous knowledge: 2-3 sentences.
- Write detailed, rich content but use tight wording — every word must earn its place. No filler or fluff.

Return ONLY valid JSON with this exact structure (no markdown, no code fences). The arrays must have the SAME length (behaviouralObjectives count === lessonSteps count === evaluation question count):
{
  "behaviouralObjectives": ["Learners define ...", "Learners identify ...", "Learners differentiate between ..."],
  "instructionalMaterials": ["Material 1", "Material 2", "Material 3", ...],
  "previousKnowledge": "2-3 sentences about what students already know related to this topic.",
  "lessonSteps": [
    {
      "step": 1,
      "teacherActivities": "2-3 detailed sentences describing what the teacher does in this step.",
      "learnerActivities": "2-3 detailed sentences describing what learners do in this step.",
      "learningPoints": "Substantive paragraph about the key learning point from this step."
    }
  ],
  "evaluation": "1. First evaluation question?\\n2. Second evaluation question?\\n3. Third evaluation question?\\n...",
  "assignment": "Detailed take-home assignment (2-3 sentences).",
  "summary": "Substantive summary of the lesson (3-4 sentences).",
  "conclusion": "Concluding remarks connecting to next lesson (3-4 sentences)."
}

RULES:
- {$stepCountInstruction}
- BEHAVIOURAL OBJECTIVES STYLE (MANDATORY): write each objective directly with an action verb, starting with "Learners" + present-tense verb — e.g. "Learners define the concept of...", "Learners list the types of...", "Learners identify the parts of...", "Learners explain the importance of...", "Learners differentiate between...", "Learners describe the process of...", "Learners calculate...", "Learners demonstrate...", "Learners state...", "Learners mention...", "Learners apply...", "Learners analyse...". NEVER use the phrases "students should be able to", "students will be able to", "pupils should be able to" or any "By the end of the lesson..." prefix in objectives. Each objective must remain a full, detailed, measurable sentence.
- Each objective, its corresponding step, and its evaluation question must cover the SAME sub-topic.
- Teacher and learner activities must be practical, detailed, and curriculum-based.
- ALL content must be about "{$topic}" specifically for {$class} level ({$ageRange}).
- Use globally relatable, real-world examples without referencing any specific country or city.
- Every field must contain substantial content — no empty or one-line entries.
- If the topic is "{$topic}", do NOT write about anything else.
- Use globally relatable examples and contexts throughout — do not mention Nigeria or specific cities like Lagos or Kano.
{$stemFormatting}
PROMPT;
    }

    private function buildMathLessonPlanPrompt($subject, $class, $term, $week, $topic, $schoolName, $teacherName, $duration, $ageRange, $weekScheme, $subtopicsInstruction, $stepCountInstruction, $minSteps, $maxSteps): string
    {
        return <<<PROMPT
You are an experienced Mathematics teacher and curriculum expert. Design a DETAILED MATHEMATICS LESSON PLAN for "{$topic}" in {$subject} ({$class}, {$term}, Week {$week}).

CRITICAL — MATHEMATICS IS LEARNED THROUGH DOING. Structure every part of this lesson plan around SOLVING MATHEMATICAL PROBLEMS.

TOPIC (do not deviate): {$topic}

CONTEXT:
- Subject: {$subject}
- Class: {$class} (Age range: {$ageRange})
- Term: {$term}
- Week: {$week}
- School: {$schoolName}
- Teacher: {$teacherName}
- Duration: {$duration}
{$weekScheme}
{$subtopicsInstruction}

LESSON PLAN STRUCTURE FOR MATHEMATICS:

Each lesson step should follow this pattern:
1. **Teacher introduces a concept or formula** (1-2 minutes — keep it brief)
2. **Teacher works through solved examples on the board** (5-10 minutes per example — show EVERY step)
3. **Guided practice**: Students attempt similar problems with teacher assistance
4. **Independent class work**: Students solve problems on their own
5. **Review answers** and address common mistakes

BEHAVIOURAL OBJECTIVES must be action-oriented and measurable, each starting with "Learners" + an action verb — e.g., "Learners solve quadratic equations using the formula method", "Learners factorise algebraic expressions", "Learners calculate the area of a circle". NEVER write "students should be able to..." or any similar prefix.

{$stepCountInstruction}

Return ONLY valid JSON with this exact structure:
{
  "behaviouralObjectives": ["Learners solve/factorise/calculate/sketch ..."],
  "instructionalMaterials": ["Whiteboard/Chalkboard", "Markers/Chalk", "Textbook", "Worked example handouts", "Graph paper (if needed)", "Calculator (if allowed)", ...],
  "previousKnowledge": "State the prerequisite mathematical knowledge required for this topic (e.g., multiplication tables, basic algebra, previous formulae). Keep it concise.",
  "lessonSteps": [
    {
      "step": 1,
      "teacherActivities": "Teacher introduces the concept/formula briefly (2-3 minutes). Then works through Example 1 on the board showing every step. Describes the method clearly.",
      "learnerActivities": "Students listen and copy the worked example into their notebooks. They ask questions about each step.",
      "learningPoints": "The key mathematical technique or formula demonstrated in this step."
    },
    {
      "step": 2,
      "teacherActivities": "Teacher works through Example 2 (slightly harder) on the board. Shows alternative method if applicable. Points out common mistakes.",
      "learnerActivities": "Students follow along, attempt the example alongside the teacher, and note the method.",
      "learningPoints": "The application of the formula/technique to more complex problems."
    },
    {
      "step": 3,
      "teacherActivities": "Teacher gives 3-4 guided practice questions. Monitors students as they attempt them, assists struggling students.",
      "learnerActivities": "Students attempt the questions in their exercise books. Raise hands for help. Volunteer to solve on the board.",
      "learningPoints": "Practice applying the technique with teacher guidance."
    },
    {
      "step": 4,
      "teacherActivities": "Teacher assigns 5-8 independent class exercises ranging from easy to difficult. Walks around to check progress.",
      "learnerActivities": "Students solve problems independently. Check their answers with neighbours.",
      "learningPoints": "Independent application and consolidation of the skill."
    },
    {
      "step": {$minSteps},
      "teacherActivities": "Teacher reviews answers to the independent exercises on the board. Highlights common errors and gives examination tips.",
      "learnerActivities": "Students mark their own work, correct mistakes, and note examination tips.",
      "learningPoints": "Self-assessment and error correction."
    }
  ],
  "evaluation": "1. [Easy question testing basic understanding]\\n2. [Moderate question testing application]\\n3. [Hard question testing problem-solving]\\n4. [Word problem applying to real-life context]\\n...",
  "assignment": "5-10 homework problems covering the topic, progressing from easy to difficult. Include at least one word problem.",
  "summary": "Briefly restate the key formula/technique and when to use it (2-3 sentences).",
  "conclusion": "State what the next Mathematics lesson will cover and how it builds on this topic (1-2 sentences)."
}

MATHEMATICAL NOTATION — CRITICAL FORMATTING RULES:
{$this->mathFormattingInstructions()}

RULES:
- Every step MUST involve solving mathematical problems — minimize lecturing
- Examples must progress from simple to difficult
- Each worked example must show EVERY step — no skipping
- Include globally relatable real-world contexts (market prices, local measurements) in word problems — use neutral currency like $ or generic units, not $aira
- Allocate at least 60% of class time to students solving problems (guided + independent)
- Evaluation questions must test calculation ability, not theory recall
- Assignment must be substantial problem-solving practice
- ALL mathematical expressions must use proper notation: × (not x), ÷ (not /), √, π, <sup> for powers, CSS fractions
PROMPT;
    }

    protected function buildLessonNotePrompt($subject, $class, $term, $week, $topic, $periods, $difficulty, $ageRange, $scheme, $userSubtopics = ''): string
    {
        $weekScheme = '';
        foreach ($scheme as $s) {
            if (($s['week'] ?? 0) == $week) {
                $weekScheme = 'Scheme sub-topics: ' . implode(', ', $s['subtopics'] ?? []);
                break;
            }
        }

        $subtopicInstruction = '';
        if (!empty($userSubtopics)) {
            $subtopicInstruction = "\n\nYOU MUST COVER THESE SPECIFIC SUB-TOPICS IN ORDER:\n" . $userSubtopics;
        }

        $subjectCategory = $this->categorizeSubject($subject);

        if ($subjectCategory === 'math') {
            return $this->buildMathLessonNotePrompt($subject, $class, $term, $week, $topic, $periods, $difficulty, $ageRange, $scheme, $userSubtopics);
        }

        $subjectGuidance = match($subjectCategory) {
            'stem' => "This is a SCIENCE subject. Include: relevant formulae, laws/principles, experiments/practical activities, labelled [DIAGRAM: description] placeholders, and real-world applications where appropriate. Include worked examples and calculations ONLY when the specific topic genuinely involves quantitative work (see subject rules below) — never force calculations into descriptive topics.

CRITICAL — YOU MUST INCLUDE A MINIMUM OF 5 FULLY SOLVED NUMERICAL EXAMPLES for any Physics or Chemistry topic that involves calculations:

For PHYSICS topics with calculations — INCLUDE EXACTLY 5-7 WORKED EXAMPLES:
  Example 1: Simple direct substitution (one formula, one step)
  Example 2: Moderate (requires formula selection and substitution)
  Example 3: Moderate-Hard (requires unit conversion + calculation)
  Example 4: Hard (multi-step, combining two or more formulas)
  Example 5: Examination standard (WAEC/NECO/JAMB style)
  Each example must include: Formula Used → Given Data → Substitution with units → Step-by-step working → Final Answer with correct SI units

For CHEMISTRY — LET THE TOPIC DETERMINE WHETHER CALCULATIONS ARE NEEDED:
  First classify the topic as QUANTITATIVE or DESCRIPTIVE.
  QUANTITATIVE topics (numerical examples REQUIRED): mole concept, stoichiometry, concentration/molarity, gas laws, relative atomic/molecular masses, empirical & molecular formulae, percentage composition, electrolysis calculations, pH calculations, energy changes involving calculations, solubility, titration, yield & percentage purity.
    → Include 5-7 fully solved numerical examples: Formula Used → Given Data → Substitution with units → Step-by-step working → Final Answer
  DESCRIPTIVE topics (qualitative, structural, property-based — e.g. Sulphur, Nitrogen and its compounds, Periodic Table, Air, Water, metals, hydrocarbon nomenclature):
    → Do NOT manufacture or force numerical calculations. Concentrate on the ACTUAL chemistry: definition/introduction, position in periodic table, electronic configuration, occurrence, allotropes/isotopes where relevant, physical properties, chemical properties, key reactions WITH BALANCED CHEMICAL EQUATIONS, important compounds, preparation/extraction/industrial production, uses, tests for identification, environmental effects, health & safety, examination points and common misconceptions.
  KEY DISTINCTION: balanced chemical EQUATIONS are NOT numerical calculations — include relevant equations in almost every Chemistry note; numerical worked examples ONLY when the topic genuinely requires them.
  Never invent calculations just to make the note look more detailed. Every section must be relevant to THIS topic — no fixed template.

For other purely theoretical/conceptual science topics — include illustrative examples or case studies instead of calculations

CHEMISTRY FORMATTING REQUIREMENTS (all Chemistry notes):
  Balance ALL chemical equations with proper state symbols
  Use proper subscripts (H₂O, CO₂, H₂SO₄, NH₃, C₂H₅OH, CaCO₃, CH₄, NaCl)
  Use proper superscripts for charges (Ca²⁺, SO₄²⁻, Na⁺, Cl⁻, Fe³⁺, OH⁻)
  Use → for reaction arrows, ⇌ for reversible reactions
  Show state symbols: (s), (l), (g), (aq)
  Format ionic equations with proper charges

FORMATTING REQUIREMENTS FOR EQUATIONS AND FORMULAE:
- Use <sup> for exponents and powers: m/s<sup>2</sup>, N/m<sup>2</sup>, 10<sup>6</sup>
- Use <sub> for chemical formulae: H<sub>2</sub>O, CO<sub>2</sub>, H<sub>2</sub>SO<sub>4</sub>, NH<sub>3</sub>
- Use proper Unicode symbols: × (not x), ÷ (not /), ≤, ≥, ≠, ±, ∞, √, π, θ, Δ, →, ⇌
- Format fractions using CSS inline-block or Unicode fraction characters, never slanted slashes
- Use → for chemical reaction arrows, ⇌ for reversible reactions
- Show state symbols in chemical equations: (s), (l), (g), (aq)
- Format charges and oxidation states with <sup>: Ca<sup>2+</sup>, SO<sub>4</sub><sup>2-</sup>
- Use proper Greek letters: θ, ω, α, μ, λ, ρ
- Format physical units clearly: ms<sup>-1</sup>, kg, N, J, W, Pa
- Align multi-step calculations in a logical vertical layout using <pre> or <table>
- Scientific notation: 6.02 × 10<sup>23</sup> (not E-notation)
- Every bracket must be correctly paired and clearly visible
- For Physics: display equations exactly as in standard textbooks, show derivations step by step
- For Chemistry: balance all chemical equations, format ionic equations with proper charges",
            'humanities' => "This is a HUMANITIES/SOCIAL SCIENCE subject. Focus on: clear explanations of concepts, definitions, classifications, historical developments, causes and effects, significance, key figures, quotations (where relevant), and connections to society and contemporary issues. Use globally relatable real-life examples and case studies without favoring any country.",
            'commercial' => "This is a COMMERCIAL/BUSINESS subject. Include: key terms with definitions, principles and concepts, calculations where relevant (e.g., ratios, interest, profit margins), practical business examples using globally relatable contexts, formats and templates (e.g., ledger accounts, invoices, receipts), and real-world business applications without country-specific references.",
            'tech_voc' => "This is a TECHNOLOGY/VOCATIONAL subject. Include: step-by-step processes, safety precautions (where relevant), tools and materials needed, practical applications, labelled [DIAGRAM: description] placeholders for equipment/processes, maintenance procedures, and globally relatable vocational contexts.",
            default => "Organize content based on the natural structure of the topic. Let the topic determine what headings and sections are appropriate."
        };

        return <<<PROMPT
You are a curriculum expert and experienced classroom teacher. Write a DETAILED LESSON NOTE about "{$topic}" for {$subject} ({$class}, {$term}, Week {$week}). Difficulty: {$difficulty}.

BEFORE YOU WRITE, ANALYZE THE TOPIC:
1. What is the nature of "{$topic}"? (concept, process, classification, theory, formula-based, historical event, practical skill, literary analysis, etc.)
2. What class level is this? ({$class} — {$ageRange}) — match vocabulary and complexity precisely.
3. What headings would an experienced teacher naturally use when teaching THIS topic to THIS class?
4. What supporting elements (definitions, examples, diagrams, formulae, calculations, exercises, activities) will help students understand THIS topic?
5. What sections would NOT be relevant and should be OMITTED?

STRUCTURE RULES (CRITICAL):
- Choose headings that are NATURALLY RELEVANT to "{$topic}" ONLY
- DO NOT force any heading that does not belong. Examples of OMISSIONS:
  * If the topic has no "advantages/disadvantages", DO NOT include them
  * If the topic does not need a "summary", OMIT it
  * If "safety precautions" are not relevant, LEAVE THEM OUT
  * If "applications" do not naturally arise, SKIP them
  * If "comparison tables" do not add clarity, DO NOT use them
  * If "classroom activities" do not fit, DO NOT force them
  * If "practical applications" are not relevant, OMIT them
- NEVER include filler sections to match a template
- Every section you include must add genuine educational value

{$subjectGuidance}

{$weekScheme}
{$subtopicInstruction}

Now write the lesson note. Return ONLY valid JSON. ALL fields below are OPTIONAL except "topic", "introduction", and "content" — only include fields that are relevant to this topic:

{
  "topic": "{$topic}",
  "subtopics": ["Relevant subtopics — only if the topic naturally breaks into subtopics"],
  "learningObjectives": ["Learners define ...", "Learners explain ..." — start each with 'Learners' + action verb; NEVER 'students should be able to' — only if objectives add value],
  "introduction": "3-5 sentence engaging introduction connecting to prior knowledge with globally relatable context",
  "content": "FULL HTML — MAIN BODY of the lesson (~2-3 A4 pages). Choose <h3> for main section headings and <h4> for subsections that are APPROPRIATE for this topic. Possible headings (use only what fits): Definitions, Types/Kinds, Classification, Properties, Characteristics, Structure, Functions, Causes, Effects, Symptoms, Prevention, Control, Processes, Steps, Principles, Laws, Formulae, Worked Examples, Solved Problems, Experiments, Observations, Uses, Importance, Benefits, Drawbacks, Diagrams, Tables. Use <p>, <ul>, <ol>, <table>, <pre> as needed. For diagrams: include [DIAGRAM: clear description]. For calculations: show step-by-step solutions. Use globally relatable examples throughout without mentioning any specific country.",
  "sections": [
    {
      "heading": "Section heading (only if this section needs distinct visual treatment)",
      "content": "HTML content for this section"
    }
  ],
  "evaluationQuestions": ["...", "..." — only if relevant to this topic],
  "assignment": "Take-home assignment — only if meaningful",
  "keyPoints": ["1-2 sentence key takeaways" — only if they add value]
}

ABSOLUTELY FORBIDDEN:
- No filler content — every sentence must teach something
- No fixed template structure — this note must feel custom-written for "{$topic}"
- No repeated phrasing across different topics — each lesson note is unique
- No heading that is not genuinely relevant to this topic

IMPORTANT RULES:
- Every sentence MUST be about "{$topic}"
- Follow standard curriculum standards for the specified class level
- Use globally relatable examples without mentioning Nigeria or specific cities like Lagos or Kano
- Match vocabulary complexity to {$class} level ({$ageRange})
- Difficulty "{$difficulty}": Simple=foundational, Standard=curriculum depth, Deep=advanced (including WAEC/NECO/JAMB-level content)
- If a diagram would improve understanding, include [DIAGRAM: clear description of what to draw] in the content
- No two unrelated topics should have identical structure
PROMPT;
    }

    private function buildMathLessonNotePrompt($subject, $class, $term, $week, $topic, $periods, $difficulty, $ageRange, $scheme, $userSubtopics = ''): string
    {
        $weekScheme = '';
        foreach ($scheme as $s) {
            if (($s['week'] ?? 0) == $week) {
                $weekScheme = 'Scheme sub-topics: ' . implode(', ', $s['subtopics'] ?? []);
                break;
            }
        }

        $subtopicInstruction = '';
        if (!empty($userSubtopics)) {
            $subtopicInstruction = "\n\nCOVER THESE SUB-TOPICS:\n" . $userSubtopics;
        }

        return <<<PROMPT
You are an experienced Mathematics teacher and curriculum expert. Write a DETAILED MATHEMATICS LESSON NOTE about "{$topic}" for {$subject} ({$class}, {$term}, Week {$week}). Difficulty: {$difficulty}.

CRITICAL — MATHEMATICS IS A PRACTICAL SUBJECT. This lesson note must focus on SOLVING PROBLEMS, not writing long explanations. Follow these rules strictly:

CONTENT BALANCE (MANDATORY):
- At least 80-90% of the lesson must be worked examples, calculations, and practice exercises
- No more than 10-20% should be descriptive text or explanations
- Keep all explanations short, clear, and straight to the point

MINIMUM 5 FULLY SOLVED WORKED EXAMPLES — YOU MUST INCLUDE AT LEAST 5:
- Example 1: Simple/basic — direct application of the formula or method
- Example 2: Moderate — requires slightly more steps or careful substitution
- Example 3: Moderate-Hard — word problem or application in a globally relatable real-world context
- Example 4: Hard — multi-step problem requiring combined techniques
- Example 5: Examination standard — WAEC/NECO/JAMB style question
- (Include more examples if the topic merits it, up to 8)
- EVERY example must show ALL steps — do NOT skip any calculation
- Include the formula used, substitution, step-by-step working, and final answer
- Include "Common Mistake to Avoid" notes after each example

STRUCTURE:
1. BRIEF DEFINITION OR EXPLANATION (only if necessary — keep it to 2-4 sentences max)
2. FORMULAE / RULES / THEOREMS (present clearly, with notation explanations)
3. WORKED EXAMPLES (this is the MAIN part of the lesson — MINIMUM 5):
   - Start with simple examples, progress to more difficult ones
   - Show EVERY step clearly — do not skip calculations
   - Explain WHY each step is taken where necessary
   - Include alternative methods of solving where appropriate
   - Use <pre> or <code> blocks for step-by-step working
   - Format all mathematical expressions using proper notation:
     × (not x), ÷ (not /), ≤, ≥, ≠, ±, ∞, √, π, θ, α, β, Δ, Σ, ∛, ∝, ∠, °, ∥, ⊥, ≈
     Use <sup> for powers: x<sup>2</sup>, 2<sup>3</sup>, e<sup>x</sup>
     Use CSS inline-block for fractions — NEVER slanted slashes
4. PRACTICE EXERCISES (questions for students WITHOUT answers — at least 5)
5. COMMON MISTAKES AND EXAMINATION TIPS (short, bullet-point format)
6. SHORTCUTS / MENTAL MATH TRICKS (where applicable)

Example format for worked examples — each example MUST have this structure:
<div class="example">
<h4>Example 1: [Title — e.g., Solving Quadratic Equation by Formula Method]</h4>
<p><strong>Formula:</strong> [State the formula clearly]</p>
<p><strong>Solution:</strong></p>
<pre>
Step 1: [show working]
Step 2: [show working]
Step 3: [show working]
...
Final Answer: [answer with proper notation]
</pre>
<p><em>Common Mistake:</em> [1 sentence about error students often make]</p>
</div>

For the CONTENT field, use these HTML headings as appropriate:
- <h3>Formulae</h3>
- <h3>Worked Examples</h3>
- <h3>Practice Exercises</h3>
- <h3>Common Mistakes</h3>
- <h3>Examination Tips</h3>
- <h3>Shortcuts</h3>

{$weekScheme}
{$subtopicInstruction}

MATHEMATICAL NOTATION — CRITICAL FORMATTING RULES:
{$this->mathFormattingInstructions()}

Return ONLY valid JSON:
{
  "topic": "{$topic}",
  "subtopics": ["Relevant subtopics"],
  "learningObjectives": ["Learners define ...", "Learners identify ...", "Learners calculate ..." — start each with 'Learners' + action verb; NEVER 'students should be able to'],
  "introduction": "VERY BRIEF — 1-2 sentences connecting to prior knowledge or stating the importance of this topic in Mathematics",
  "content": "FULL HTML — MAIN BODY (~3-4 A4 pages). 80-90% of this must be worked examples, step-by-step solutions, and practice exercises. Use <h3> for section headings, <pre> or <code> for mathematical workings, <table> for comparison/formulae. INCLUDE A MINIMUM OF 5 WORKED EXAMPLES. Each worked example must show all steps clearly. Include practice exercises after each section without solutions. FORMAT ALL MATHEMATICAL EXPRESSIONS using proper notation as specified above.",
  "sections": [
    {
      "heading": "Additional section heading if needed",
      "content": "HTML content"
    }
  ],
  "evaluationQuestions": ["Practice questions covering all difficulty levels"],
  "assignment": "Take-home problems to solve",
  "keyPoints": ["Key formulae, rules, or techniques to remember"]
}

RULES:
- Every example must be FULLY SOLVED with all steps shown — never skip steps
- Use globally relatable contexts for word problems (markets, local measurements) with neutral currency
- Match difficulty to {$class} level ({$ageRange})
- Difficulty "{$difficulty}": Simple=basic numeracy, Standard=curriculum level, Deep=WAEC/NECO/JAMB exam standard
- Include at least 5-8 fully worked examples for standard topics, more for complex topics
- After each section of worked examples, include 3-5 practice questions WITHOUT solutions
- Keep ALL explanations brief — teach through examples, not paragraphs
- For {$class}: ensure age-appropriate numbers and contexts
PROMPT;
    }

    private function mathFormattingInstructions(): string
    {
        return <<<'INSTRUCTIONS'
MATHEMATICAL AND SCIENTIFIC NOTATION FORMATTING — You MUST follow these rules for ALL expressions, equations, formulae, and calculations:

FRACTIONS (CRITICAL):
- NEVER use slanted slashes for fractions like 3/4 or (2x+1)/(x-3)
- ALWAYS use inline-block CSS fraction format:
  <span style="display:inline-flex;flex-direction:column;vertical-align:middle;text-align:center;margin:0 2px;font-size:0.9em">
    <span style="border-bottom:2px solid #333;padding:0 6px 2px">numerator</span>
    <span style="padding:2px 6px 0">denominator</span>
  </span>
- For simple fractions in text, use Unicode fraction characters: ½ ⅓ ⅔ ¼ ¾ ⅛ ⅜ ⅝ ⅞
- For algebraic fractions, ALWAYS use the CSS fraction format described above
- NEVER write a/b, x/y, or any fraction with a forward slash

SUPERSCRIPTS AND SUBSCRIPTS (MANDATORY):
- Use <sup> for powers and exponents: x<sup>2</sup>, 2<sup>3</sup>, e<sup>x</sup>, a<sup>n</sup>
- Use <sub> for indices and chemical formulae: log<sub>2</sub>x, H<sub>2</sub>O, CO<sub>2</sub>, H<sub>2</sub>SO<sub>4</sub>, NH<sub>3</sub>, Na<sub>2</sub>CO<sub>3</sub>, C<sub>2</sub>H<sub>5</sub>OH, CaCO<sub>3</sub>, CH<sub>4</sub>, NaCl
- For compound superscripts like x²ⁿ, use <sup>2n</sup> inside the HTML
- For compound subscripts like Fe₂O₃, use <sub>2</sub>O<sub>3</sub>

MATHEMATICAL SYMBOLS (use Unicode or HTML entities — MANDATORY):
- × for multiplication (not x, not *): 5 × 3, 2 × 10<sup>3</sup>
- ÷ for division (not /): 12 ÷ 4, 144 ÷ 12
- ≤ for less than or equal to: x ≤ 5
- ≥ for greater than or equal to: y ≥ 3
- ≠ for not equal to: x ≠ 0
- ± for plus-minus: x = ±√4
- ∞ for infinity: x → ∞
- √ for square root: √16, √(x+1)
- ∛ for cube root: ∛27, ∛(x+1)
- π for pi: πr<sup>2</sup>, 2πr, πd
- θ, α, β, γ, δ, ε, λ, μ, ω, ρ, φ, ψ for standard Greek letters
- Δ for change/delta: Δx, Δt, ΔT
- Σ for sum/summation
- → and ⇌ for reaction arrows (Chemistry)
- ° for degrees: 90°, 180°, 360°, 45°
- ∠ for angles: ∠ABC, ∠θ
- ∥ for parallel: AB ∥ CD
- ⊥ for perpendicular: AB ⊥ CD
- ≈ for approximately equal: π ≈ 3.142
- ≡ for identical to/equivalence
- ∩ for intersection, ∪ for union
- ∫ for integration, ∬ for double integration
- ∝ for proportional to: F ∝ ma
- ∑ for summation

BRACKETS AND GROUPING:
- Always use clear, correctly matched brackets: ( ), [ ], { }
- For nested expressions, alternate bracket types: { [ ( ) ] }
- Never write confusing nested brackets like ((((x+1)))) — use different bracket types
- Ensure every opening bracket has a matching closing bracket
- Use \| or | for absolute value: |x|, |-5|

ALIGNMENT OF WORKED SOLUTIONS:
- Use <table> or <pre> with clear spacing for multi-step calculations
- Align equals signs vertically in consecutive steps
- Number each step: Step 1, Step 2, Step 3, etc.
- Keep expressions on separate lines rather than chaining with = on one long line
- Indent continuation lines to show logical grouping

PHYSICS EQUATIONS AND NOTATION:
- Display all equations exactly as in standard textbooks
- Use proper Greek letters: θ (theta), ω (omega), α (alpha), μ (mu), λ (lambda), ρ (rho), Δ (Delta)
- Format units properly: ms<sup>-1</sup>, ms<sup>-2</sup>, N, J, W, kg, m, s, A, V, Ω, Hz, Pa
- Distinguish variables (italic concept) from units (roman concept)
- Show derivations step-by-step with clear algebraic manipulation
- Use subscripts for related quantities: v<sub>1</sub>, v<sub>2</sub>, F<sub>g</sub>, E<sub>k</sub>, E<sub>p</sub>
- Scientific notation: 6.02 × 10<sup>23</sup> (not E-notation, not 6.02e23)
- Format large numbers with commas: 1,000,000

CHEMICAL EQUATIONS AND NOTATION:
- Use subscripts for ALL chemical formulae: H<sub>2</sub>SO<sub>4</sub>, CaCO<sub>3</sub>, C<sub>2</sub>H<sub>5</sub>OH, CH<sub>4</sub>, NH<sub>3</sub>, NaCl
- Use → for reaction arrows, ⇌ for reversible reactions (equilibrium)
- Show state symbols for ALL substances: (s), (l), (g), (aq)
- Balance ALL equations with proper coefficients
- Use <sup> for charges and oxidation states: Ca<sup>2+</sup>, SO<sub>4</sub><sup>2-</sup>, Fe<sup>3+</sup>, Na<sup>+</sup>, Cl<sup>-</sup>, OH<sup>-</sup>, NH<sub>4</sub><sup>+</sup>
- Format ionic equations with proper charges
- Examples of properly formatted equations:
  * Zn(s) + H<sub>2</sub>SO<sub>4</sub>(aq) → ZnSO<sub>4</sub>(aq) + H<sub>2</sub>(g)
  * N<sub>2</sub>(g) + 3H<sub>2</sub>(g) ⇌ 2NH<sub>3</sub>(g)
  * CaCO<sub>3</sub>(s) + 2HCl(aq) → CaCl<sub>2</sub>(aq) + H<sub>2</sub>O(l) + CO<sub>2</sub>(g)
  * H<sup>+</sup>(aq) + OH<sup>-</sup>(aq) → H<sub>2</sub>O(l)

SCIENTIFIC NOTATION:
- Use × 10<sup>n</sup> format: 6.02 × 10<sup>23</sup>, 3.0 × 10<sup>8</sup>, 1.6 × 10<sup>-19</sup>
- Use proper formatting: negative exponents use <sup>-n</sup>
- For very small numbers: 1.0 × 10<sup>-6</sup> (not 0.000001)
- Format large numbers with commas: 1,000,000,000

GENERAL RULES:
- NEVER use $...$ or $$...$$ LaTeX delimiters. The platform does NOT have MathJax/KaTeX, so ALL math must use inline HTML entities and CSS formatting as specified above.
- Verify every bracket is correctly paired before output
- Every expression must be clear on both mobile and desktop screens
- Never produce confusing expressions due to incorrect spacing or misplaced symbols
- Use <code> or <pre> for multi-line algebraic working
- Use <table> for structured data, comparisons, and formula summaries
- The final output should look like a professionally typeset textbook
- Use consistent formatting throughout the entire document
INSTRUCTIONS;
    }

    private function categorizeSubject(string $subject): string
    {
        $math = ['Mathematics', 'Further Mathematics'];
        $stem = ['Physics', 'Chemistry', 'Biology', 'Basic Science', 'Intermediate Science', 'Physical and Health Education (PHE)', 'Physical Education'];
        $humanities = ['English Language', 'Literature', 'Literature in English', 'History', 'Government', 'Civic Education', 'Social Studies', 'Christian Religious Studies', 'Islamic Studies', 'Nigerian History', 'Music', 'Art & Design', 'Cultural and Creative Arts (CCA)', 'Craft and Art', 'French', 'Yoruba', 'Igbo', 'Hausa', 'Phonics', 'Verbal Reasoning'];
        $commercial = ['Commerce', 'Accounting', 'Economics'];
        $tech_voc = ['Basic Technology', 'ICT', 'Coding', 'Computer Studies', 'Home Economics', 'Agricultural Science', 'Vocational Studies', 'Artificial Intelligence'];

        if (in_array($subject, $math)) return 'math';
        if (in_array($subject, $stem)) return 'stem';
        if (in_array($subject, $humanities)) return 'humanities';
        if (in_array($subject, $commercial)) return 'commercial';
        if (in_array($subject, $tech_voc)) return 'tech_voc';
        return 'general';
    }

    protected function buildQuestionsPrompt($subject, $topic, $count, $class, $term, $week, $includeTheory, $lessonNoteContent, $subTopic = '', $difficulty = 'Standard'): string
    {
        // Ask for extra questions so filtering still yields the requested count
        $askCount = min((int) ceil($count * 1.25), 200);

        if ($lessonNoteContent) {
            return $this->buildQuestionsFromNotePrompt($subject, $topic, $askCount, $class, $term, $week, $includeTheory, $lessonNoteContent, $subTopic);
        }

        $isMath = $this->categorizeSubject($subject) === 'math';
        $isPhysics = stripos($subject, 'Physics') !== false;
        $isChemistry = stripos($subject, 'Chemistry') !== false;

        if ($isMath) {
            return $this->buildMathQuestionsPrompt($subject, $topic, $askCount, $class, $term, $week, $includeTheory, $subTopic, $difficulty);
        }

        $theoryPart = $includeTheory ? '
  "theoryQuestions": [
    {"question": "Theory question 1", "answer": "Model answer"}
  ],
  "essayQuestions": [
    {"question": "Essay question 1", "guidance": "Key points to cover"}
  ],
  "structuredQuestions": [
    {"question": "Question with parts a, b, c", "parts": {"a": "Part a", "b": "Part b", "c": "Part c"}}
  ]' : '';

        $subtopicLine = $subTopic ? "\nSUB-TOPIC: \"{$subTopic}\". Write questions specifically about this sub-topic within \"{$topic}\"." : '';
        $difficultyLine = $difficulty && $difficulty !== 'Standard' ? " DIFFICULTY: {$difficulty}." : '';

        // Physics: 80% calculation-based, 20% theory
        $physicsInstructions = '';
        if ($isPhysics) {
            $physicsInstructions = <<<PHYSICS

CRITICAL — PHYSICS QUESTION DISTRIBUTION (MANDATORY):
- 80% CALCULATION QUESTIONS: Require formula selection, substitution with units, calculation, and unit conversion where appropriate.
- 20% THEORY/CONCEPTUAL QUESTIONS: Test concepts, definitions, principles, laws, applications, and interpretation of scientific ideas.

CALCULATION QUESTIONS MUST:
- Provide numerical data that requires formula application
- Include proper units in both questions and options
- Require formula selection before substitution
- Test ability to convert units where applicable
- Follow WAEC and NECO examination standards

EXAMPLES OF CALCULATION QUESTIONS:
- "A car of mass 1200 kg accelerates from rest to 20 ms⁻¹ in 10 s. Calculate the force exerted by the engine."
- "A ray of light strikes a plane mirror at an angle of 35° to the normal. What is the angle of reflection?"
- "Calculate the resistance of a wire of length 2 m and cross-sectional area 4 × 10⁻⁶ m² if its resistivity is 1.7 × 10⁻⁸ Ωm."

AVOID too many definition/recall questions. Focus on application and problem-solving.

All Physics questions must use:
- × for multiplication (not x), ÷ for division (not /)
- <sup> for exponents and units: ms<sup>-1</sup>, ms<sup>-2</sup>, N, J, W, kg
- Proper Greek letters: θ, ω, λ, μ, ρ, Δ
- Scientific notation: 6.02 × 10<sup>23</sup> (not E-notation)
PHYSICS;
        }

        // Chemistry: balance equations, calculations
        $chemistryInstructions = '';
        if ($isChemistry) {
            $chemistryInstructions = <<<CHEMISTRY

CRITICAL — CHEMISTRY QUESTIONS MUST:
- Include balanced chemical equations where relevant
- Use proper subscripts for chemical formulae: H<sub>2</sub>O, CO<sub>2</sub>, H<sub>2</sub>SO<sub>4</sub>, NH<sub>3</sub>, C<sub>2</sub>H<sub>5</sub>OH
- Use proper superscripts for charges: Ca<sup>2+</sup>, SO<sub>4</sub><sup>2-</sup>, Na<sup>+</sup>, Cl<sup>-</sup>
- Include calculation questions for quantitative chemistry (mole concept, concentrations, gas laws)
- Use → for reaction arrows, ⇌ for reversible reactions
- Show state symbols: (s), (l), (g), (aq)
- Format fractions using CSS — NEVER slanted slashes
CHEMISTRY;
        }

        return <<<PROMPT
You are an examination expert. Generate {$askCount} objective (multiple-choice) questions{$theoryPart} about "{$topic}" in {$subject} for {$class} level ({$term}).{$difficultyLine}
{$subtopicLine}
{$physicsInstructions}
{$chemistryInstructions}

SUBJECT: {$subject} — Every question MUST be about {$subject} content.
TOPIC: {$topic} — Every question MUST test knowledge specifically about "{$topic}" within {$subject}.
CLASS: {$class} — Match difficulty to {$class} per standard curriculum for that class level.

CRITICAL RULE — EVERY question stem MUST contain the word "{$topic}" or a direct reference to a specific subtopic within {$topic}. If the stem doesn't mention {$topic}, the question is OFF-TOPIC and will be rejected.

VARY QUESTION STYLES across the set. At most 2 WH-word starters per 10 questions. Include:
- Directives (State/Define/List)
- Fill-the-blank (___)
- Scenario/Application
- Classification (example of)
- Comparison/Differentiation
- Negative/Exception (All EXCEPT)
- True/False statements about
- Cause-Effect
- Sequence/Order
- Calculations (if math/science)

Each question: 4 UNIQUE options (A/B/C/D), exactly ONE correct answer, wrong options plausible but clearly wrong. Randomize answer position (~25% each).

Return ONLY valid JSON:
{"objectives":[{"id":1,"question":"stem about {$topic}","A":"opt","B":"opt","C":"opt","D":"opt","answer":"A"}]{$theoryPart}}
PROMPT;
    }

    private function buildMathQuestionsPrompt($subject, $topic, $count, $class, $term, $week, $includeTheory, $subTopic = '', $difficulty = 'Standard'): string
    {
        $theoryPart = $includeTheory ? '
  "shortAnswer": [
    {"question": "Short-answer question requiring calculation", "answer": "Numerical answer"}
  ],
  "structuredQuestions": [
    {"question": "Multi-part problem", "parts": {"a": "Part a", "b": "Part b", "c": "Part c"}}
  ]' : '';

        $subtopicLine = $subTopic ? "\nSUB-TOPIC: \"{$subTopic}\". Focus questions on this sub-topic within \"{$topic}\"." : '';

        return <<<PROMPT
You are a Mathematics examination expert. Generate a MATHEMATICS QUESTION POOL about "{$topic}" in {$subject} for {$class} level ({$term}). Difficulty: {$difficulty}.

CRITICAL — NEARLY 100% OF MATHEMATICS QUESTIONS MUST BE CALCULATION-BASED. Do NOT include definition, list, state, or theory recall questions. Every question must require mathematical working.

{$subtopicLine}

Generate {$count} objective (multiple-choice) questions{$theoryPart}. Distribute them as:

QUESTION DISTRIBUTION:
- 50% Direct computational (solve, calculate, evaluate, simplify, find the value of)
- 25% Word problems (real-life scenarios using globally relatable contexts — markets, farm measurements, business — with neutral currency)
- 15% Application questions (apply formula to given data, interpret results)
- 10% Multi-step problem-solving (combine 2+ techniques to find answer)

DIFFICULTY BREAKDOWN:
- 25% Beginner (direct formula application, simple substitution — but still requires calculation)
- 50% Intermediate (require multiple steps, combining concepts, careful algebra)
- 25% Advanced (examination-standard, multi-step word problems, WAEC/NECO/JAMB style)

QUESTION TYPES MUST INCLUDE:
- Simplify algebraic expressions
- Solve equations/inequalities (linear, quadratic, simultaneous)
- Calculate numerical values (area, volume, perimeter, distance, speed, etc.)
- Find unknown quantities using given data
- Evaluate trigonometric expressions
- Apply logarithmic and indicial laws
- Solve problems on sequences and series
- Vector and matrix operations
- Statistical calculations (mean, median, mode, standard deviation)
- Probability calculations
- Calculus problems (differentiation, integration, rates of change)
- Mensuration problems (area, volume of solids)
- Coordinate geometry calculations

ABSOLUTELY FORBIDDEN:
- "What is the definition of..." — DO NOT INCLUDE
- "List the properties of..." — DO NOT INCLUDE
- "State the..." — DO NOT INCLUDE unless followed by a calculation
- "Which of the following is a type of..." — DO NOT INCLUDE
- Any question that can be answered without doing a calculation

Notation: Use proper mathematical symbols in questions and options — × (not x), ÷ (not /), use <sup> for powers, use √ for square roots, use π for pi, etc. Format fractions with CSS or Unicode — NEVER slanted slashes.

Each MCQ: 4 UNIQUE options (A/B/C/D), exactly ONE correct answer. Numerical options must include plausible wrong answers derived from common calculation errors (wrong formula, sign error, unit conversion mistake). For "none of the above" or "all of the above" use sparingly (max 1 per set).

AVOID one-step arithmetic (e.g., "What is 5 + 3?"). Every question must require at least 2-3 steps of reasoning.

Return ONLY valid JSON:
{"objectives":[{"id":1,"question":"Calculate ...","A":"opt","B":"opt","C":"opt","D":"opt","answer":"A"}]{$theoryPart}}
PROMPT;
    }

    protected function buildQuestionsFromNotePrompt($subject, $topic, $count, $class, $term, $week, $includeTheory, $lessonNoteContent, $subTopic = ''): string
    {
        // Ask for extra questions so filtering still yields the requested count
        $askCount = min((int) ceil($count * 1.25), 200);

        $isMath = $this->categorizeSubject($subject) === 'math';
        $isPhysics = stripos($subject, 'Physics') !== false;
        $isChemistry = stripos($subject, 'Chemistry') !== false;

        if ($isMath) {
            return $this->buildMathQuestionsFromNotePrompt($subject, $topic, $askCount, $class, $term, $week, $includeTheory, $lessonNoteContent, $subTopic);
        }

        $physicsCalcRequirement = '';
        if ($isPhysics) {
            $physicsCalcRequirement = "\n\nPHYSICS QUESTION DISTRIBUTION (MANDATORY):\n"
                . "- 80% Calculation questions (formula selection, substitution, unit conversion, calculations)\n"
                . "- 20% Theory/Conceptual questions (definitions, principles, laws, applications)\n"
                . "- Use proper units and scientific notation: ms<sup>-1</sup>, N, J, W, kg, ms<sup>-2</sup>\n"
                . "- Use Greek letters: θ, ω, λ, μ, ρ, Δ\n"
                . "- Format fractions with CSS — NEVER slanted slashes\n";
        }

        $chemistryFormatting = '';
        if ($isChemistry) {
            $chemistryFormatting = "\n\nCHEMISTRY FORMATTING (MANDATORY):\n"
                . "- Use proper subscripts: H<sub>2</sub>O, CO<sub>2</sub>, H<sub>2</sub>SO<sub>4</sub>, NH<sub>3</sub>\n"
                . "- Use proper superscripts for charges: Ca<sup>2+</sup>, SO<sub>4</sub><sup>2-</sup>, Na<sup>+</sup>\n"
                . "- Use → for reaction arrows, ⇌ for reversible reactions\n"
                . "- Show state symbols: (s), (l), (g), (aq)\n"
                . "- Include calculation questions for quantitative chemistry topics\n";
        }

        $theoryPart = $includeTheory ? '
  "theoryQuestions": [
    {"question": "Theory question 1", "answer": "Model answer"}
  ],
  "essayQuestions": [
    {"question": "Essay question 1", "guidance": "Key points to cover"}
  ],
  "structuredQuestions": [
    {"question": "Question with parts a, b, c", "parts": {"a": "Part a", "b": "Part b", "c": "Part c"}}
  ]' : '';

        $extract = $this->extractNoteText($lessonNoteContent);

        // Truncate lesson note to avoid exceeding AI context window
        // Prioritize the most content-rich parts
        $maxLength = 8000;
        if (strlen($extract) > $maxLength) {
            $truncated = mb_substr($extract, 0, $maxLength);
            $lastBreak = mb_strrpos($truncated, "\n\n");
            if ($lastBreak !== false && $lastBreak > $maxLength * 0.7) {
                $extract = mb_substr($extract, 0, $lastBreak);
            } else {
                $extract = $truncated;
            }
            $extract .= "\n\n[Note: Lesson note truncated to fit within context limits. Focus on the content above to generate questions.]";
        }

        Log::info('Building questions from lesson note', [
            'note_length' => strlen($lessonNoteContent),
            'extract_length' => strlen($extract),
            'truncated' => strlen($lessonNoteContent) > $maxLength,
        ]);

        return <<<PROMPT
You are an examination expert. Your task is to generate {$askCount} objective (multiple-choice) questions based STRICTLY on the lesson note provided below for {$subject} ({$class}, {$term}, Week {$week}).

SUBJECT: {$subject}
TOPIC: {$topic}
CLASS: {$class}
TERM: {$term}
WEEK: {$week}

CRITICAL: The subject is {$subject}. The topic is {$topic}. The class level is {$class}. Every question must be:
- About {$subject} (not another subject)
- About {$topic} specifically (not another topic in {$subject})
- Appropriate for {$class} level difficulty
- Based strictly on the lesson note content below
{$physicsCalcRequirement}
{$chemistryFormatting}

Do NOT write questions about general knowledge, other subjects, other topics within {$subject}, or anything not covered in the lesson note.

LESSON NOTE CONTENT:
{$extract}

GENERATION RULES:
1. Read the complete lesson note above before generating any questions.
2. Generate questions ONLY from the content contained in the lesson note.
3. Never introduce facts, examples, or concepts that are not covered in the lesson note.
4. Ensure every question can be answered using only the lesson note.
5. Cover all major sections and subtopics in the lesson note.
6. Avoid repeating the same concept unnecessarily.
7. Produce balanced and comprehensive objective questions that fairly cover the full lesson.

QUESTION STYLE GUIDELINES:
- Vary question openings — do not start most questions with What, Why, When, Where, Who, Which, or How
- Use a mix of styles: command/directive, fill-the-blank, true/false, classification, cause-effect, scenario/application, negative/exception
- Each question must have 4 distinct options (A, B, C, D) — one correct, three wrong but plausible
- Randomize which letter has the correct answer across all questions
- Ensure wrong options are also derived from the lesson note content (not made up from outside knowledge)
- For Mathematics: nearly 100% must be calculation questions requiring mathematical working
- For Physics: 80% calculation questions with proper units, 20% theory/conceptual
- For Chemistry: include balanced equations, proper subscripts/superscripts, calculation questions

SELF-VERIFICATION:
After writing all {$askCount} questions, verify EVERY SINGLE ONE:
- Can this question be answered using ONLY the lesson note above? If NO, rewrite or delete it.
- Does this question test a concept actually present in the lesson note? If NO, rewrite or delete it.
- Are all 4 options based on the lesson note content? If NO, fix them.
- Is exactly one option correct? If NO, fix.

Return ONLY valid JSON in this exact format (no text before or after):
{
  "objectives": [
    {
      "id": 1,
      "question": "Question text based strictly on the lesson note",
      "A": "Option A",
      "B": "Option B",
      "C": "Option C",
      "D": "Option D",
      "answer": "C"
    }
  ]{$theoryPart}
}
PROMPT;
    }

    private function buildMathQuestionsFromNotePrompt($subject, $topic, $count, $class, $term, $week, $includeTheory, $lessonNoteContent, $subTopic = ''): string
    {
        $theoryPart = $includeTheory ? '
  "shortAnswer": [
    {"question": "Short-answer question requiring calculation", "answer": "Numerical answer"}
  ],
  "structuredQuestions": [
    {"question": "Multi-part problem", "parts": {"a": "Part a", "b": "Part b", "c": "Part c"}}
  ]' : '';

        $extract = $this->extractNoteText($lessonNoteContent);

        $maxLength = 8000;
        if (strlen($extract) > $maxLength) {
            $truncated = mb_substr($extract, 0, $maxLength);
            $lastBreak = mb_strrpos($truncated, "\n\n");
            if ($lastBreak !== false && $lastBreak > $maxLength * 0.7) {
                $extract = mb_substr($extract, 0, $lastBreak);
            } else {
                $extract = $truncated;
            }
            $extract .= "\n\n[Note: Lesson note truncated to fit within context limits.]";
        }

        return <<<PROMPT
You are a Mathematics examination expert. Generate {$count} MATHEMATICS QUESTIONS based STRICTLY on the lesson note below for {$subject} ({$class}, {$term}, Week {$week}).

CRITICAL — These are MATHEMATICS questions. Nearly 100% must test CALCULATION, not theory. Focus on:
- Computational problems (solve, calculate, simplify, evaluate, find)
- Word problems using globally relatable contexts (local measurements, markets) with neutral currency
- Application of formulae and methods shown in the lesson note
- Problems requiring logical reasoning and step-by-step working
- Multi-step problems that combine techniques

QUESTION DISTRIBUTION:
- 50% Direct computational questions (exact methods from the lesson note, requiring calculation)
- 25% Word problems applying methods to real-life situations
- 15% Multi-step problems requiring combined techniques
- 10% Application questions requiring formula selection and substitution

DIFFICULTY:
- 25% Easy (direct substitution, 2-3 step calculations)
- 50% Moderate (3-5 steps, method application, careful working)
- 25% Difficult (multi-step problem-solving, WAEC/NECO/JAMB examination standard)

AVOID: definition questions, list questions, "what is" questions that don't require calculation.

LESSON NOTE CONTENT:
{$extract}

Notation: Use proper mathematical symbols in questions and options — × (not x), ÷ (not /), use <sup> for powers, use √ for square roots, use π for pi, etc.

Each MCQ: 4 UNIQUE options (A/B/C/D), exactly ONE correct answer. Plausible wrong options based on common calculation errors.

Return ONLY valid JSON:
{"objectives":[{"id":1,"question":"Calculate ...","A":"opt","B":"opt","C":"opt","D":"opt","answer":"A"}]{$theoryPart}}
PROMPT;
    }

    protected function extractNoteText($lessonNoteContent): string
    {
        $data = json_decode($lessonNoteContent, true);
        if (!is_array($data)) {
            return $lessonNoteContent;
        }

        $parts = [];

        // Metadata header
        $meta = [];
        if (!empty($data['topic'])) $meta[] = $data['topic'];
        if (!empty($data['subTopic'])) $meta[] = $data['subTopic'];
        if (!empty($data['subject'])) $meta[] = $data['subject'];
        if (!empty($data['class'])) $meta[] = $data['class'];
        if (!empty($data['term'])) $meta[] = $data['term'];
        if (!empty($data['week'])) $meta[] = "Week {$data['week']}";
        if ($meta) $parts[] = implode(' | ', $meta);

        // Core content sections — ordered by importance, most important first
        // (truncation will cut from the bottom)

        if (!empty($data['learningObjectives'])) {
            $objectives = is_array($data['learningObjectives']) ? implode("\n", $data['learningObjectives']) : $data['learningObjectives'];
            $parts[] = "LEARNING OBJECTIVES:\n" . $objectives;
        }

        if (!empty($data['introduction'])) {
            $intro = is_array($data['introduction']) ? json_encode($data['introduction']) : $data['introduction'];
            $parts[] = "INTRODUCTION:\n" . html_entity_decode(strip_tags($intro), ENT_QUOTES | ENT_HTML5);
        }

        if (!empty($data['content'])) {
            $cont = is_array($data['content']) ? json_encode($data['content']) : $data['content'];
            $parts[] = "CONTENT:\n" . html_entity_decode(strip_tags($cont), ENT_QUOTES | ENT_HTML5);
        }

        if (!empty($data['subtopics'])) {
            $subtopics = is_array($data['subtopics']) ? implode("\n- ", $data['subtopics']) : $data['subtopics'];
            $parts[] = "SUBTOPICS:\n- " . $subtopics;
        }

        if (!empty($data['definitions'])) {
            $defs = '';
            foreach ($data['definitions'] as $d) {
                $term = $d['term'] ?? '';
                $def = $d['definition'] ?? '';
                if ($term || $def) $defs .= "- {$term}: {$def}\n";
            }
            if ($defs) $parts[] = "DEFINITIONS:\n" . $defs;
        }

        if (!empty($data['examples'])) {
            $exs = '';
            foreach ($data['examples'] as $ex) {
                $title = $ex['title'] ?? '';
                $desc = $ex['description'] ?? '';
                if ($title || $desc) $exs .= "- {$title}: {$desc}\n";
            }
            if ($exs) $parts[] = "EXAMPLES:\n" . $exs;
        }

        if (!empty($data['practicalApplications'])) {
            $apps = is_array($data['practicalApplications']) ? implode("\n", $data['practicalApplications']) : $data['practicalApplications'];
            $parts[] = "PRACTICAL APPLICATIONS:\n" . $apps;
        }

        if (!empty($data['keyPoints'])) {
            $points = is_array($data['keyPoints']) ? implode("\n", $data['keyPoints']) : $data['keyPoints'];
            $parts[] = "KEY POINTS:\n" . $points;
        }

        if (!empty($data['summary'])) {
            $summary = is_array($data['summary']) ? json_encode($data['summary']) : $data['summary'];
            $parts[] = "SUMMARY:\n" . html_entity_decode(strip_tags($summary), ENT_QUOTES | ENT_HTML5);
        }

        if (!empty($data['detailedNote'])) {
            $dn = is_array($data['detailedNote']) ? json_encode($data['detailedNote']) : $data['detailedNote'];
            $parts[] = "DETAILED NOTE:\n" . html_entity_decode(strip_tags($dn), ENT_QUOTES | ENT_HTML5);
        }

        $text = implode("\n\n", $parts);

        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = trim($text);

        if (!empty($text)) {
            return $text;
        }

        // Fallback: try to extract plain text from the raw JSON
        $fallback = strip_tags(json_encode($data, JSON_UNESCAPED_UNICODE));
        $fallback = html_entity_decode($fallback, ENT_QUOTES | ENT_HTML5);
        return $fallback;
    }

    // --- STORE HELPERS ---

    protected function storeAndReturnLessonPlan(array $plan, array $data, $user, string $teacherName, string $schoolName, string $duration, string $ageRange)
    {
        $plan = $this->normalizeObjectivePhrasing($plan);
        $plan['subject'] = $data['subject'];
        $plan['class'] = $data['class'];
        $plan['term'] = $data['term'];
        $plan['week'] = $data['week'];
        $plan['topic'] = $data['topic'];
        $plan['subTopic'] = $data['subTopic'] ?? '';
        $plan['schoolName'] = $schoolName;
        $plan['teacherName'] = $teacherName;
        $plan['duration'] = $duration;
        $plan['ageRange'] = $ageRange;
        $plan['date'] = now()->format('l, F j, Y');

        JsonDb::init();
        $db = JsonDb::get();
        $teacherId = $user['id'] ?? 'unknown';
        $planId = 'plan_' . uniqid();
        $db['lessonPlans'][] = array_merge($plan, [
            'id' => $planId,
            'teacherId' => $teacherId,
            'createdAt' => now()->toIso8601String(),
        ]);
        JsonDb::save($db);

        Log::info('Lesson plan stored successfully', ['planId' => $planId, 'topic' => $data['topic']]);

        return response()->json([
            'success' => true,
            'plan' => $plan,
            'planId' => $planId,
            'message' => 'Lesson plan generated successfully.',
        ]);
    }

    /**
     * Human-readable suffix describing the underlying AI provider failure,
     * so users see the real cause (bad key, quota, archived model...) instead
     * of a generic "empty response" message.
     */
    protected function aiErrorSuffix(): string
    {
        $err = $this->ai->getLastError();
        if (empty($err)) {
            return '';
        }
        return ' [AI: ' . mb_substr($err, 0, 220) . ']';
    }

    /**
     * Enforce objective style: start with "Learners" + action verb.
     * Rewrites legacy phrasings such as "By the end of the lesson, students
     * should be able to define ..." into "Learners define ...".
     */
    protected function normalizeObjectivePhrasing(array $content): array
    {
        $rewrite = function (string $line): string {
            $trimmed = trim($line);
            if ($trimmed === '' || !preg_match(
                '/^((?:\d+[.)]\s+)?)(?:by\s+the\s+end\s+of\s+(?:the|this)\s+lesson[,]?\s+)?(?:students?|pupils?|learners)\s+(?:(?:should|will|would|can|could|may)\s+)?(?:be\s+able\s+to\s+|to\s+)?(.+)$/is',
                $trimmed,
                $m
            )) {
                return $trimmed;
            }
            return $m[1] . 'Learners ' . lcfirst(trim($m[2]));
        };

        foreach (['behaviouralObjectives', 'learningObjectives', 'objectives'] as $key) {
            if (empty($content[$key])) {
                continue;
            }
            if (is_array($content[$key])) {
                $content[$key] = array_map($rewrite, array_map('strval', $content[$key]));
            } elseif (is_string($content[$key])) {
                $lines = preg_split('/\r\n|\r|\n/', $content[$key]);
                $content[$key] = implode("\n", array_map($rewrite, $lines));
            }
        }

        return $content;
    }

    protected function storeAndReturnLessonNote(array $note, array $data, $user, string $periods, string $difficulty, string $ageRange)
    {
        // Enforce "Learners <verb> ..." objective style
        $note = $this->normalizeObjectivePhrasing($note);

        // Unwrap lesson_note wrapper key (some AIs nest content inside it)
        if (!empty($note['lesson_note']) && is_array($note['lesson_note'])) {
            $inner = $note['lesson_note'];
            foreach (['content','lesson_content','topic','learningObjectives','learning_objectives','definitions','examples','summary','keyPoints','key_points','evaluation','evaluationQuestions','introduction','subtopics','practicalApplications','illustrations','advantagesDisadvantages','classroomActivities','assignment'] as $k) {
                if (isset($inner[$k]) && !isset($note[$k])) {
                    $note[$k] = $inner[$k];
                }
            }
        }

        // Normalize content key — try multiple possible keys the AI might use
        $contentKeys = ['content', 'detailedNote', 'body', 'noteContent', 'htmlContent', 'lessonContent', 'mainContent', 'fullContent', 'definition'];
        if (empty($note['content'] ?? '')) {
            // content as object → convert to HTML string
            if (!empty($note['content']) && is_array($note['content'])) {
                $note['content'] = $this->noteContentObjectToHtml($note['content']);
            } else {
                foreach ($contentKeys as $key) {
                    if (!empty($note[$key] ?? '')) {
                        if (is_array($note[$key])) {
                            $note['content'] = $this->noteContentObjectToHtml($note[$key]);
                        } else {
                            $note['content'] = $note[$key];
                        }
                        break;
                    }
                }
            }
        }
        // lesson_content array → convert to HTML
        if (empty($note['content'] ?? '') && !empty($note['lesson_content']) && is_array($note['lesson_content'])) {
            $note['content'] = $this->noteContentObjectToHtml($note['lesson_content']);
        }
        // If content is an object, convert to HTML
        if (!empty($note['content']) && is_array($note['content'])) {
            $note['content'] = $this->noteContentObjectToHtml($note['content']);
        }

        // Normalize field name inconsistencies
        if (!empty($note['key_points']) && empty($note['keyPoints'])) {
            $note['keyPoints'] = $note['key_points'];
        }
        if (!empty($note['learning_objectives']) && empty($note['learningObjectives'])) {
            $note['learningObjectives'] = $note['learning_objectives'];
        }
        if (!empty($note['evaluation']) && empty($note['evaluationQuestions'])) {
            $note['evaluationQuestions'] = is_array($note['evaluation']) ? $note['evaluation'] : [$note['evaluation']];
        }
        if (!empty($note['objectives']) && empty($note['learningObjectives'])) {
            $note['learningObjectives'] = $note['objectives'];
        }

        $note['subject'] = $data['subject'];
        $note['class'] = $data['class'];
        $note['term'] = $data['term'];
        $note['week'] = $data['week'];
        $note['topic'] = $data['topic'];
        $note['subTopic'] = $data['subTopic'] ?? '';
        $note['difficulty'] = $difficulty;
        $note['periods'] = $periods;
        $note['ageRange'] = $ageRange;

        JsonDb::init();
        $db = JsonDb::get();
        $teacherId = $user['id'] ?? 'unknown';
        $noteId = 'note_' . uniqid();
        $db['lessonNotes'][] = array_merge($note, [
            'id' => $noteId,
            'teacherId' => $teacherId,
            'createdAt' => now()->toIso8601String(),
        ]);
        JsonDb::save($db);

        Log::info('Lesson note stored successfully', ['noteId' => $noteId, 'topic' => $data['topic']]);

        return response()->json([
            'success' => true,
            'note' => $note,
            'noteId' => $noteId,
            'message' => 'Lesson note generated successfully.',
        ]);
    }

    /**
     * Convert a nested content object/array from the AI into an HTML string.
     */
    private function noteContentObjectToHtml(array $content): string
    {
        $parts = [];
        foreach ($content as $key => $val) {
            if (is_string($val)) {
                $parts[] = "<p>{$val}</p>";
            } elseif (is_array($val)) {
                $heading = $val['heading'] ?? $val['title'] ?? $val['subtopic'] ?? $key;
                $body = $val['body'] ?? $val['explanation'] ?? $val['description'] ?? $val['content'] ?? '';
                $points = $val['points'] ?? $val['sub_headings'] ?? $val['solution_steps'] ?? [];
                $example = $val['example'] ?? $val['final_answer'] ?? '';

                if (!empty($heading)) {
                    $parts[] = "<h4>" . (is_string($heading) ? $heading : $key) . "</h4>";
                }
                if (!empty($body)) {
                    $parts[] = "<p>" . (is_string($body) ? $body : '') . "</p>";
                }
                if (!empty($example)) {
                    $parts[] = "<p><strong>Example:</strong> " . (is_string($example) ? $example : '') . "</p>";
                }
                if (is_array($points)) {
                    foreach ($points as $pt) {
                        if (is_string($pt)) {
                            $parts[] = "<li>" . $pt . "</li>";
                        } elseif (is_array($pt)) {
                            $ptHeading = $pt['title'] ?? $pt['heading'] ?? '';
                            $ptDesc = $pt['description'] ?? $pt['body'] ?? '';
                            if ($ptHeading) {
                                $parts[] = "<li><strong>" . $ptHeading . ":</strong> " . $ptDesc . "</li>";
                            }
                        }
                    }
                }
            }
        }
        return implode("\n", $parts);
    }

    public function deleteLessonNote($noteId)
    {
        $user = Session::get('user');
        if (!$user) return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        JsonDb::init();
        $db = JsonDb::get();
        $found = null;
        foreach ($db['lessonNotes'] as $n) { if ($n['id'] === $noteId) { $found = $n; break; } }
        if ($found && ($user['role'] ?? '') !== 'admin' && ($found['teacherId'] ?? '') !== ($user['id'] ?? '')) {
            return response()->json(['success' => false, 'error' => 'You can only delete your own lesson notes'], 403);
        }
        $db['lessonNotes'] = array_values(array_filter($db['lessonNotes'], fn($n) => $n['id'] !== $noteId));
        JsonDb::save($db);
        return response()->json(['success' => true, 'message' => 'Lesson note deleted.']);
    }

    public function deleteLessonPlan($planId)
    {
        $user = Session::get('user');
        if (!$user) return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        JsonDb::init();
        $db = JsonDb::get();
        $found = null;
        foreach ($db['lessonPlans'] as $p) { if ($p['id'] === $planId) { $found = $p; break; } }
        if ($found && ($user['role'] ?? '') !== 'admin' && ($found['teacherId'] ?? '') !== ($user['id'] ?? '')) {
            return response()->json(['success' => false, 'error' => 'You can only delete your own lesson plans'], 403);
        }
        $db['lessonPlans'] = array_values(array_filter($db['lessonPlans'], fn($p) => $p['id'] !== $planId));
        JsonDb::save($db);
        return response()->json(['success' => true, 'message' => 'Lesson plan deleted.']);
    }

    public function deleteQuestionSet($id)
    {
        $user = Session::get('user');
        if (!$user) return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        JsonDb::init();
        $db = JsonDb::get();
        $found = null;
        foreach ($db['questionSets'] ?? [] as $qs) { if ($qs['id'] === $id) { $found = $qs; break; } }
        if (!$found) return response()->json(['success' => false, 'error' => 'Question set not found'], 404);
        if (($user['role'] ?? '') !== 'admin' && ($found['teacherId'] ?? '') !== ($user['id'] ?? '')) {
            return response()->json(['success' => false, 'error' => 'You can only delete your own question sets'], 403);
        }
        $db['questionSets'] = array_values(array_filter($db['questionSets'] ?? [], fn($qs) => ($qs['id'] ?? '') !== $id));
        JsonDb::save($db);
        return response()->json(['success' => true, 'message' => 'Question set deleted.']);
    }

    /**
     * Last-resort fallback when AI fails to produce valid questions.
     * Tries one final AI call with a minimal prompt, then falls back to ContentGenerator.
     */
    private function fallbackToContentGenerator(array $data): \Illuminate\Http\JsonResponse
    {
        Log::warning('Attempting last-resort AI fallback for questions', [
            'subject' => $data['subject'],
            'topic' => $data['topic'],
            'count' => $data['count'],
        ]);

        // First try: simple AI prompt without json_mode
        try {
            $subject = $data['subject'];
            $topic = $data['topic'];
            $count = $data['count'];
            $class = $data['class'] ?? 'SS1';

            $subtopic = $data['subTopic'] ?? '';
            $subtopicLine = $subtopic ? " Sub-topic: \"{$subtopic}\"." : '';
            $simplePrompt = "You are an exam expert for {$subject} ({$class} level). "
                . "CRITICAL: Generate {$count} multiple-choice questions that DIRECTLY TEST \"{$topic}\" in {$subject} for {$class} level.{$subtopicLine}\n\n"
                . "SUBJECT: {$subject}. TOPIC: \"{$topic}\". CLASS: {$class}.\n"
                . "EVERY question stem MUST contain the exact word \"{$topic}\" or a direct subtopic reference. Questions without {$topic} in the stem are OFF-TOPIC.\n\n"
                . "Vary question styles — use at most 2 'What/Why/How' questions per 10. Include: definitions, completions (___), scenarios, classifications, comparisons, negatives (EXCEPT), calculations (if applicable), and true/false.\n\n"
                . "Return ONLY a JSON array. Each item: {\"id\":number,\"question\":\"stem that mentions {$topic}\",\"A\":\"opt\",\"B\":\"opt\",\"C\":\"opt\",\"D\":\"opt\",\"answer\":\"A\"}.\n\n"
                . "Example: [{\"id\":1,\"question\":\"The correct definition of {$topic} is:\",\"A\":\"opt1\",\"B\":\"opt2\",\"C\":\"opt3\",\"D\":\"opt4\",\"answer\":\"A\"}]";

            $retryResponse = $this->ai->generate($simplePrompt, false, 8192, 0.5);

            if (!empty(trim($retryResponse))) {
                $retryData = json_decode($retryResponse, true);
                if (!is_array($retryData) || empty($retryData)) {
                    $cleaned = $this->extractJson($retryResponse);
                    if ($cleaned !== null) {
                        $retryData = $cleaned;
                    }
                }
                if (is_array($retryData) && !empty($retryData)) {
                    $items = $retryData['objectives'] ?? $retryData;
                    if (is_array($items) && !empty($items) && isset($items[0])) {
                        $items = $this->normalizeQuestionFields($items);
                        $items = $this->filterValidQuestions($items, $topic, $subject, false, 1) ?? [];
                        if (count($items) > 0) {
                            $items = $this->shuffleAnswers($items);
                            $actual = count($items);
                            return response()->json([
                                'success' => true,
                                'questions' => ['objectives' => $items],
                                'count' => $count,
                                'message' => $actual . ' out of ' . $count . ' questions generated.',
                            ]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Simple AI fallback failed', ['error' => $e->getMessage()]);
        }

        // Second try: ContentGenerator as absolute last resort
        Log::warning('Falling back to ContentGenerator for questions', $data);
        try {
            $fallback = ContentGenerator::generateQuestions(
                $data['subject'], $data['topic'], $data['count'],
                $data['includeTheory'] ?? false
            );
            $items = $fallback['objectives'] ?? $fallback;
            if (is_array($items) && !empty($items)) {
                $items = $this->normalizeQuestionFields($items);
                $items = $this->filterValidQuestions($items, $topic, $subject, false, 1) ?? [];
                if (count($items) === 0) {
                    // The static question bank does not embed the topic keyword
                    // in its stems, so the strict topic filter rejects them all.
                    // Regenerate with the topic-anchored archetype engine, which
                    // guarantees every stem mentions the topic.
                    Log::warning('Question bank failed the topic filter — regenerating topic-anchored questions');
                    $items = ContentGenerator::generateTopicAnchoredQuestions($subject, $topic, $data['count']);
                    $items = $this->normalizeQuestionFields($items);
                    $items = $this->filterValidQuestions($items, $topic, $subject, false, 1) ?? [];
                }
                if (count($items) > 0) {
                    $items = $this->shuffleAnswers($items);
                    $actual = count($items);
                    return response()->json([
                        'success' => true,
                        'questions' => ['objectives' => $items],
                        'count' => $data['count'],
                        'message' => $actual . ' out of ' . $data['count'] . ' questions generated.',
                        'fallback' => true,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('ContentGenerator fallback also failed', ['error' => $e->getMessage()]);
        }

        Log::error('All question generation attempts failed');
        return response()->json([
            'success' => false,
            'error' => 'Unable to generate questions at this time. Please try again with a different topic or try again later.',
        ], 503);
    }

    /**
     * Fill a question set up to the requested count using the offline
     * topic-anchored generator. The AI may return fewer questions than
     * requested (output-token truncation) or have some rejected during
     * validation — this guarantees the user always receives their full set.
     */
    private function topUpQuestionsFromContentGenerator(array $items, array $data): array
    {
        $target = max(1, (int) $data['count']);
        if (count($items) >= $target) {
            return array_slice($items, 0, $target);
        }

        $remaining = $target - count($items);
        $fill = ContentGenerator::generateTopicAnchoredQuestions($data['subject'], $data['topic'], $remaining);
        $fill = $this->normalizeQuestionFields($fill);
        $fill = $this->filterValidQuestions($fill, $data['topic'], $data['subject'], false, 1) ?? [];

        if (!empty($fill)) {
            Log::warning('Topping up questions from offline generator', [
                'had' => count($items),
                'filling' => count($fill),
                'requested' => $target,
            ]);
            $items = array_merge($items, array_slice($fill, 0, $remaining));
        }

        return array_slice($items, 0, $target);
    }

    /**
     * Validate questions quality and retry up to MAX_RETRIES times
     * if validation fails. Returns the validated items array or null
     * if all retries are exhausted (caller should handle fallback).
     */
    private function validateAndRetryQuestions(array $questionItems, string $prompt, array $data, bool $hasLessonNote = false): ?array
    {
        $subject = $data['subject'];
        $topic = $data['topic'];
        $class = $data['class'] ?? 'SS1';
        $targetCount = $data['count'] ?? 10;
        $minAcceptable = max(1, (int) ceil($targetCount * 0.95));
        $currentItems = $this->normalizeQuestionFields($questionItems);

        for ($attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++) {
            if ($attempt > 0) {
                try {
                    $retryPrompt = $this->buildStrictRetryPrompt($prompt, $subject, $topic, $class, 'questions', $targetCount);
                    if (!empty($validationErrors)) {
                        $retryPrompt .= "\n\nPREVIOUS ISSUES:\n" . implode("\n", array_slice($validationErrors, 0, 5));
                    }
                    $retryResponse = $this->ai->generate($retryPrompt, true, 16384, 0.4);

                    if ($this->isRefusal($retryResponse)) {
                        break;
                    }

                    $retryData = json_decode($retryResponse, true);
                    if (!is_array($retryData) || empty($retryData)) {
                        $cleaned = $this->extractJson($retryResponse);
                        if ($cleaned !== null) {
                            $retryData = $cleaned;
                        }
                    }
                    if (is_array($retryData) && !empty($retryData)) {
                        $newItems = $retryData['objectives'] ?? $retryData;
                        $newItems = $this->normalizeQuestionFields($newItems);
                        $newItems = $this->filterValidQuestions($newItems, $topic, $subject, false, 1);
                        if ($newItems !== null) {
                            $currentItems = array_merge($currentItems, $newItems);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Question retry attempt failed', ['error' => $e->getMessage()]);
                }
            }

            $valid = $this->filterValidQuestions($currentItems, $topic, $subject, false, $minAcceptable);
            if ($valid !== null && count($valid) >= $targetCount) {
                return array_slice($valid, 0, $targetCount);
            }

            if ($valid !== null) {
                $currentItems = $valid;
            }

            $validationErrors = $this->validateQuestionPool($currentItems, $topic, $subject, false);
            Log::warning("Question pool validation (attempt {$attempt})", [
                'errors' => array_slice($validationErrors ?? [], 0, 5),
                'valid_count' => $valid ? count($valid) : 0,
                'target' => $targetCount,
            ]);
        }

        // If we still don't have enough, fill the remainder offline so users
        // always receive their requested count even when the AI truncates its
        // output (finish_reason=length) or rate-limits every retry.
        if (count($currentItems) < $targetCount && count($currentItems) > 0) {
            $remaining = $targetCount - count($currentItems);
            $fillItems = ContentGenerator::generateTopicAnchoredQuestions($subject, $topic, $remaining);
            $fillItems = $this->normalizeQuestionFields($fillItems);
            $fillItems = $this->filterValidQuestions($fillItems, $topic, $subject, false, 1) ?? [];
            if (!empty($fillItems)) {
                Log::warning('Filling remaining questions from offline generator', [
                    'remaining' => $remaining,
                    'filled' => count($fillItems),
                ]);
                $currentItems = array_merge($currentItems, array_slice($fillItems, 0, $remaining));
            }
        }

        $currentItems = $this->normalizeQuestionFields($currentItems);
        $currentItems = $this->filterValidQuestions($currentItems, $topic, $subject, false, 1) ?? [];
        return count($currentItems) > 0 ? array_slice($currentItems, 0, $targetCount) : null;
    }

    /**
     * Filter questions that pass validation. Returns the filtered array
     * if at least $minCount questions survive, or null otherwise.
     */
    private function filterValidQuestions(array $questions, string $topic, string $subject, bool $skipTopicCheck, int $minCount): ?array
    {
        $requiredCount = $minCount;
        $valid = [];
        $topicLower = strtolower(trim($topic));
        $topicWords = array_filter(explode(' ', $topicLower), fn($w) => strlen($w) > 2);
        if (empty($topicWords)) {
            $topicWords = [$topicLower];
        }
        $topicWordCount = count($topicWords);

        foreach ($questions as $q) {
            if (!is_array($q)) continue;
            $questionText = trim($q['question'] ?? '');
            if (empty($questionText)) continue;

            // Check 4 non-empty options
            $hasOptions = true;
            $options = [];
            foreach (['A', 'B', 'C', 'D'] as $letter) {
                $opt = trim($q[$letter] ?? '');
                if (empty($opt)) { $hasOptions = false; break; }
                $options[$letter] = $opt;
            }
            if (!$hasOptions) continue;

            // Check unique options
            if (count(array_unique(array_map('strtolower', $options))) < 4) continue;

            // Check valid answer key
            $answer = strtoupper(trim($q['answer'] ?? ''));
            if (!in_array($answer, ['A', 'B', 'C', 'D'], true)) continue;

            // Topic relevance — require keyword in question STEM (not just options)
            $qTextLower = strtolower($questionText);
            $keywordHits = 0;
            foreach ($topicWords as $word) {
                if (str_contains($qTextLower, $word)) { $keywordHits++; }
            }
            // Require at least one keyword hit in question STEM for single-word topics,
            // or at least half the words for multi-word topics
            $requiredHits = $topicWordCount === 1 ? 1 : max(1, (int) ceil($topicWordCount / 2));
            if ($keywordHits < $requiredHits) continue;

            $valid[] = $q;
        }

        return count($valid) >= $minCount ? $valid : null;
    }

    // --- QUALITY VALIDATION ---

    /**
     * Validate a set of questions for quality:
     * - No duplicate question text
     * - All 4 options are unique within each question
     * - Answer key is valid (A/B/C/D)
     * - Topic keywords appear in question text
     */
    private function validateQuestionPool(array $questions, string $topic, string $subject, bool $skipTopicCheck = false): array
    {
        $errors = [];
        $seenQuestions = [];
        $answerCount = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0];
        $topicLower = strtolower(trim($topic));
        $topicWords = array_filter(explode(' ', $topicLower), fn($w) => strlen($w) > 2);
        if (empty($topicWords)) {
            $topicWords = [$topicLower];
        }

        foreach ($questions as $i => $q) {
            if (!is_array($q)) continue;
            $qNum = $i + 1;
            $questionText = trim($q['question'] ?? '');

            // Check question text exists
            if (empty($questionText)) {
                $errors[] = "Question {$qNum} has empty question text";
                continue;
            }

            // Check for duplicate question text — strict threshold to prevent repeats
            $qLower = strtolower(trim(preg_replace('/[^a-z0-9\s]/', '', $questionText)));
            if (!empty($qLower)) {
                foreach ($seenQuestions as $seen) {
                    similar_text($qLower, $seen, $pct);
                    if ($pct > 75) {
                        $errors[] = "Question {$qNum} is too similar to another question ({$pct}% match)";
                        break;
                    }
                }
                $seenQuestions[] = $qLower;
            }

            // Check answer key is valid
            $answer = strtoupper(trim($q['answer'] ?? ''));
            if (!in_array($answer, ['A', 'B', 'C', 'D'], true)) {
                $errors[] = "Question {$qNum} has invalid answer '{$answer}' (must be A, B, C, or D)";
                continue;
            }
            $answerCount[$answer]++;

            // Collect options
            $options = [];
            foreach (['A', 'B', 'C', 'D'] as $letter) {
                $opt = trim($q[$letter] ?? '');
                if (empty($opt)) {
                    $errors[] = "Question {$qNum} option {$letter} is empty";
                }
                $options[$letter] = $opt;
            }

            // Check all 4 options are different
            $uniqueOptions = array_unique(array_values($options));
            if (count($uniqueOptions) < 4) {
                $repeated = array_keys(array_filter(array_count_values(array_map('strtolower', $options)), fn($c) => $c > 1));
                $errors[] = "Question {$qNum} has duplicate options: " . implode(', ', $repeated);
            }

            // Check topic relevance — require keyword in question STEM
            if (!$skipTopicCheck) {
                $qTextLower = strtolower($questionText);
                $topicMatchCount = 0;
                foreach ($topicWords as $word) {
                    if (str_contains($qTextLower, $word)) {
                        $topicMatchCount++;
                    }
                }
                $requiredHits = count($topicWords) === 1 ? 1 : max(1, (int) ceil(count($topicWords) / 2));
                if ($topicMatchCount < $requiredHits) {
                    $errors[] = "Question {$qNum} does not reference '{$topic}' in its stem";
                }
            }
        }

        // Check answer distribution (should be roughly even)
        $total = count($questions);
        if ($total >= 10) {
            $expected = $total / 4;
            foreach ($answerCount as $letter => $count) {
                if ($count === 0) {
                    $errors[] = "No correct answers placed in option {$letter} — distribution is severely imbalanced";
                } elseif ($total >= 20 && $count < $expected * 0.3) {
                    $errors[] = "Option {$letter} has only {$count} correct answers (expected ~{$expected}) — distribution is imbalanced";
                }
            }
        }

        return $errors;
    }

    /**
     * Randomly shuffle the correct answer position among A/B/C/D
     * while keeping the correct answer content attached to the new position.
     */
    /**
     * Normalize question field names from various AI output formats to standard names.
     * Maps: text/stem/questionText -> question, correctAnswer/correct_answer/ans -> answer,
     * option_a/options.A -> A, etc.
     */
    private function normalizeQuestionFields(array $questions): array
    {
        $normalized = [];
        foreach ($questions as $i => $q) {
            if (!is_array($q)) continue;
            $item = [
                'id' => $q['id'] ?? ($i + 1),
                'question' => $q['question'] ?? $q['text'] ?? $q['stem'] ?? $q['questionText'] ?? $q['q'] ?? '',
                'A' => $q['A'] ?? $q['option_a'] ?? (isset($q['options']['A']) ? $q['options']['A'] : ''),
                'B' => $q['B'] ?? $q['option_b'] ?? (isset($q['options']['B']) ? $q['options']['B'] : ''),
                'C' => $q['C'] ?? $q['option_c'] ?? (isset($q['options']['C']) ? $q['options']['C'] : ''),
                'D' => $q['D'] ?? $q['option_d'] ?? (isset($q['options']['D']) ? $q['options']['D'] : ''),
                'answer' => $q['answer'] ?? $q['correctAnswer'] ?? $q['correct_answer'] ?? $q['ans'] ?? '',
            ];
            $normalized[] = $item;
        }
        return $normalized;
    }

    private function shuffleAnswers(array $questions): array
    {
        $letters = ['A', 'B', 'C', 'D'];
        $positionsUsed = [];

        foreach ($questions as $i => $q) {
            if (!is_array($q)) continue;
            $currentAnswer = strtoupper(trim($q['answer'] ?? ''));
            if (!in_array($currentAnswer, $letters, true)) {
                $currentAnswer = $letters[$i % 4];
            }
            $correctContent = $q[$currentAnswer] ?? '';

            // Pick a random position, avoiding the same position as previous question
            $available = $letters;
            if (!empty($positionsUsed)) {
                $lastPos = end($positionsUsed);
                $available = array_values(array_filter($letters, fn($l) => $l !== $lastPos));
            }
            $newPos = $available[array_rand($available)];
            $positionsUsed[] = $newPos;

            // Build new option set with correct answer at new position
            $newOptions = [];
            $wrongContents = [];
            foreach ($letters as $l) {
                if ($l !== $currentAnswer) {
                    $wrongContents[] = $q[$l] ?? '';
                }
            }
            shuffle($wrongContents);

            $wrongIdx = 0;
            foreach ($letters as $l) {
                if ($l === $newPos) {
                    $newOptions[$l] = $correctContent ?: ($q[$l] ?? '');
                } else {
                    $newOptions[$l] = $wrongContents[$wrongIdx++] ?? '';
                }
            }

            $questions[$i]['A'] = $newOptions['A'];
            $questions[$i]['B'] = $newOptions['B'];
            $questions[$i]['C'] = $newOptions['C'];
            $questions[$i]['D'] = $newOptions['D'];
            $questions[$i]['answer'] = $newPos;
        }

        return $questions;
    }

    // --- VALIDATION ---

    private function isRelevantToTopic(array $content, string $type, string $subject, string $topic, string $class): bool
    {
        $topicLower = strtolower(trim($topic));
        $subjectLower = strtolower(trim($subject));

        $allText = '';

        if ($type === 'lesson_plan') {
            $toStr = fn($v) => is_string($v) ? $v : (is_array($v) ? json_encode($v) : '');
            $allText = implode(' ', $content['behaviouralObjectives'] ?? []) . ' ' .
                       $toStr($content['previousKnowledge'] ?? '') . ' ' .
                       (is_array($content['instructionalMaterials'] ?? null) ? implode(' ', $content['instructionalMaterials']) : $toStr($content['instructionalMaterials'] ?? '')) . ' ' .
                       implode(' ', array_map(fn($s) => $toStr($s['teacherActivities'] ?? '') . ' ' . $toStr($s['learnerActivities'] ?? '') . ' ' . $toStr($s['learningPoints'] ?? ''), $content['lessonSteps'] ?? [])) . ' ' .
                       $toStr($content['evaluation'] ?? '') . ' ' .
                       $toStr($content['summary'] ?? '');
        } elseif ($type === 'lesson_note') {
            $noteSubtopics = $content['subtopics'] ?? [];
            $toStr = fn($v) => is_string($v) ? $v : (is_array($v) ? json_encode($v) : '');
            $allText = $toStr($content['content'] ?? '') . ' ' .
                       $toStr($content['introduction'] ?? '') . ' ' .
                       $toStr($content['summary'] ?? '') . ' ' .
                       $toStr($content['detailedNote'] ?? '') . ' ' .
                       (is_array($noteSubtopics) ? implode(' ', $noteSubtopics) : '');
        } elseif ($type === 'questions') {
            $items = $content['objectives'] ?? $content;
            if (is_array($items)) {
                $parts = [];
                foreach ($items as $q) {
                    $parts[] = $q['question'] ?? '';
                    foreach (['A', 'B', 'C', 'D', 'options', 'optionA', 'optionB', 'optionC', 'optionD'] as $opt) {
                        if (isset($q[$opt])) {
                            $parts[] = is_string($q[$opt]) ? $q[$opt] : '';
                        }
                    }
                }
                $allText = implode(' ', $parts);
            }
        }

        $allText = strtolower($allText);

        if (empty(trim($allText))) {
            return false;
        }

        $topicWords = array_filter(explode(' ', $topicLower), fn($w) => strlen($w) > 2);
        if (empty($topicWords)) {
            $topicWords = [$topicLower];
        }

        $topicMatchCount = 0;
        foreach ($topicWords as $word) {
            if (str_contains($allText, $word)) {
                $topicMatchCount++;
            }
        }
        $topicScore = $topicMatchCount / count($topicWords);

        $subjectWords = array_filter(explode(' ', $subjectLower), fn($w) => strlen($w) > 2);
        $subjectFound = empty($subjectWords) || !empty(array_filter($subjectWords, fn($w) => str_contains($allText, $w)));

        $pass = true;
        $reasons = [];

        if ($topicScore < 0.2) {
            $pass = false;
            $reasons[] = "topicScore={$topicScore}";
        }
        if (!$subjectFound && $topicScore < 0.5) {
            $pass = false;
            $reasons[] = 'subjectMissing';
        }

        if (!$pass) {
            Log::warning("AI relevance rejected [{$type}]: " . implode(', ', $reasons), [
                'subject' => $subject, 'topic' => $topic, 'class' => $class,
            ]);
        }

        return $pass;
    }

    private function isRefusal(string $response): bool
    {
        $lower = strtolower(trim($response));

        if (str_starts_with($lower, 'i cannot') && (str_contains($lower, 'generate') || str_contains($lower, 'provide') || str_contains($lower, 'write') || str_contains($lower, 'answer'))) {
            return true;
        }

        $refusalPhrases = [
            'cannot generate content',
            'cannot provide content',
            "can't generate content",
            "can't provide content",
            'outside my knowledge',
            'outside your knowledge',
            'outside my expertise',
            'i am not able to generate',
            'i am not able to provide',
            'i don\'t have information about',
            'i do not have information about',
            'not within my knowledge',
            'beyond my capabilities',
            'unable to generate content',
            'unable to provide content',
            'sorry, but i cannot',
            'i apologize, but i cannot',
            'as an ai, i cannot generate',
            'as an ai language model, i cannot',
        ];
        foreach ($refusalPhrases as $phrase) {
            if (str_contains($lower, $phrase)) {
                return true;
            }
        }
        return false;
    }

    protected function buildStrictRetryPrompt(string $originalPrompt, string $subject, string $topic, string $class, string $type = 'questions', int $count = 20): string
    {
        if ($type === 'lesson_note') {
            return "You are a curriculum expert. Your ONLY task: Write a DETAILED LESSON NOTE about \"{$topic}\" in {$subject} for {$class}.\n\n"
                 . "PREVIOUS ATTEMPT REJECTED — REASON: The lesson note did not focus on the requested topic.\n\n"
                 . "CRITICAL INSTRUCTIONS:\n"
                 . "- The topic is \"{$topic}\". Write ONLY about \"{$topic}\".\n"
                 . "- Analyze the topic and choose headings that are naturally relevant. Do NOT force any section.\n"
                 . "- Every sentence must be about \"{$topic}\".\n"
                 . "- Use globally relatable examples without mentioning Nigeria or specific cities like Lagos or Kano.\n"
                 . "- For Mathematics/Physics/Chemistry: INCLUDE AT LEAST 5 FULLY SOLVED WORKED EXAMPLES with every step shown.\n"
                 . "- Use proper mathematical notation: × (not x), ÷ (not /), <sup> for powers, <sub> for indices/chemical formulae\n"
                 . "- Format fractions with CSS inline-block — NEVER slanted slashes\n"
                 . "- Use → and ⇌ for reaction arrows, state symbols (s), (l), (g), (aq) for Chemistry\n"
                 . "- Use √ for square roots, π for pi, θ, ω, λ, μ, ρ, Δ for Greek letters\n"
                 . "- Scientific notation: 6.02 × 10<sup>23</sup> (not E-notation)\n"
                 . "- Show ALL calculation steps — do not skip any\n"
                 . "- Return ONLY valid JSON with this flexible structure:\n"
                 . '  {"topic":"...","introduction":"...","content":"FULL HTML with <h3>/<h4> headings, minimum 5 worked examples","sections":[{"heading":"...","content":"..."}],"evaluationQuestions":["..."],"keyPoints":["..."]}' . "\n";
        }

        return "You are an examination expert for {$subject} ({$class}).\n\n"
             . "CRITICAL: Generate {$count} objective questions about \"{$topic}\". Do not mention any specific country or cities like Lagos or Kano.\n\n"
             . "PREVIOUS ATTEMPT REJECTED — QUESTIONS WERE OFF-TOPIC OR TOO SIMPLE.\n\n"
             . "STRICT RULES — FOLLOW EVERY ONE:\n"
             . "- SUBJECT: {$subject}. TOPIC: \"{$topic}\". CLASS: {$class}.\n"
             . "- EVERY question stem MUST contain the exact word \"{$topic}\" or one of its key subtopics.\n"
             . "- Every question must test knowledge specifically about {$topic} in {$subject}.\n"
             . "- If the stem doesn't mention {$topic}, the question is REJECTED.\n"
             . "- Do not add phrases like \"in the Nigerian context\" or city names.\n"
             . "- Vary styles: directives (State/Define/List), fill-the-blank (___), scenarios, classifications, compare/contrast, cause-effect, All-EXCEPT, calculations, true/false.\n"
             . "- At most 2 WH-word starters per 10 questions.\n"
             . "- 4 UNIQUE options (A/B/C/D), exactly ONE correct answer.\n"
             . "- Write questions appropriate for a {$class} student per standard curriculum for that level.\n"
             . "- For Mathematics questions: nearly 100% must be CALCULATION-BASED. No definition/list/state questions.\n"
             . "- For Physics questions: 80% calculation, 20% theory. Use proper units and scientific notation.\n"
             . "- Use proper notation: × (not x), ÷ (not /), <sup> for powers, √ for square roots, π for pi\n"
             . "- Format fractions with CSS — NEVER slanted slashes\n"
             . "- Return ONLY valid JSON in this format: {\"objectives\":[{\"id\":1,\"question\":\"stem that mentions {$topic}\",\"A\":\"opt\",\"B\":\"opt\",\"C\":\"opt\",\"D\":\"opt\",\"answer\":\"A\"}]}\n";
    }

    private function extractJson(string $text): ?array
    {
        $text = trim($text);
        if (empty($text)) {
            return null;
        }

        // Remove BOM characters and all markdown fences
        $text = preg_replace('/^[\xEF\xBB\xBF\xFE\xFF]+/', '', $text);
        $text = preg_replace('/```(?:json)?\s*/i', '', $text);
        $text = str_replace('`', '', $text);
        $text = trim($text);

        // Fix trailing commas before closing braces/brackets
        $text = preg_replace('/,\s*([}\]])/', '$1', $text);

        // Try direct decode first
        $decoded = json_decode($text, true);
        if (is_array($decoded) && !empty($decoded)) {
            return $decoded;
        }

        // Try to extract each top-level JSON block with proper bracket matching
        $blocks = $this->extractJsonBlocks($text);
        foreach ($blocks as $block) {
            $candidate = preg_replace('/,\s*([}\]])/', '$1', trim($block));
            $decoded = json_decode($candidate, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }

        // Last resort: brute-force search — try every substring between { and }
        // that balances braces, to catch deeply nested or oddly formatted JSON
        $decoded = $this->bruteForceFindJson($text);
        if ($decoded !== null) {
            // If this is a single question object salvaged from a TRUNCATED
            // response, prefer the complete list of salvaged questions so the
            // caller receives every question emitted before the cut-off.
            if ($this->looksLikeQuestion($decoded)) {
                $salvaged = $this->extractAllQuestionObjects($text);
                if (count($salvaged) > 0) {
                    return $salvaged;
                }
            }
            return $decoded;
        }

        // Last resort 2: the JSON may be TRUNCATED (e.g. the AI hit its output
        // token limit and finish_reason=length). json_decode fails on an
        // unclosed object, so salvage every complete, balanced question object
        // that was emitted before the cut-off and return them as a list.
        $salvaged = $this->extractAllQuestionObjects($text);
        if (count($salvaged) > 0) {
            return $salvaged;
        }

        return null;
    }

    /**
     * Salvage complete question objects from a malformed or truncated JSON
     * response. Scans the raw text for every balanced {…} block that looks
     * like a question (has a "question" stem plus at least one option) and
     * returns them as a list.
     */
    private function extractAllQuestionObjects(string $text): array
    {
        $text = trim($text);
        if (empty($text)) {
            return [];
        }

        // Remove BOM characters and all markdown fences
        $text = preg_replace('/^[\xEF\xBB\xBF\xFE\xFF]+/', '', $text);
        $text = preg_replace('/```(?:json)?\s*/i', '', $text);
        $text = str_replace('`', '', $text);

        $objects = [];
        $len = strlen($text);

        for ($i = 0; $i < $len; $i++) {
            if ($text[$i] !== '{') {
                continue;
            }

            $depth = 0;
            $inString = false;
            $escaped = false;
            $closeAt = -1;

            for ($j = $i; $j < $len; $j++) {
                $c = $text[$j];
                if ($escaped) { $escaped = false; continue; }
                if ($c === '\\') { $escaped = true; continue; }
                if ($c === '"') { $inString = !$inString; continue; }
                if ($inString) continue;

                if ($c === '{') {
                    $depth++;
                } elseif ($c === '}') {
                    $depth--;
                    if ($depth === 0) {
                        $closeAt = $j;
                        break;
                    }
                }
            }

            if ($closeAt !== -1) {
                // Balanced object — try to decode it as a question.
                $block = substr($text, $i, $closeAt - $i + 1);
                $candidate = preg_replace('/,\s*([}\]])/', '$1', $block);
                $decoded = json_decode($candidate, true);
                if (is_array($decoded) && $this->looksLikeQuestion($decoded)) {
                    $objects[] = $decoded;
                }
                // Skip past this balanced block.
                $i = $closeAt;
            }
            // Else: unbalanced object (e.g. the truncated top-level
            // {"objectives":[…) — keep scanning inside it for balanced
            // question objects.
        }

        return $objects;
    }

    /**
     * Quick heuristic: does this decoded object look like a single
     * multiple-choice question?
     */
    private function looksLikeQuestion(array $item): bool
    {
        if (empty($item['question']) || !is_scalar($item['question'])) {
            return false;
        }

        $optionFound = false;
        foreach (['A', 'B', 'C', 'D'] as $letter) {
            if (isset($item[$letter]) && is_scalar($item[$letter]) && trim((string) $item[$letter]) !== '') {
                $optionFound = true;
                break;
            }
        }

        return $optionFound;
    }

    /**
     * Brute-force search for any valid JSON object in the text.
     * Tries every substring starting with { and ending with a balanced }.
     */
    private function bruteForceFindJson(string $text): ?array
    {
        $len = strlen($text);
        for ($start = 0; $start < $len; $start++) {
            if ($text[$start] !== '{') continue;

            $depth = 0;
            $inString = false;
            $escaped = false;

            for ($end = $start; $end < $len; $end++) {
                $c = $text[$end];
                if ($escaped) { $escaped = false; continue; }
                if ($c === '\\') { $escaped = true; continue; }
                if ($c === '"') { $inString = !$inString; continue; }
                if ($inString) continue;

                if ($c === '{') $depth++;
                if ($c === '}') {
                    $depth--;
                    if ($depth === 0) {
                        $candidate = substr($text, $start, $end - $start + 1);
                        $candidate = preg_replace('/,\s*([}\]])/', '$1', $candidate);
                        $decoded = json_decode($candidate, true);
                        if (is_array($decoded) && !empty($decoded)) {
                            return $decoded;
                        }
                        break;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Extract all top-level JSON objects/arrays from text,
     * properly handling nested braces/brackets and strings.
     */
    private function extractJsonBlocks(string $text): array
    {
        $blocks = [];
        $len = strlen($text);
        $i = 0;

        while ($i < $len) {
            $char = $text[$i];
            if ($char === '{' || $char === '[') {
                $depth = 0;
                $inString = false;
                $escaped = false;
                $openChar = $char;
                $closeChar = $openChar === '{' ? '}' : ']';
                $start = $i;
                $j = $i;

                while ($j < $len) {
                    $c = $text[$j];
                    if ($escaped) {
                        $escaped = false;
                        $j++;
                        continue;
                    }
                    if ($c === '\\') {
                        $escaped = true;
                        $j++;
                        continue;
                    }
                    if ($c === '"') {
                        $inString = !$inString;
                        $j++;
                        continue;
                    }
                    if (!$inString) {
                        if ($c === $openChar) {
                            $depth++;
                        } elseif ($c === $closeChar) {
                            $depth--;
                            if ($depth === 0) {
                                $blocks[] = substr($text, $start, $j - $start + 1);
                                break;
                            }
                        }
                    }
                    $j++;
                }
                $i = $j;
            }
            $i++;
        }

        return $blocks;
    }
}
