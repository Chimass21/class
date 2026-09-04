<?php

namespace App\Http\Controllers;

use App\Helpers\JsonDb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function dashboard()
    {
        JsonDb::init();
        $db = JsonDb::get();
        $user = Session::get('user');
        $exams = array_filter($db['exams'], fn($e) => $e['isPublished']);
        $results = array_filter($db['results'], fn($r) => $r['studentId'] === $user['id']);
        return view('student.dashboard', ['exams' => $exams, 'results' => $results]);
    }

    public function startExam(Request $request, $examId)
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
        if (!$exam) abort(404);
        if (empty($exam['isPublished'])) {
            $checkUser = Session::get('user');
            if (!$checkUser || (($checkUser['role'] ?? '') !== 'admin' && ($exam['creatorId'] ?? '') !== ($checkUser['id'] ?? ''))) {
                abort(403, 'This exam is not yet published and is private to its owner.');
            }
        }

        $user = Session::get('user');

        // Guest flow - require name entry
        if (!$user) {
            $guestName = $request->query('name', '');
            if (empty($guestName)) {
                return view('student.guest-name', ['exam' => (object)$exam]);
            }
            $user = [
                'id' => 'guest_' . uniqid(),
                'name' => $guestName,
                'email' => strtolower(str_replace(' ', '', $guestName)) . '@student.cbt',
                'role' => 'student',
                'isGuest' => true,
                'walletBalance' => 0,
            ];
        }

        // Check attempt limit for guests
        if (isset($user['isGuest']) || !Session::has('user')) {
            $attempts = 0;
            foreach ($db['results'] as $r) {
                if ($r['examId'] === $examId && $r['studentName'] === $user['name']) {
                    $attempts++;
                }
            }
            if ($attempts >= 2) {
                return redirect()->route('landing')->with('error', "Access Denied: '{$user['name']}' has already taken this exam 2 times.");
            }
        }

        $exam['questions'] = array_values(array_filter($exam['questions'] ?? [], fn($q) => is_array($q)));
        return view('student.exam', ['exam' => (object)$exam, 'studentUser' => $user]);
    }

    public function submitExam(Request $request, $examId)
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
        if (!$exam) abort(404);

        $answers = $request->input('answers', []);
        $score = 0;
        $correctAnswers = 0;
        $failedQuestions = [];

        foreach ($exam['questions'] as $index => $q) {
            if (!is_array($q)) continue;
            $selected = $answers[$index] ?? null;
            if ($selected === ($q['correctAnswer'] ?? null)) {
                $score += (int)($q['marks'] ?? 1);
                $correctAnswers++;
            } else {
                $failedQuestions[] = [
                    'question' => $q['question'] ?? '',
                    'optionA' => $q['optionA'] ?? '',
                    'optionB' => $q['optionB'] ?? '',
                    'optionC' => $q['optionC'] ?? '',
                    'optionD' => $q['optionD'] ?? '',
                    'selectedAnswer' => $selected,
                    'correctAnswer' => $q['correctAnswer'] ?? '',
                ];
            }
        }

        $totalMarks = count($exam['questions']) * 5;
        $percentage = $totalMarks > 0 ? round(($score / $totalMarks) * 100) : 0;
        $user = Session::get('user');
        $studentId = $user['id'] ?? 'guest_' . uniqid();
        $studentName = $user['name'] ?? $request->input('studentName', 'Guest Scholar');

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
        ];
        $db['results'][] = $result;
        JsonDb::save($db);
        return view('student.result', ['result' => (object)$result, 'exam' => (object)$exam]);
    }
}
