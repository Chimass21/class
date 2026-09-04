<?php

namespace App\Http\Controllers;

use App\Helpers\JsonDb;
use App\Helpers\WhatsApp;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function apiIndex()
    {
        $user = \Illuminate\Support\Facades\Session::get('user');
        if (!$user) return response()->json(['results' => [], 'whatsapp' => ['phone' => WhatsApp::phone()]], 401);
        JsonDb::init();
        $db = JsonDb::get();
        $role = $user['role'] ?? '';
        $uid = $user['id'] ?? '';
        if ($role === 'admin') {
            $results = $db['results'] ?? [];
        } elseif ($role === 'teacher') {
            $ownExamIds = array_column(array_filter($db['exams'] ?? [], fn($e) => ($e['creatorId'] ?? '') === $uid), 'id');
            $results = array_values(array_filter($db['results'] ?? [], fn($r) => in_array($r['examId'] ?? '', $ownExamIds, true)));
        } else {
            $results = array_values(array_filter($db['results'] ?? [], fn($r) => ($r['studentId'] ?? '') === $uid));
        }
        return response()->json([
            'results' => $results,
            'whatsapp' => ['phone' => WhatsApp::phone()],
        ]);
    }

    public function apiShow($resultId)
    {
        $user = \Illuminate\Support\Facades\Session::get('user');
        if (!$user) return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        JsonDb::init();
        $db = JsonDb::get();
        $resultId = trim($resultId);
        $role = $user['role'] ?? '';
        $uid = $user['id'] ?? '';
        $ownExamIds = $role === 'teacher' ? array_column(array_filter($db['exams'] ?? [], fn($e) => ($e['creatorId'] ?? '') === $uid), 'id') : [];
        foreach ($db['results'] as $result) {
            if (trim($result['id'] ?? '') == $resultId) {
                $ownerCheck = ($result['studentId'] ?? '') === $uid;
                $teacherCheck = $role === 'teacher' && in_array($result['examId'] ?? '', $ownExamIds, true);
                if ($role !== 'admin' && !$ownerCheck && !$teacherCheck) {
                    return response()->json(['success' => false, 'error' => 'Unauthorized - result is private to its owner'], 403);
                }
                // Attach exam questions for answer review
                $exam = null;
                foreach ($db['exams'] as $e) {
                    if (trim($e['id'] ?? '') == trim($result['examId'] ?? '')) {
                        $exam = $e;
                        break;
                    }
                }
                return response()->json([
                    'success' => true,
                    'result' => $result,
                    'exam' => $exam,
                ]);
            }
        }
        return response()->json(['success' => false, 'error' => 'Result not found.', 'resultId' => $resultId], 404);
    }

    public function apiStudentResults($studentId)
    {
        $user = \Illuminate\Support\Facades\Session::get('user');
        if (!$user) return response()->json(['results' => []], 401);
        $role = $user['role'] ?? '';
        $uid = $user['id'] ?? '';
        // Only admin, the student themselves, or a teacher who owns the exam can query
        if ($role !== 'admin' && $studentId !== $uid) {
            if ($role === 'teacher') {
                // Teacher can only see results for their own exams - filter below
                JsonDb::init();
                $db = JsonDb::get();
                $ownExamIds = array_column(array_filter($db['exams'] ?? [], fn($e) => ($e['creatorId'] ?? '') === $uid), 'id');
                $results = array_values(array_filter($db['results'] ?? [], fn($r) => ($r['studentId'] ?? '') === $studentId && in_array($r['examId'] ?? '', $ownExamIds, true)));
                return response()->json(['results' => $results]);
            }
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        JsonDb::init();
        $db = JsonDb::get();
        $results = array_values(array_filter($db['results'], fn($r) => ($r['studentId'] ?? '') === $studentId));
        return response()->json(['results' => $results]);
    }
}
