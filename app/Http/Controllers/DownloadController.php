<?php

namespace App\Http\Controllers;

use App\Helpers\JsonDb;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;

class DownloadController extends Controller
{
    public function downloadLessonNote($id, $format)
    {
        $user = \Illuminate\Support\Facades\Session::get('user');
        JsonDb::init();
        $db = JsonDb::get();
        $note = null;
        foreach ($db['lessonNotes'] as $n) {
            if ($n['id'] === $id) { $note = $n; break; }
        }
        if (!$note) return abort(404);
        if ($user && ($user['role'] ?? '') !== 'admin' && ($note['teacherId'] ?? '') !== ($user['id'] ?? '')) {
            abort(403, 'This lesson note is private to its owner.');
        }
        if (!$user) abort(401);

        $html = $this->buildNoteHtml($note);
        if ($format === 'pdf') return $this->downloadPdf($html, 'lesson_note_' . $id);
        if ($format === 'docx') return $this->downloadDocx($html, 'lesson_note_' . $id);
        return abort(400);
    }

    public function downloadLessonPlan($id, $format)
    {
        $user = \Illuminate\Support\Facades\Session::get('user');
        JsonDb::init();
        $db = JsonDb::get();
        $plan = null;
        foreach ($db['lessonPlans'] as $p) {
            if ($p['id'] === $id) { $plan = $p; break; }
        }
        if (!$plan) return abort(404);
        if ($user && ($user['role'] ?? '') !== 'admin' && ($plan['teacherId'] ?? '') !== ($user['id'] ?? '')) {
            abort(403, 'This lesson plan is private to its owner.');
        }
        if (!$user) abort(401);

        if ($format === 'pdf') {
            $html = $this->buildPlanHtml($plan);
            return $this->downloadPdf($html, 'lesson_plan_' . $id);
        }
        if ($format === 'docx') {
            return $this->downloadPlanDocx($plan, 'lesson_plan_' . $id);
        }
        return abort(400);
    }

    public function downloadExam($id, $format)
    {
        $user = \Illuminate\Support\Facades\Session::get('user');
        JsonDb::init();
        $db = JsonDb::get();
        $exam = null;
        foreach ($db['exams'] as $e) {
            if ($e['id'] === $id) { $exam = $e; break; }
        }
        if (!$exam) return abort(404);
        // Private-by-default: unpublished exams only owner/admin; published exams require login
        if (empty($exam['isPublished'])) {
            if (!$user) abort(401);
            if (($user['role'] ?? '') !== 'admin' && ($exam['creatorId'] ?? '') !== ($user['id'] ?? '')) {
                abort(403, 'This exam is private to its owner.');
            }
        } else {
            if (!$user) abort(401);
        }

        $html = $this->buildExamHtml($exam);
        if ($format === 'pdf') return $this->downloadPdf($html, 'exam_' . $id);
        if ($format === 'docx') return $this->downloadDocx($html, 'exam_' . $id);
        return abort(400);
    }

    public function downloadGradedScript($examId, $resultId, $format = 'pdf')
    {
        $user = \Illuminate\Support\Facades\Session::get('user');
        if (!$user) abort(401);
        JsonDb::init();
        $db = JsonDb::get();

        $exam = null;
        foreach ($db['exams'] as $e) {
            if ($e['id'] === $examId) { $exam = $e; break; }
        }
        if (!$exam) return abort(404, 'Exam not found');

        $result = null;
        foreach ($db['results'] as $r) {
            if ($r['id'] === $resultId && ($r['examId'] ?? '') === $examId) { $result = $r; break; }
        }
        if (!$result) return abort(404, 'Result not found');
        // Only student owner, exam owner, or admin can download
        $isOwner = ($result['studentId'] ?? '') === ($user['id'] ?? '');
        $isExamOwner = ($exam['creatorId'] ?? '') === ($user['id'] ?? '');
        $isAdmin = ($user['role'] ?? '') === 'admin';
        if (!$isOwner && !$isExamOwner && !$isAdmin) {
            abort(403, 'This result is private to its owner.');
        }

        $html = $this->buildGradedScriptHtml($exam, $result);
        if ($format === 'pdf') {
            return $this->downloadPdf($html, 'graded_script_' . $resultId);
        }
        if ($format === 'docx') {
            return $this->downloadDocx($html, 'graded_script_' . $resultId);
        }
        return abort(400, 'Unsupported format');
    }

    public function emailLessonPlan(Request $request, $id)
    {
        $request->validate(['email' => 'required|email']);
        $user = \Illuminate\Support\Facades\Session::get('user');
        if (!$user) return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        \App\Helpers\JsonDb::init();
        $db = \App\Helpers\JsonDb::get();
        $plan = null;
        foreach ($db['lessonPlans'] ?? [] as $p) { if ($p['id'] === $id) { $plan = $p; break; } }
        if (!$plan) return response()->json(['success' => false, 'error' => 'Lesson plan not found'], 404);
        if (($user['role'] ?? '') !== 'admin' && ($plan['teacherId'] ?? '') !== ($user['id'] ?? '')) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }
        $tmpFile = $this->generatePlanDocxFile($plan);
        $filename = preg_replace('/[^a-z0-9]+/i', '_', $plan['topic'] ?? 'lesson_plan');
        $filename = trim($filename, '_') ?: 'lesson_plan';
        $filename .= '.docx';
        try {
            Mail::send([], [], function ($message) use ($request, $plan, $tmpFile, $filename) {
                $message->to($request->input('email'))
                    ->subject('Lesson Plan: ' . ($plan['topic'] ?? 'Lesson Plan') . ' - Cfschool')
                    ->attach($tmpFile, ['as' => $filename, 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->setBody('Please find the lesson plan attached in DOCX tabular format (not a link).', 'text/plain');
                $fromAddr = config('mail.from.address', 'noreply@cfschool.com');
                $fromName = config('mail.from.name', 'Cfschool');
                if ($fromAddr) $message->from($fromAddr, $fromName);
            });
            @unlink($tmpFile);
            return response()->json(['success' => true, 'message' => 'Lesson plan sent via email as DOCX attachment']);
        } catch (\Exception $e) {
            @unlink($tmpFile);
            return response()->json(['success' => false, 'error' => 'Email failed: ' . $e->getMessage()], 500);
        }
    }

    protected function getStudentClassLevel(\Illuminate\Support\Collection|array $users, string $studentId): string
    {
        foreach ($users as $u) {
            if (($u['id'] ?? '') === $studentId) {
                return $u['classLevel'] ?? $u['class_level'] ?? '';
            }
        }
        return '';
    }

    protected function buildGradedScriptHtml(array $exam, array $result): string
    {
        $db = JsonDb::get();
        $schoolName = $db['schoolConfig']['name'] ?? 'Covenant Foundation School';
        $schoolAddress = $db['schoolConfig']['address'] ?? '';
        $schoolMotto = $db['schoolConfig']['motto'] ?? '';

        $title = $exam['title'] ?? 'Examination';
        $subject = $exam['subject'] ?? '';
        $classLevel = $exam['level'] ?? '';
        $studentName = $result['studentName'] ?? 'Student';
        $studentId = $result['studentId'] ?? '';
        $score = (int)($result['score'] ?? 0);
        $totalPossible = (int)($result['totalPossibleMarks'] ?? 0);
        $percentage = (int)($result['percentage'] ?? 0);
        $correctAnswers = (int)($result['correctAnswers'] ?? 0);
        $totalQuestions = (int)($result['totalQuestions'] ?? 0);
        $timeSpent = (int)($result['timeSpent'] ?? 0);
        $grade = $percentage >= 75 ? 'A' : ($percentage >= 60 ? 'B' : ($percentage >= 50 ? 'C' : ($percentage >= 40 ? 'D' : 'F')));
        $isPassed = $percentage >= 50;
        $allQuestions = $result['failedQuestions'] ?? [];
        $date = $result['date'] ?? now()->toIso8601String();
        $resultId = $result['id'] ?? '';

        // Look up student class level from users array
        $users = $db['users'] ?? [];
        $studentClassLevel = $this->getStudentClassLevel($users, $studentId);
        if (empty($studentClassLevel)) {
            $studentClassLevel = $classLevel;
        }

        $timeStr = $timeSpent < 60 ? $timeSpent . 's' : floor($timeSpent / 60) . 'm ' . ($timeSpent % 60) . 's';

        // Build questions HTML
        $questionsHtml = '';
        foreach ($allQuestions as $idx => $item) {
            $qNum = $idx + 1;
            $selectedAnswer = $item['selectedAnswer'] ?? null;
            $correctAnswer = $item['correctAnswer'] ?? '';
            $isCorrect = $selectedAnswer !== null && strtoupper((string)$selectedAnswer) === strtoupper((string)$correctAnswer);
            $isNotAnswered = $selectedAnswer === null || $selectedAnswer === '';
            $qMarks = (int)($item['marks'] ?? 0);
            $earnedMarks = $isCorrect ? $qMarks : 0;

            $statusBadge = $isCorrect
                ? '<span style="color:#059669;font-weight:800;font-size:10px">✓ Correct</span>'
                : ($isNotAnswered
                    ? '<span style="color:#d97706;font-weight:800;font-size:10px">— Not Answered</span>'
                    : '<span style="color:#dc2626;font-weight:800;font-size:10px">✗ Wrong</span>');

            $optKeys = ['A', 'B', 'C', 'D'];
            $optLabels = [];
            foreach ($optKeys as $k) {
                $v = $item['option' . $k] ?? $item['options'][$k] ?? $item[$k] ?? '';
                $optLabels[] = ['k' => $k, 'v' => $v];
            }

            $optsHtml = '';
            foreach ($optLabels as $opt) {
                $isCorrectOpt = strtoupper($opt['k']) === strtoupper($correctAnswer);
                $isSelectedOpt = strtoupper($opt['k']) === strtoupper((string)$selectedAnswer);
                $bg = '#ffffff';
                $border = '#e2e8f0';
                $marker = '#f1f5f9';
                $mark = '';
                $markColor = 'transparent';
                if ($isCorrectOpt) {
                    $bg = '#f0fdf4';
                    $border = '#86efac';
                    $marker = '#22c55e';
                    $mark = ' ✓';
                    $markColor = '#059669';
                } elseif ($isSelectedOpt && !$isCorrectOpt) {
                    $bg = '#fff1f2';
                    $border = '#fda4af';
                    $marker = '#e11d48';
                    $mark = ' ✗';
                    $markColor = '#dc2626';
                }
                $optsHtml .= '<div style="padding:5px 8px;border:1px solid ' . $border . ';border-radius:4px;background:' . $bg . ';font-size:10px;font-weight:600;color:#334155;display:flex;align-items:center;gap:6px;page-break-inside:avoid;break-inside:avoid">'
                    . '<span style="width:18px;height:18px;border-radius:3px;display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:800;color:#fff;background:' . $marker . ';border:1px solid ' . $border . '">' . $opt['k'] . '</span>'
                    . '<span style="flex:1">' . e($opt['v']) . '</span>'
                    . ($mark ? '<span style="font-size:10px;font-weight:800;color:' . $markColor . '">' . $mark . '</span>' : '')
                    . '</div>';
            }

            $explanation = $item['explanation'] ?? ('The correct answer is Option ' . $correctAnswer . '.');
            $topic = $item['topic'] ?? '';

            $questionsHtml .= '
            <div style="border:1px solid #e2e8f0;border-radius:6px;padding:10px;margin-bottom:10px;page-break-inside:avoid;break-inside:avoid">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                    <div>
                        <span style="font-size:9px;font-weight:800;color:#64748b">Question ' . $qNum . ' of ' . $totalQuestions . '</span>
                        ' . ($topic ? '<span style="font-size:8px;color:#94a3b8;margin-left:6px">(' . e($topic) . ')</span>' : '') . '
                    </div>
                    <div style="display:flex;align-items:center;gap:6px">
                        <span style="font-size:9px;font-weight:700;color:#475569">Marks: ' . $earnedMarks . '/' . $qMarks . '</span>
                        ' . $statusBadge . '
                    </div>
                </div>
                <p style="font-size:11px;font-weight:700;color:#1e293b;margin-bottom:6px;line-height:1.4">' . e($item['question']) . '</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px">' . $optsHtml . '</div>
                <div style="margin-top:6px;display:flex;gap:6px;flex-wrap:wrap">
                    <div style="padding:4px 8px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:4px;font-size:9px;font-weight:700;color:#475569">
                        Selected: <span style="color:' . ($isNotAnswered ? '#d97706' : ($isCorrect ? '#059669' : '#dc2626')) . '">' . ($selectedAnswer ? e($selectedAnswer) : '—') . '</span>
                    </div>
                    <div style="padding:4px 8px;background:#f0fdf4;border:1px solid #86efac;border-radius:4px;font-size:9px;font-weight:700;color:#059669">
                        Correct: <span style="color:#16a34a">' . e($correctAnswer) . '</span>
                    </div>
                </div>
                <div style="margin-top:6px;padding:6px 10px;background:#eef2ff;border:1px solid #e0e7ff;border-radius:4px;font-size:10px;color:#334155;line-height:1.3"><strong style="color:#4338ca">Explanation:</strong> ' . e($explanation) . '</div>
            </div>';
        }

        $dateFormatted = date('F j, Y', strtotime($date));
        $dateTimeFormatted = date('F j, Y \a\t g:i A', strtotime($date));
        $gradeColor = $grade === 'A' ? '#059669' : ($grade === 'B' ? '#2563eb' : ($grade === 'C' ? '#d97706' : ($grade === 'D' ? '#ea580c' : '#dc2626')));

        $body = '
        <div style="max-width:190mm;margin:0 auto;padding:5mm 0">
            <!-- School Header -->
            <div style="text-align:center;border-bottom:3px double #1e293b;padding-bottom:6px;margin-bottom:14px">
                <h1 style="font-size:20px;font-weight:900;color:#0f172a;margin:0;text-transform:uppercase;letter-spacing:1.5px">' . e($schoolName) . '</h1>
                ' . ($schoolMotto ? '<p style="font-size:8px;color:#94a3b8;font-style:italic;margin:2px 0 0 0;letter-spacing:0.5px">"' . e($schoolMotto) . '"</p>' : '') . '
                <p style="font-size:8px;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:2px;margin:2px 0 0 0">Official Assessment Center &bull; Computer Based Testing Division</p>
                ' . ($schoolAddress ? '<p style="font-size:7px;color:#94a3b8;margin:2px 0 0 0">' . e($schoolAddress) . '</p>' : '') . '
            </div>

            <!-- Student Info Row -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:6px;padding:10px 14px">
                <div>
                    <p style="font-size:7px;font-weight:700;text-transform:uppercase;color:#94a3b8;margin:0 0 2px 0;letter-spacing:0.5px">Student Name</p>
                    <p style="font-size:12px;font-weight:800;color:#0f172a;margin:0">' . e($studentName) . '</p>
                </div>
                <div style="text-align:right">
                    <p style="font-size:7px;font-weight:700;text-transform:uppercase;color:#94a3b8;margin:0 0 2px 0;letter-spacing:0.5px">Student ID</p>
                    <p style="font-size:12px;font-weight:800;color:#0f172a;margin:0;font-family:monospace">' . e($studentId) . '</p>
                </div>
                <div>
                    <p style="font-size:7px;font-weight:700;text-transform:uppercase;color:#94a3b8;margin:0 0 2px 0;letter-spacing:0.5px">Class / Level</p>
                    <p style="font-size:12px;font-weight:700;color:#0f172a;margin:0">' . e($studentClassLevel ?: 'N/A') . '</p>
                </div>
                <div style="text-align:right">
                    <p style="font-size:7px;font-weight:700;text-transform:uppercase;color:#94a3b8;margin:0 0 2px 0;letter-spacing:0.5px">Date Taken</p>
                    <p style="font-size:12px;font-weight:700;color:#0f172a;margin:0">' . $dateTimeFormatted . '</p>
                </div>
            </div>

            <!-- Exam Info -->
            <div style="margin-bottom:12px;padding:0 2px">
                <h2 style="font-size:14px;font-weight:900;color:#0f172a;margin:0 0 4px 0">' . e($title) . '</h2>
                <div style="display:flex;flex-wrap:wrap;gap:12px;font-size:9px;color:#64748b;font-weight:600">
                    <span>Subject: <strong style="color:#334155">' . e($subject) . '</strong></span>
                    <span>Time Used: <strong style="color:#334155">' . $timeStr . '</strong></span>
                    <span>Questions: <strong style="color:#334155">' . $totalQuestions . '</strong></span>
                    <span>Total Marks: <strong style="color:#334155">' . $totalPossible . '</strong></span>
                </div>
            </div>

            <!-- Score Card -->
            <div style="background:' . ($isPassed ? '#059669' : '#dc2626') . ';color:#fff;border-radius:8px;padding:12px 16px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center">
                <div>
                    <p style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:1px;opacity:0.9;margin:0 0 3px 0">Final Score</p>
                    <p style="font-size:24px;font-weight:900;margin:0">' . $score . ' / ' . $totalPossible . '</p>
                </div>
                <div style="text-align:right">
                    <p style="font-size:28px;font-weight:900;margin:0">' . $percentage . '%</p>
                    <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;opacity:0.9;margin:2px 0 0 0">' . ($isPassed ? 'Passed' : 'Failed') . '</p>
                </div>
            </div>

            <!-- Stats Row -->
            <div style="display:grid;grid-template-columns:repeat(5, 1fr);gap:6px;margin-bottom:14px">
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:6px 8px;text-align:center">
                    <p style="font-size:7px;font-weight:700;text-transform:uppercase;color:#94a3b8;margin:0 0 3px 0;letter-spacing:0.3px">Score</p>
                    <p style="font-size:13px;font-weight:900;color:#0f172a;margin:0">' . $score . '/' . $totalPossible . '</p>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:6px 8px;text-align:center">
                    <p style="font-size:7px;font-weight:700;text-transform:uppercase;color:#94a3b8;margin:0 0 3px 0;letter-spacing:0.3px">Correct</p>
                    <p style="font-size:13px;font-weight:900;color:#0f172a;margin:0">' . $correctAnswers . '/' . $totalQuestions . '</p>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:6px 8px;text-align:center">
                    <p style="font-size:7px;font-weight:700;text-transform:uppercase;color:#94a3b8;margin:0 0 3px 0;letter-spacing:0.3px">Percentage</p>
                    <p style="font-size:13px;font-weight:900;color:' . ($isPassed ? '#059669' : '#dc2626') . ';margin:0">' . $percentage . '%</p>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:6px 8px;text-align:center">
                    <p style="font-size:7px;font-weight:700;text-transform:uppercase;color:#94a3b8;margin:0 0 3px 0;letter-spacing:0.3px">Grade</p>
                    <p style="font-size:15px;font-weight:900;color:' . $gradeColor . ';margin:0">' . $grade . '</p>
                </div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:6px 8px;text-align:center">
                    <p style="font-size:7px;font-weight:700;text-transform:uppercase;color:#94a3b8;margin:0 0 3px 0;letter-spacing:0.3px">Time</p>
                    <p style="font-size:13px;font-weight:900;color:#0f172a;margin:0">' . $timeStr . '</p>
                </div>
            </div>

            <!-- Questions -->
            <h3 style="font-size:12px;font-weight:900;color:#0f172a;text-transform:uppercase;border-bottom:2.5px solid #1e293b;padding-bottom:5px;margin:0 0 12px 0;letter-spacing:0.5px">Question-by-Question Breakdown</h3>
            ' . ($questionsHtml ?: '<p style="text-align:center;padding:20px;color:#94a3b8;font-size:11px">No question data available.</p>') . '

            <!-- Legend -->
            <div style="display:flex;gap:14px;margin-top:6px;padding:8px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;font-size:8px;color:#64748b;flex-wrap:wrap">
                <span><span style="display:inline-block;width:11px;height:11px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:3px;vertical-align:middle;margin-right:4px"></span> Correct answer</span>
                <span><span style="display:inline-block;width:11px;height:11px;background:#fff1f2;border:1.5px solid #fda4af;border-radius:3px;vertical-align:middle;margin-right:4px"></span> Selected wrong answer</span>
                <span><span style="display:inline-block;width:11px;height:11px;background:#ffffff;border:1.5px solid #e2e8f0;border-radius:3px;vertical-align:middle;margin-right:4px"></span> Not selected</span>
            </div>

            <!-- Certificate Section (if passed) -->
            ' . ($isPassed ? '
            <div style="page-break-before:always;break-before:page;margin-top:20px">
                <div style="border:4px double #d97706;padding:24px 20px;text-align:center;background:linear-gradient(135deg, #fffbeb 0%, #ffffff 100%);border-radius:10px;position:relative">
                    <div style="position:absolute;top:10px;left:10px;width:28px;height:28px;border-top:3px solid #d97706;border-left:3px solid #d97706;border-radius:5px 0 0 0"></div>
                    <div style="position:absolute;top:10px;right:10px;width:28px;height:28px;border-top:3px solid #d97706;border-right:3px solid #d97706;border-radius:0 5px 0 0"></div>
                    <div style="position:absolute;bottom:10px;left:10px;width:28px;height:28px;border-bottom:3px solid #d97706;border-left:3px solid #d97706;border-radius:0 0 0 5px"></div>
                    <div style="position:absolute;bottom:10px;right:10px;width:28px;height:28px;border-bottom:3px solid #d97706;border-right:3px solid #d97706;border-radius:0 0 5px 0"></div>
                    <h2 style="font-size:20px;font-weight:900;color:#92400e;text-transform:uppercase;letter-spacing:2px;margin:0 0 6px 0;font-family:serif">Certificate of Excellence</h2>
                    <div style="width:70px;height:2.5px;background:#d97706;margin:8px auto;border-radius:2px"></div>
                    <p style="font-size:11px;color:#64748b;font-style:italic;margin:6px 0">Presented to</p>
                    <h3 style="font-size:22px;font-weight:900;color:#0f172a;margin:6px 0;text-transform:uppercase;text-decoration:underline;text-underline-offset:8px;font-family:serif">' . e($studentName) . '</h3>
                    <p style="font-size:11px;color:#475569;max-width:420px;margin:8px auto;line-height:1.6">For completing the computer-based evaluation test in <strong>' . e($title) . ' (' . e($subject) . ')</strong> with a final score of <strong>' . $score . '/' . $totalPossible . ' (' . $percentage . '%)</strong> achieving a grade of <strong>' . $grade . '</strong>.</p>
                    <p style="font-size:40px;font-weight:900;color:#d97706;margin:10px 0">' . $percentage . '%</p>
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:9px;color:#94a3b8;margin-top:24px;padding-top:12px;border-top:1px dashed #d97706">
                        <div style="text-align:left">Principal Assessor:<br><strong style="color:#1e293b;font-size:11px">Nwaigbo Augustine</strong></div>
                        <div style="text-align:right">Verification Code:<br><strong style="color:#1e293b;font-family:monospace;font-size:10px">' . e($resultId) . '</strong></div>
                    </div>
                </div>
            </div>
            ' : '') . '

            <!-- Footer -->
            <div style="margin-top:18px;padding-top:10px;border-top:1.5px solid #e2e8f0;font-size:7.5px;color:#94a3b8;text-align:center;line-height:1.6">
                Generated on ' . date('F j, Y \a\t g:i A') . ' &bull; ' . e($schoolName) . ' &bull; Official Document<br>
                This is a computer-generated document and is valid without a physical signature.
            </div>
        </div>';

        return $this->wrapHtml($body);
    }

    protected function buildNoteHtml(array $note): string
    {
        $topic = $note['topic'] ?? 'Lesson Note';
        $subject = $note['subject'] ?? '';
        $class = $note['class'] ?? '';
        $term = $note['term'] ?? '';
        $week = $note['week'] ?? '';
        $content = $note['content'] ?? '';
        $detailedNote = $note['detailedNote'] ?? '';
        $evaluation = $note['evaluationQuestions'] ?? [];
        $assignment = $note['assignment'] ?? '';
        $definitions = $note['definitions'] ?? [];
        $keyPoints = $note['keyPoints'] ?? [];
        $sections = $note['sections'] ?? [];

        $evalHtml = '';
        foreach ($evaluation as $eq) {
            $evalHtml .= '<li>' . $eq . '</li>';
        }

        $definitionsHtml = '';
        if (!empty($definitions)) {
            $definitionsHtml .= '<h3>Definitions of Key Terms</h3><table class="definitions" style="width:100%;border-collapse:collapse;margin-bottom:10px">';
            foreach ($definitions as $def) {
                $definitionsHtml .= '<tr style="border-bottom:1px solid #ddd"><td style="padding:6px 8px;font-weight:700;width:30%">' . e($def['term'] ?? '') . '</td><td style="padding:6px 8px">' . e($def['definition'] ?? '') . '</td></tr>';
            }
            $definitionsHtml .= '</table>';
        }

        $sectionsHtml = '';
        foreach ($sections as $sec) {
            if (!empty($sec['heading']) && !empty($sec['content'])) {
                $sectionsHtml .= '<h3>' . e($sec['heading']) . '</h3><div>' . $sec['content'] . '</div>';
            }
        }

        $keyPointsHtml = '';
        if (!empty($keyPoints)) {
            $keyPointsHtml .= '<h3>Key Points to Remember</h3><ul style="color:#047857">';
            foreach ($keyPoints as $kp) {
                $keyPointsHtml .= '<li>' . e($kp) . '</li>';
            }
            $keyPointsHtml .= '</ul>';
        }

        return $this->wrapHtml('
            <div class="header">
                <h1>' . $topic . '</h1>
                <p class="meta">' . $subject . ' | ' . $class . ' | ' . $term . ' | Week ' . $week . '</p>
            </div>
            <div class="content">
                ' . $content . '
                ' . $definitionsHtml . '
                ' . $sectionsHtml . '
                ' . ($evalHtml ? '<h3>Evaluation Questions</h3><ol>' . $evalHtml . '</ol>' : '') . '
                ' . ($assignment ? '<h3>Assignment</h3><p>' . nl2br(e($assignment)) . '</p>' : '') . '
                ' . $keyPointsHtml . '
                ' . ($detailedNote ? '<h3>Detailed Note</h3><div class="detailed-note">' . nl2br(e($detailedNote)) . '</div>' : '') . '
            </div>
        ');
    }

    protected function truncate(string $text, int $limit = 200): string
    {
        // For lower-class visual topics, preserve image HTML — don't truncate image tags
        if (str_contains($text, '<img') || str_contains($text, '[IMAGE:')) {
            // Return as-is (up to larger limit) to keep image visible in DOCX
            // Strip only non-image tags for length check but keep image
            $cleanForLength = strip_tags(preg_replace('/<img[^>]*>/i', '', $text));
            if (mb_strlen($cleanForLength) <= $limit + 500) return strip_tags($text, '<img><div><p><br>');
        }
        $clean = strip_tags($text);
        if (mb_strlen($clean) <= $limit) return $clean;
        $trimmed = mb_substr($clean, 0, $limit);
        $lastSpace = mb_strrpos($trimmed, ' ');
        return mb_substr($trimmed, 0, $lastSpace ?: $limit) . '...';
    }

    protected function generatePlanDocxFile(array $plan): string
    {
        $topic = $plan['topic'] ?? 'Lesson Plan';
        $subject = $plan['subject'] ?? '';
        $class = $plan['class'] ?? '';
        $term = $plan['term'] ?? '';
        $week = $plan['week'] ?? '';
        $schoolName = $plan['schoolName'] ?? '';
        $teacherName = $plan['teacherName'] ?? '';
        $duration = $plan['duration'] ?? '40 Minutes';
        $ageRange = $plan['ageRange'] ?? '';
        $date = $plan['date'] ?? now()->format('l, F j, Y');

        $objectives = $plan['behaviouralObjectives'] ?? [];
        $materials = $plan['instructionalMaterials'] ?? [];
        $previousKnowledge = $plan['previousKnowledge'] ?? '';
        $steps = $plan['lessonSteps'] ?? [];
        $evaluation = $plan['evaluation'] ?? '';
        $assignment = $plan['assignment'] ?? '';
        $summary = $plan['summary'] ?? '';
        $conclusion = $plan['conclusion'] ?? '';

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(7.5);

        $section = $phpWord->addSection([
            'pageSizeW' => 11906,
            'pageSizeH' => 16838,
            'marginLeft' => 567,
            'marginRight' => 567,
            'marginTop' => 340,
            'marginBottom' => 340,
        ]);

        $pStyle = ['spaceBefore' => 0, 'spaceAfter' => 0, 'lineHeight' => 1.05];
        $pStyleSmall = ['spaceBefore' => 0, 'spaceAfter' => 0, 'lineHeight' => 1.0];

        $border = ['borderSize' => 6, 'borderColor' => '000000'];
        $cm = 20;

        $table = $section->addTable(array_merge($border, [
            'cellMargin' => $cm,
            'cellMarginTop' => $cm,
            'cellMarginLeft' => $cm,
            'cellMarginRight' => $cm,
            'cellMarginBottom' => $cm,
        ]));

        $titleCell = $table->addRow()->addCell(9000, array_merge($border, ['gridSpan' => 4, 'valign' => 'center']));
        $titleCell->addText('LESSON PLAN', ['bold' => true, 'size' => 9], $pStyle);

        $labels = ['bold' => true, 'size' => 7];
        $vals = ['size' => 7];

        // School / Teacher
        $table->addRow();
        $table->addCell(1080, array_merge($border, $labels))->addText('School:', $labels, $pStyle);
        $table->addCell(3420, $border)->addText($schoolName, $vals, $pStyle);
        $table->addCell(1080, array_merge($border, $labels))->addText('Teacher:', $labels, $pStyle);
        $table->addCell(3420, $border)->addText($teacherName, $vals, $pStyle);

        // Subject / Class
        $table->addRow();
        $table->addCell(1080, array_merge($border, $labels))->addText('Subject:', $labels, $pStyle);
        $table->addCell(3420, $border)->addText($subject, $vals, $pStyle);
        $table->addCell(1080, array_merge($border, $labels))->addText('Class:', $labels, $pStyle);
        $table->addCell(3420, $border)->addText($class . ($ageRange ? ' (' . $ageRange . ')' : ''), $vals, $pStyle);

        // Term / Week
        $table->addRow();
        $table->addCell(1080, array_merge($border, $labels))->addText('Term:', $labels, $pStyle);
        $table->addCell(3420, $border)->addText($term, $vals, $pStyle);
        $table->addCell(1080, array_merge($border, $labels))->addText('Week:', $labels, $pStyle);
        $table->addCell(3420, $border)->addText((string)$week, $vals, $pStyle);

        // Date / Duration
        $table->addRow();
        $table->addCell(1080, array_merge($border, $labels))->addText('Date:', $labels, $pStyle);
        $table->addCell(3420, $border)->addText($date, $vals, $pStyle);
        $table->addCell(1080, array_merge($border, $labels))->addText('Duration:', $labels, $pStyle);
        $table->addCell(3420, $border)->addText($duration, $vals, $pStyle);

        // Topic
        $table->addRow();
        $table->addCell(1080, array_merge($border, $labels))->addText('Topic:', $labels, $pStyle);
        $table->addCell(7920, array_merge($border, ['gridSpan' => 3]))->addText($topic, $vals, $pStyle);

        // Behavioural Objectives header
        $table->addRow();
        $table->addCell(9000, array_merge($border, ['gridSpan' => 4, 'bold' => true]))->addText('Behavioural Objectives', $labels, $pStyle);
        foreach ($objectives as $obj) {
            $table->addRow();
            $table->addCell(9000, array_merge($border, ['gridSpan' => 4]))->addText('• ' . $this->truncate($obj, 150), $vals, $pStyleSmall);
        }

        // Instructional Materials
        if (!empty($materials)) {
            $table->addRow();
            $table->addCell(9000, array_merge($border, ['gridSpan' => 4, 'bold' => true]))->addText('Instructional Materials', $labels, $pStyle);
            $table->addRow();
            $table->addCell(9000, array_merge($border, ['gridSpan' => 4]))->addText($this->truncate(implode('; ', $materials), 200), $vals, $pStyleSmall);
        }

        // Previous Knowledge
        if ($previousKnowledge) {
            $table->addRow();
            $table->addCell(9000, array_merge($border, ['gridSpan' => 4, 'bold' => true]))->addText('Previous Knowledge', $labels, $pStyle);
            $table->addRow();
            $table->addCell(9000, array_merge($border, ['gridSpan' => 4]))->addText($this->truncate($previousKnowledge, 200), $vals, $pStyleSmall);
        }

        // Steps header
        $table->addRow();
        $table->addCell(900, array_merge($border, $labels))->addText('Step', $labels, $pStyle);
        $table->addCell(2700, array_merge($border, $labels))->addText("Teacher's Activities", $labels, $pStyle);
        $table->addCell(2700, array_merge($border, $labels))->addText("Learners' Activities", $labels, $pStyle);
        $table->addCell(2700, array_merge($border, $labels))->addText('Learning Points', $labels, $pStyle);

        foreach ($steps as $s) {
            $table->addRow();
            $sn = $table->addCell(900, array_merge($border, ['valign' => 'top']));
            $sn->addText($s['step'] ?? '', $vals, ['align' => 'center', 'spaceBefore' => 0, 'spaceAfter' => 0]);
            foreach (['teacherActivities' => 200, 'learnerActivities' => 200, 'learningPoints' => 150] as $field => $limit) {
                $cell = $table->addCell(2700, array_merge($border, ['valign' => 'top']));
                $content = $s[$field] ?? '';
                if (str_contains($content, '<img') || str_contains($content, '[IMAGE:')) {
                    // Render HTML with images for lower-class visual topics
                    Html::addHtml($cell, $content, false, false);
                } else {
                    $cell->addText($this->truncate($content, $limit), $vals, $pStyleSmall);
                }
            }
        }

        // Evaluation
        if ($evaluation) {
            $table->addRow();
            $table->addCell(9000, array_merge($border, ['gridSpan' => 4, 'bold' => true]))->addText('Evaluation / Assessment', $labels, $pStyle);
            $table->addRow();
            $table->addCell(9000, array_merge($border, ['gridSpan' => 4]))->addText($this->truncate(strip_tags($evaluation), 300), $vals, $pStyleSmall);
        }

        // Assignment
        if ($assignment) {
            $table->addRow();
            $table->addCell(9000, array_merge($border, ['gridSpan' => 4, 'bold' => true]))->addText('Assignment / Homework', $labels, $pStyle);
            $table->addRow();
            $table->addCell(9000, array_merge($border, ['gridSpan' => 4]))->addText($this->truncate(strip_tags($assignment), 200), $vals, $pStyleSmall);
        }

        // Summary
        if ($summary) {
            $table->addRow();
            $table->addCell(9000, array_merge($border, ['gridSpan' => 4, 'bold' => true]))->addText('Summary', $labels, $pStyle);
            $table->addRow();
            $table->addCell(9000, array_merge($border, ['gridSpan' => 4]))->addText($this->truncate($summary, 200), $vals, $pStyleSmall);
        }

        // Conclusion
        if ($conclusion) {
            $table->addRow();
            $table->addCell(9000, array_merge($border, ['gridSpan' => 4, 'bold' => true]))->addText('Conclusion', $labels, $pStyle);
            $table->addRow();
            $table->addCell(9000, array_merge($border, ['gridSpan' => 4]))->addText($this->truncate($conclusion, 200), $vals, $pStyleSmall);
        }

        // Remarks + Signature (combined in one row to save space)
        $table->addRow(200);
        $sigCell = $table->addCell(9000, array_merge($border, ['gridSpan' => 4]));
        $sigCell->addText("Remarks: ____________________________________________________________________", $vals, $pStyle);
        $sigCell->addText("Teacher's Signature: _______________   Date: _______________   Head Teacher's Signature: _______________   Date: _______________", $vals, $pStyle);

        $tempFile = tempnam(sys_get_temp_dir(), 'docx');
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);
        return $tempFile;
    }

    protected function downloadPlanDocx(array $plan, string $filename)
    {
        $tempFile = $this->generatePlanDocxFile($plan);
        return response()->download($tempFile, $filename . '.docx')->deleteFileAfterSend(true);
    }

    protected function buildPlanHtml(array $plan): string
    {
        $topic = $plan['topic'] ?? 'Lesson Plan';
        $subject = $plan['subject'] ?? '';
        $class = $plan['class'] ?? '';
        $term = $plan['term'] ?? '';
        $week = $plan['week'] ?? '';
        $schoolName = $plan['schoolName'] ?? '';
        $teacherName = $plan['teacherName'] ?? '';
        $duration = $plan['duration'] ?? '40 Minutes';
        $ageRange = $plan['ageRange'] ?? '';
        $date = $plan['date'] ?? now()->format('l, F j, Y');

        $objectives = $plan['behaviouralObjectives'] ?? [];
        $materials = $plan['instructionalMaterials'] ?? [];
        $previousKnowledge = $plan['previousKnowledge'] ?? '';
        $steps = $plan['lessonSteps'] ?? [];
        $evaluation = $plan['evaluation'] ?? '';
        $assignment = $plan['assignment'] ?? '';
        $summary = $plan['summary'] ?? '';
        $conclusion = $plan['conclusion'] ?? '';

        $objRows = '';
        foreach ($objectives as $obj) {
            $objRows .= '<tr><td style="padding:0 1px;font-size:6.5pt;border:1px solid #000" colspan="4">' . $obj . '</td></tr>';
        }

        $matRow = '';
        if (!empty($materials)) {
            $matRow = '<tr><td style="padding:1px 2px;font-size:6.5pt;font-weight:700;border:1px solid #000" colspan="4">Instructional Materials</td></tr>'
                . '<tr><td style="padding:1px 2px;font-size:6.5pt;border:1px solid #000" colspan="4">' . implode('; ', $materials) . '</td></tr>';
        }

        $prevRow = '';
        if ($previousKnowledge) {
            $prevRow = '<tr><td style="padding:1px 2px;font-size:6.5pt;font-weight:700;border:1px solid #000" colspan="4">Previous Knowledge</td></tr>'
                . '<tr><td style="padding:1px 2px;font-size:6.5pt;border:1px solid #000" colspan="4">' . $previousKnowledge . '</td></tr>';
        }

        $stepsHtml = '';
        foreach ($steps as $s) {
            $stepsHtml .= '<tr>
                <td style="padding:1px 1px;border:1px solid #000;font-size:6.5pt;font-weight:700;vertical-align:top;text-align:center">' . ($s['step'] ?? '') . '</td>
                <td style="padding:1px 1px;border:1px solid #000;font-size:6.5pt;vertical-align:top">' . ($s['teacherActivities'] ?? '') . '</td>
                <td style="padding:1px 1px;border:1px solid #000;font-size:6.5pt;vertical-align:top">' . ($s['learnerActivities'] ?? '') . '</td>
                <td style="padding:1px 1px;border:1px solid #000;font-size:6.5pt;vertical-align:top">' . ($s['learningPoints'] ?? '') . '</td>
            </tr>';
        }

        $evalRow = '';
        if ($evaluation) {
            $evalRow = '<tr><td style="padding:1px 2px;font-size:6.5pt;font-weight:700;border:1px solid #000" colspan="4">Evaluation / Assessment</td></tr>'
                . '<tr><td style="padding:1px 2px;font-size:6.5pt;border:1px solid #000" colspan="4">' . nl2br(e($evaluation)) . '</td></tr>';
        }

        $assignRow = '';
        if ($assignment) {
            $assignRow = '<tr><td style="padding:1px 2px;font-size:6.5pt;font-weight:700;border:1px solid #000" colspan="4">Assignment / Homework</td></tr>'
                . '<tr><td style="padding:1px 2px;font-size:6.5pt;border:1px solid #000" colspan="4">' . nl2br(e($assignment)) . '</td></tr>';
        }

        $summaryRow = '';
        if ($summary) {
            $summaryRow = '<tr><td style="padding:1px 2px;font-size:6.5pt;font-weight:700;border:1px solid #000" colspan="4">Summary</td></tr>'
                . '<tr><td style="padding:1px 2px;font-size:6.5pt;border:1px solid #000" colspan="4">' . $summary . '</td></tr>';
        }

        $conclusionRow = '';
        if ($conclusion) {
            $conclusionRow = '<tr><td style="padding:1px 2px;font-size:6.5pt;font-weight:700;border:1px solid #000" colspan="4">Conclusion</td></tr>'
                . '<tr><td style="padding:1px 2px;font-size:6.5pt;border:1px solid #000" colspan="4">' . $conclusion . '</td></tr>';
        }

        return $this->wrapHtml('
        <div style="max-width:210mm;margin:0 auto;page-break-inside:avoid;break-inside:avoid">
        <table style="width:100%;border-collapse:collapse;font-family:Arial,sans-serif;font-size:7pt">
            <tr>
                <th colspan="4" style="padding:2px 3px;font-size:8pt;font-weight:700;text-align:center;background:#1a56db;color:#fff;border:1px solid #000">LESSON PLAN</th>
            </tr>
            <tr>
                <td style="padding:1px 2px;font-size:7pt;font-weight:700;border:1px solid #000;width:12%">School:</td>
                <td style="padding:1px 2px;font-size:7pt;border:1px solid #000;width:38%">' . $schoolName . '</td>
                <td style="padding:1px 2px;font-size:7pt;font-weight:700;border:1px solid #000;width:12%">Teacher:</td>
                <td style="padding:1px 2px;font-size:7pt;border:1px solid #000;width:38%">' . $teacherName . '</td>
            </tr>
            <tr>
                <td style="padding:1px 2px;font-size:7pt;font-weight:700;border:1px solid #000">Subject:</td>
                <td style="padding:1px 2px;font-size:7pt;border:1px solid #000">' . $subject . '</td>
                <td style="padding:1px 2px;font-size:7pt;font-weight:700;border:1px solid #000">Class:</td>
                <td style="padding:1px 2px;font-size:7pt;border:1px solid #000">' . $class . ($ageRange ? ' (' . $ageRange . ')' : '') . '</td>
            </tr>
            <tr>
                <td style="padding:1px 2px;font-size:7pt;font-weight:700;border:1px solid #000">Term:</td>
                <td style="padding:1px 2px;font-size:7pt;border:1px solid #000">' . $term . '</td>
                <td style="padding:1px 2px;font-size:7pt;font-weight:700;border:1px solid #000">Week:</td>
                <td style="padding:1px 2px;font-size:7pt;border:1px solid #000">' . $week . '</td>
            </tr>
            <tr>
                <td style="padding:1px 2px;font-size:7pt;font-weight:700;border:1px solid #000">Date:</td>
                <td style="padding:1px 2px;font-size:7pt;border:1px solid #000">' . $date . '</td>
                <td style="padding:1px 2px;font-size:7pt;font-weight:700;border:1px solid #000">Duration:</td>
                <td style="padding:1px 2px;font-size:7pt;border:1px solid #000">' . $duration . '</td>
            </tr>
            <tr>
                <td style="padding:1px 2px;font-size:7pt;font-weight:700;border:1px solid #000" colspan="2">Topic:</td>
                <td style="padding:1px 2px;font-size:7pt;border:1px solid #000" colspan="2">' . $topic . '</td>
            </tr>
            <tr>
                <td style="padding:1px 2px;font-size:7pt;font-weight:700;border:1px solid #000" colspan="4">Behavioural Objectives</td>
            </tr>
            ' . $objRows . '
            ' . $matRow . '
            ' . $prevRow . '
            <tr>
                <td style="padding:1px 2px;font-size:6.5pt;font-weight:700;text-align:center;background:#1a56db;color:#fff;border:1px solid #1a56db">Step</td>
                <td style="padding:1px 2px;font-size:6.5pt;font-weight:700;text-align:left;background:#1a56db;color:#fff;border:1px solid #1a56db">Teacher\'s Activities</td>
                <td style="padding:1px 2px;font-size:6.5pt;font-weight:700;text-align:left;background:#1a56db;color:#fff;border:1px solid #1a56db">Learners\' Activities</td>
                <td style="padding:1px 2px;font-size:6.5pt;font-weight:700;text-align:left;background:#1a56db;color:#fff;border:1px solid #1a56db">Learning Points</td>
            </tr>
            ' . ($stepsHtml ?: '<tr><td style="padding:1px;border:1px solid #000;font-size:6.5pt" colspan="4">No steps available</td></tr>') . '
            ' . $evalRow . '
            ' . $assignRow . '
            ' . $summaryRow . '
            ' . $conclusionRow . '
            <tr>
                <td style="padding:1px 2px;border:1px solid #000;font-size:6.5pt;font-weight:700" colspan="4">Remarks</td>
            </tr>
            <tr>
                <td colspan="4" style="padding:1px 2px;border:1px solid #000;font-size:6.5pt;height:8px"></td>
            </tr>
            <tr>
                <td style="padding:1px 2px;border:1px solid #000;font-size:6.5pt;width:25%"><b>Teacher\'s Signature:</b> _______________</td>
                <td style="padding:1px 2px;border:1px solid #000;font-size:6.5pt;width:25%"><b>Date:</b> _______________</td>
                <td style="padding:1px 2px;border:1px solid #000;font-size:6.5pt;width:25%"><b>Head Teacher\'s Signature:</b> _______________</td>
                <td style="padding:1px 2px;border:1px solid #000;font-size:6.5pt;width:25%"><b>Date:</b> _______________</td>
            </tr>
        </table>
        </div>
        ');
    }

    protected function buildExamHtml(array $exam): string
    {
        $title = $exam['title'] ?? 'Examination';
        $subject = $exam['subject'] ?? '';
        $duration = $exam['duration'] ?? 0;
        $totalMarks = $exam['totalMarks'] ?? 0;
        $instructions = $exam['instructions'] ?? '';
        $questions = $exam['questions'] ?? [];

        $qHtml = '';
        foreach ($questions as $i => $q) {
            $num = $i + 1;
            $options = $q['options'] ?? $q;
            $optA = $options['A'] ?? $q['optionA'] ?? '';
            $optB = $options['B'] ?? $q['optionB'] ?? '';
            $optC = $options['C'] ?? $q['optionC'] ?? '';
            $optD = $options['D'] ?? $q['optionD'] ?? '';
            $qHtml .= "<div class='question'>
                <p><strong>{$num}.</strong> {$q['question']}</p>
                <ul class='options'>
                    <li>A. {$optA}</li>
                    <li>B. {$optB}</li>
                    <li>C. {$optC}</li>
                    <li>D. {$optD}</li>
                </ul>
            </div>";
        }

        $answerHtml = '';
        foreach ($questions as $i => $q) {
            $num = $i + 1;
            $ans = $q['answer'] ?? $q['correctAnswer'] ?? '';
            $answerHtml .= "<tr><td>{$num}</td><td>{$ans}</td></tr>";
        }

        return $this->wrapHtml('
            <div class="header">
                <h1>' . $title . '</h1>
                <p class="meta">' . $subject . ' | Duration: ' . $duration . ' min | Total Marks: ' . $totalMarks . '</p>
            </div>
            ' . ($instructions ? '<h3>Instructions</h3><p>' . $instructions . '</p>' : '') . '
            <h3>Questions</h3>
            ' . $qHtml . '
            <div style="page-break-before: always;">
                <h3>Answer Key</h3>
                <table class="procedure-table">
                    <thead><tr><th>Question</th><th>Answer</th></tr></thead>
                    <tbody>' . $answerHtml . '</tbody>
                </table>
            </div>
        ');
    }

    protected function wrapHtml(string $body): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                @page { size: A4; margin: 6mm 8mm; }
                body { font-family: Arial, sans-serif; font-size: 7pt; color: #000; margin: 0; padding: 0; line-height: 1.15; }
                table { page-break-inside: avoid; break-inside: avoid; width: 100%; border-collapse: collapse; }
                tr { page-break-inside: avoid; break-inside: avoid; }
                td, th { page-break-inside: avoid; break-inside: avoid; padding: 1px 2px; }
                img { max-width: 100%; }
                .page-break { page-break-before: always; break-before: page; }
                @media print {
                    body { margin: 0; padding: 0; }
                    table { font-size: 6.5pt !important; }
                    .no-print { display: none !important; }
                }
            </style>
        </head>
        <body>' . $body . '</body></html>';
    }

    /**
     * Strip full-document wrapper for DOCX generation (PhpWord expects HTML fragment)
     */
    protected function extractBodyFragment(string $html): string
    {
        if (preg_match('/<body[^>]*>(.*)<\/body>/si', $html, $m)) {
            $fragment = $m[1];
        } else {
            $fragment = $html;
        }
        // Remove <style> blocks (unsupported in DOCX)
        $fragment = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $fragment);
        // Fix void elements for XML compliance (skip already self-closed tags)
        $fragment = preg_replace('~<(br|hr|img)([^>]*?)(?<!/)>~i', '<$1$2 />', $fragment);
        return $fragment;
    }

    protected function downloadPdf(string $html, string $filename)
    {
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'dpi' => 150,
            'defaultFont' => 'serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);
        return $pdf->download($filename . '.pdf');
    }

    protected function downloadDocx(string $html, string $filename)
    {
        $fragment = $this->extractBodyFragment($html);

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->getStyle()->setPageSizeW(11906);
        $section->getStyle()->setPageSizeH(16838);
        Html::addHtml($section, $fragment, false, false);
        $tempFile = tempnam(sys_get_temp_dir(), 'docx');
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);
        return response()->download($tempFile, $filename . '.docx')->deleteFileAfterSend(true);
    }
}
