<?php

namespace App\Http\Controllers;

use App\Helpers\JsonDb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ExamController extends Controller
{
    public function create()
    {
        return view('teacher.exams.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'duration' => 'required|integer',
            'instructions' => 'nullable|string',
            'questions' => 'required|array',
        ]);

        JsonDb::init();
        $db = JsonDb::get();
        $user = Session::get('user');

        $questions = array_values($request->questions);

        $defaultMarks = $request->defaultMarks ?? 5;
        $exam = [
            'id' => 'exam_' . uniqid(),
            'title' => $request->title,
            'subject' => $request->subject,
            'level' => 'Senior Secondary School',
            'topic' => $request->topic ?? '',
            'subTopic' => $request->subTopic ?? '',
            'duration' => $request->duration,
            'totalMarks' => count($questions) * $defaultMarks,
            'defaultMarks' => $defaultMarks,
            'instructions' => $request->instructions,
            'questions' => $questions,
            'creatorId' => $user['id'],
            'creatorName' => $user['name'],
            'isPublished' => false,
            'createdAt' => now()->toIso8601String(),
        ];
        $db['exams'][] = $exam;
        JsonDb::save($db);
        return redirect()->route('teacher.dashboard')->with('success', 'Exam created successfully!');
    }

    public function publish($examId)
    {
        $user = Session::get('user');
        JsonDb::init();
        $db = JsonDb::get();
        foreach ($db['exams'] as &$exam) {
            if ($exam['id'] === $examId) {
                if (($user['role'] ?? '') !== 'admin' && ($exam['creatorId'] ?? '') !== ($user['id'] ?? '')) {
                    abort(403, 'You can only publish your own exams.');
                }
                $exam['isPublished'] = true;
                break;
            }
        }
        JsonDb::save($db);
        return back()->with('success', 'Exam published!');
    }

    public function destroy($examId)
    {
        $user = Session::get('user');
        JsonDb::init();
        $db = JsonDb::get();
        // Verify ownership
        $found = null;
        foreach ($db['exams'] as $e) { if ($e['id'] === $examId) { $found = $e; break; } }
        if ($found && ($user['role'] ?? '') !== 'admin' && ($found['creatorId'] ?? '') !== ($user['id'] ?? '')) {
            abort(403, 'You can only delete your own exams.');
        }
        $db['exams'] = array_filter($db['exams'], fn($e) => $e['id'] !== $examId);
        $db['results'] = array_filter($db['results'], fn($r) => $r['examId'] !== $examId);
        JsonDb::save($db);
        return back()->with('success', 'Exam deleted!');
    }

    public function apiStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'duration' => 'required|integer',
            'questions' => 'required|array',
        ]);

        JsonDb::init();
        $db = JsonDb::get();
        $user = Session::get('user');

        $defaultMarks = $request->defaultMarks ?? 5;
        $exam = [
            'id' => 'exam_' . uniqid(),
            'title' => $request->title,
            'subject' => $request->subject,
            'level' => $request->level ?? 'Senior Secondary School',
            'topic' => $request->topic ?? '',
            'subTopic' => $request->subTopic ?? '',
            'duration' => $request->duration,
            'totalMarks' => $request->totalMarks ?? count($request->questions) * $defaultMarks,
            'defaultMarks' => $defaultMarks,
            'instructions' => $request->instructions ?? '',
            'questions' => array_values($request->questions),
            'creatorId' => $user['id'] ?? 'unknown',
            'creatorName' => $user['name'] ?? 'Unknown',
            'isPublished' => false,
            'createdAt' => now()->toIso8601String(),
        ];
        $db['exams'][] = $exam;
        JsonDb::save($db);
        return response()->json(['success' => true, 'exam' => $exam]);
    }

    public function apiUpdateSettings(Request $request, $examId)
    {
        $request->validate([
            'duration' => 'required|integer|min:1',
            'defaultMarks' => 'required|integer|min:1|max:100',
            'title' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
        ]);

        $user = Session::get('user');
        if (!$user) return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        JsonDb::init();
        $db = JsonDb::get();
        $updated = false;
        foreach ($db['exams'] as &$exam) {
            if ($exam['id'] === $examId) {
                if (($user['role'] ?? '') !== 'admin' && ($exam['creatorId'] ?? '') !== ($user['id'] ?? '')) {
                    return response()->json(['success' => false, 'error' => 'You can only edit your own exams'], 403);
                }
                $exam['duration'] = (int)$request->duration;
                $exam['defaultMarks'] = (int)$request->defaultMarks;
                if ($request->title) $exam['title'] = $request->title;
                if ($request->has('instructions')) $exam['instructions'] = $request->instructions;
                $updated = true;
                break;
            }
        }
        unset($exam);
        JsonDb::save($db);

        return response()->json(['success' => $updated]);
    }

    public function apiPublish($examId)
    {
        $user = Session::get('user');
        if (!$user) return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        JsonDb::init();
        $db = JsonDb::get();
        foreach ($db['exams'] as &$exam) {
            if ($exam['id'] === $examId) {
                if (($user['role'] ?? '') !== 'admin' && ($exam['creatorId'] ?? '') !== ($user['id'] ?? '')) {
                    return response()->json(['success' => false, 'error' => 'You can only publish your own exams'], 403);
                }
                $exam['isPublished'] = true;
                break;
            }
        }
        JsonDb::save($db);
        return response()->json(['success' => true]);
    }

    public function apiDestroy($examId)
    {
        $user = Session::get('user');
        if (!$user) return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        JsonDb::init();
        $db = JsonDb::get();
        $found = null;
        foreach ($db['exams'] as $e) { if ($e['id'] === $examId) { $found = $e; break; } }
        if ($found && ($user['role'] ?? '') !== 'admin' && ($found['creatorId'] ?? '') !== ($user['id'] ?? '')) {
            return response()->json(['success' => false, 'error' => 'You can only delete your own exams'], 403);
        }
        $db['exams'] = array_values(array_filter($db['exams'], fn($e) => $e['id'] !== $examId));
        $db['results'] = array_values(array_filter($db['results'], fn($r) => $r['examId'] !== $examId));
        JsonDb::save($db);
        return response()->json(['success' => true]);
    }

    public function apiIndex()
    {
        JsonDb::init();
        $db = JsonDb::get();
        $user = Session::get('user');
        $role = $user['role'] ?? '';
        $uid = $user['id'] ?? '';
        $exams = $db['exams'] ?? [];
        if (!$user) {
            // Guest sees only published exams (without correct answers ideally, but at least filtered)
            $filtered = array_filter($exams, fn($e) => !empty($e['isPublished']));
            return response()->json(['exams' => array_values($filtered)]);
        }
        if ($role === 'admin') {
            return response()->json(['exams' => $exams]);
        }
        if ($role === 'teacher') {
            $filtered = array_filter($exams, fn($e) => ($e['creatorId'] ?? '') === $uid);
            return response()->json(['exams' => array_values($filtered)]);
        }
        // student
        $filtered = array_filter($exams, fn($e) => !empty($e['isPublished']));
        return response()->json(['exams' => array_values($filtered)]);
    }

    public function apiShow($examId)
    {
        JsonDb::init();
        $db = JsonDb::get();
        $exam = null;
        foreach ($db['exams'] as $e) {
            if ($e['id'] === $examId) {
                $exam = $e;
                break;
            }
        }
        if (!$exam) return response()->json(null, 404);
        $user = Session::get('user');
        $role = $user['role'] ?? '';
        $uid = $user['id'] ?? '';
        // Private-by-default: unpublished drafts only visible to owner/admin
        if (empty($exam['isPublished'])) {
            if (!$user) return response()->json(['error' => 'Unauthorized'], 401);
            if ($role !== 'admin' && ($exam['creatorId'] ?? '') !== $uid) {
                return response()->json(['error' => 'This exam is private to its owner'], 403);
            }
        }
        return response()->json($exam);
    }

    public function apiSubmitExam(Request $request, $examId)
    {
        JsonDb::init();
        $db = JsonDb::get();
        $exam = null;
        foreach ($db['exams'] as $e) {
            if ($e['id'] === $examId) {
                $exam = $e;
                break;
            }
        }
        if (!$exam) {
            return response()->json(['success' => false, 'error' => 'Exam not found'], 404);
        }

        $answers = $request->input('answers', []);
        $sessionUser = Session::get('user');
        $studentId = $sessionUser['id'] ?? $request->input('studentId', 'usr_guest');
        $studentName = $sessionUser['name'] ?? $request->input('studentName', 'Guest');
        // Prevent IDOR: if session exists, force studentId to session id
        if ($sessionUser && ($request->input('studentId') && $request->input('studentId') !== $sessionUser['id'])) {
            $studentId = $sessionUser['id'];
            $studentName = $sessionUser['name'];
        }
        $timeSpent = $request->input('timeSpent', 0);
        $submissionId = $request->input('submissionId', '');

        // Duplicate submission detection: if a result already exists for this
        // student + exam combination (with same submissionId), reject silently.
        if (!empty($submissionId)) {
            foreach ($db['results'] as $existing) {
                if (
                    ($existing['examId'] ?? '') === $examId
                    && ($existing['studentId'] ?? '') === $studentId
                    && ($existing['submissionId'] ?? '') === $submissionId
                ) {
                    return response()->json(['success' => true, 'result' => $existing, 'duplicate' => true]);
                }
            }
        }

        // Server-side time validation: ensure time spent is within bounds
        $durationSeconds = ($exam['duration'] ?? 30) * 60;
        if ($timeSpent > $durationSeconds + 30) {
            $timeSpent = $durationSeconds;
        }

        $score = 0;
        $correctAnswers = 0;
        $failedQuestions = [];
        $totalMarks = 0;

        $defaultMarks = $exam['defaultMarks'] ?? 5;
        $qTypecast = fn($q) => is_array($q) ? $q + ['marks' => $defaultMarks, 'correctAnswer' => $q['correctAnswer'] ?? $q['answer'] ?? '', 'explanation' => $q['explanation'] ?? ''] : ['marks' => $defaultMarks, 'correctAnswer' => '', 'explanation' => ''];
        foreach ($exam['questions'] as $index => $q) {
            $q = $qTypecast($q);
            $marks = (int)($q['marks'] ?? 5);
            $totalMarks += $marks;
            $selected = is_array($answers[$index] ?? null) ? ($answers[$index]['selectedAnswer'] ?? null) : ($answers[$index] ?? null);
            $correctAnswer = $q['correctAnswer'] ?? $q['answer'] ?? '';
            $isCorrect = $selected !== null && strtoupper((string)$selected) === strtoupper((string)$correctAnswer);
            if ($isCorrect) {
                $score += $marks;
                $correctAnswers++;
            }
            $failedQuestions[] = [
                'question' => $q['question'] ?? '',
                'optionA' => $q['optionA'] ?? $q['options']['A'] ?? '',
                'optionB' => $q['optionB'] ?? $q['options']['B'] ?? '',
                'optionC' => $q['optionC'] ?? $q['options']['C'] ?? '',
                'optionD' => $q['optionD'] ?? $q['options']['D'] ?? '',
                'selectedAnswer' => $selected,
                'correctAnswer' => $correctAnswer,
                'isCorrect' => $isCorrect,
                'marks' => $marks,
                'explanation' => $q['explanation'] ?? "The correct answer is Option {$correctAnswer}.",
                'topic' => $q['topic'] ?? 'General Topic',
            ];
        }

        $percentage = $totalMarks > 0 ? round(($score / $totalMarks) * 100) : 0;

        $result = [
            'id' => 'res_' . uniqid(),
            'examId' => $exam['id'],
            'examTitle' => $exam['title'],
            'subject' => $exam['subject'],
            'studentId' => $studentId,
            'studentName' => $studentName,
            'score' => $score,
            'percentage' => $percentage,
            'totalQuestions' => count($exam['questions']),
            'correctAnswers' => $correctAnswers,
            'failedQuestions' => $failedQuestions,
            'date' => now()->toIso8601String(),
            'timeSpent' => (int)$timeSpent,
            'totalPossibleMarks' => $totalMarks,
            'submissionId' => $submissionId,
        ];

        $db['results'][] = $result;
        JsonDb::save($db);

        Log::info('Exam submitted successfully', [
            'examId' => $examId,
            'studentId' => $studentId,
            'score' => $score,
            'totalMarks' => $totalMarks,
            'timeSpent' => $timeSpent,
            'submissionId' => $submissionId,
        ]);

        return response()->json(['success' => true, 'result' => $result]);
    }

    public function apiAutoSave(Request $request, $examId)
    {
        try {
            $answers = $request->input('answers', []);
            $sessionUser = Session::get('user');
            $studentId = $sessionUser['id'] ?? $request->input('studentId', 'usr_guest');
            if ($sessionUser && $request->input('studentId') && $request->input('studentId') !== $sessionUser['id']) {
                $studentId = $sessionUser['id'];
            }

            JsonDb::init();
            $db = JsonDb::get();
            if (!isset($db['autosaves']) || !is_array($db['autosaves'])) {
                $db['autosaves'] = [];
            }
            $key = 'autosave_' . $examId . '_' . $studentId;
            $db['autosaves'][$key] = [
                'examId' => $examId,
                'studentId' => $studentId,
                'answers' => $answers,
                'updatedAt' => now()->toIso8601String(),
            ];
            JsonDb::save($db);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::warning('Auto-save failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false], 500);
        }
    }

    public function apiLoadAutoSave(Request $request, $examId)
    {
        $sessionUser = Session::get('user');
        $studentId = $sessionUser['id'] ?? $request->input('studentId', 'usr_guest');
        if ($sessionUser && $request->input('studentId') && $request->input('studentId') !== $sessionUser['id'] && ($sessionUser['role'] ?? '') !== 'admin') {
            $studentId = $sessionUser['id'];
        }
        JsonDb::init();
        $db = JsonDb::get();
        $key = 'autosave_' . $examId . '_' . $studentId;
        $autosave = isset($db['autosaves']) ? ($db['autosaves'][$key] ?? null) : null;
        return response()->json(['success' => true, 'autosave' => $autosave]);
    }
}
