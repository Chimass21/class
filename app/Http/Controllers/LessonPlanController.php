<?php

namespace App\Http\Controllers;

use App\Helpers\JsonDb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LessonPlanController extends Controller
{
    public function create()
    {
        return view('teacher.planner');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'class' => 'required|string|max:255',
            'term' => 'required|string|max:255',
            'week' => 'required|integer',
            'topic' => 'required|string|max:255',
            'subTopic' => 'nullable|string|max:255',
            'content' => 'required|array',
        ]);

        JsonDb::init();
        $db = JsonDb::get();
        $user = Session::get('user');

        $plan = [
            'id' => 'plan_' . uniqid(),
            'teacherId' => $user['id'] ?? 'unknown',
            'schoolName' => $request->schoolName ?? 'Covenant Foundation School',
            'teacherName' => $user['name'] ?? 'Teacher',
            'subject' => $request->subject,
            'classLevel' => $request->class,
            'topic' => $request->topic,
            'subTopic' => $request->subTopic ?? '',
            'duration' => $request->duration ?? '40 Minutes',
            'term' => $request->term,
            'week' => $request->week,
            'content' => $request->content,
            'createdAt' => now()->toIso8601String(),
        ];

        $db['lessonPlans'][] = $plan;
        JsonDb::save($db);

        return redirect()->route('teacher.dashboard')->with('success', 'Lesson plan created successfully.');
    }
}
