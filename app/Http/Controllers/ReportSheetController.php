<?php

namespace App\Http\Controllers;

use App\Helpers\JsonDb;
use Illuminate\Http\Request;

class ReportSheetController extends Controller
{
    public function index()
    {
        $user = \Illuminate\Support\Facades\Session::get('user');
        JsonDb::init();
        $all = JsonDb::get()['reportSheets'] ?? [];
        if ($user && ($user['role'] ?? '') === 'admin') {
            $reports = $all;
        } elseif ($user) {
            $uid = $user['id'] ?? '';
            // Teacher sees sheets they created or where studentId matches them if student
            $reports = array_values(array_filter($all, fn($r) => ($r['teacherId'] ?? '') === $uid || ($r['studentId'] ?? '') === $uid));
        } else {
            $reports = [];
        }
        return view('teacher.reports', compact('reports'));
    }

    public function apiIndex()
    {
        $user = \Illuminate\Support\Facades\Session::get('user');
        if (!$user) return response()->json(['reportSheets' => []], 401);
        JsonDb::init();
        $all = JsonDb::get()['reportSheets'] ?? [];
        if (($user['role'] ?? '') === 'admin') {
            return response()->json(['reportSheets' => $all]);
        }
        $uid = $user['id'] ?? '';
        $filtered = array_values(array_filter($all, fn($r) => ($r['teacherId'] ?? '') === $uid || ($r['studentId'] ?? '') === $uid));
        return response()->json(['reportSheets' => $filtered]);
    }

    public function store(Request $request)
    {
        $user = \Illuminate\Support\Facades\Session::get('user');
        if (!$user) return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        JsonDb::init();
        $db = JsonDb::get();
        $sheet = $request->all();
        $sheet['id'] = 'rpt_' . uniqid();
        $sheet['teacherId'] = $user['id'] ?? 'unknown';
        $db['reportSheets'][] = $sheet;
        JsonDb::save($db);
        return response()->json(['success' => true, 'reportSheet' => $sheet]);
    }

    public function collate(Request $request)
    {
        $user = \Illuminate\Support\Facades\Session::get('user');
        if (!$user) return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        JsonDb::init();
        $db = JsonDb::get();
        $collated = $request->input('sheets', []);
        foreach ($collated as $sheet) {
            $sheet['id'] = 'rpt_' . uniqid();
            $sheet['teacherId'] = $user['id'] ?? 'unknown';
            $db['reportSheets'][] = $sheet;
        }
        JsonDb::save($db);
        return response()->json(['success' => true, 'count' => count($collated)]);
    }

    public function delete(Request $request)
    {
        $user = \Illuminate\Support\Facades\Session::get('user');
        if (!$user) return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        JsonDb::init();
        $db = JsonDb::get();
        $ids = (array) $request->input('ids', []);
        // Only allow owner or admin to delete
        if (($user['role'] ?? '') !== 'admin') {
            $uid = $user['id'] ?? '';
            // Filter to only sheets owned by this user
            $ownedIds = array_column(array_filter($db['reportSheets'] ?? [], fn($r) => ($r['teacherId'] ?? '') === $uid), 'id');
            $ids = array_intersect($ids, $ownedIds);
        }
        $db['reportSheets'] = array_values(array_filter($db['reportSheets'] ?? [], fn($r) => !in_array($r['id'] ?? '', $ids)));
        JsonDb::save($db);
        return response()->json(['success' => true]);
    }
}
