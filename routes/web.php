<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LessonNoteController;
use App\Http\Controllers\LessonPlanController;
use App\Http\Controllers\ReportSheetController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\CsvImportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Landing Page
Route::get('/', [HomeController::class, 'index'])->name('landing');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Student Dashboard (auth required)
Route::prefix('student')->middleware(['json.auth', 'role:student'])->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
});

// Exam start (guest-accessible)
Route::get('/student/exam/{examId}', [StudentController::class, 'startExam'])->name('student.exam.start');
Route::post('/student/exam/{examId}/submit', [StudentController::class, 'submitExam'])->name('student.exam.submit');

// Teacher Routes
Route::prefix('teacher')->middleware(['json.auth', 'role:teacher'])->group(function () {
    Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('teacher.dashboard');
    Route::get('/exams/create', [ExamController::class, 'create'])->name('exam.create');
    Route::post('/exams', [ExamController::class, 'store'])->name('exam.store');
    Route::post('/exams/{examId}/publish', [ExamController::class, 'publish'])->name('exam.publish');
    Route::delete('/exams/{examId}', [ExamController::class, 'destroy'])->name('exam.delete');
    Route::get('/lesson-plans/create', [LessonPlanController::class, 'create'])->name('lesson-plan.create');
    Route::post('/lesson-plans', [LessonPlanController::class, 'store'])->name('lesson-plan.store');
    Route::get('/lesson-notes/create', [LessonNoteController::class, 'create'])->name('lesson-note.create');
    Route::post('/lesson-notes', [LessonNoteController::class, 'store'])->name('lesson-note.store');
    Route::get('/reports', [ReportSheetController::class, 'index'])->name('report-sheet.index');
});

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'adminLogin'])->name('admin.login.post');
});

Route::prefix('admin')->middleware(['json.auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

// Portal Switching (requires login)
Route::middleware('json.auth')->group(function () {
    Route::post('/switch/student', [AuthController::class, 'switchToStudent'])->name('switch.to.student');
    Route::post('/switch/teacher', [AuthController::class, 'switchToTeacher'])->name('switch.to.teacher');
    Route::post('/switch/back', [AuthController::class, 'switchBack'])->name('switch.back');
});

// API Routes (for AJAX calls)
Route::prefix('api')->group(function () {
    // Auth API
    Route::post('/auth/login', [AuthController::class, 'apiLogin']);
    Route::post('/auth/register', [AuthController::class, 'apiRegister']);
    Route::get('/auth/session', [AuthController::class, 'apiSession']);
    Route::post('/auth/logout', [AuthController::class, 'apiLogout']);
    Route::post('/auth/reset', [AuthController::class, 'apiReset']);

    Route::get('/exams', [ExamController::class, 'apiIndex']);
    Route::get('/exams/{exam}', [ExamController::class, 'apiShow']);
    Route::post('/ai/lesson-plan', [AIController::class, 'generateLessonPlan']);
    Route::post('/ai/lesson-note', [AIController::class, 'generateLessonNote']);
    Route::post('/ai/questions', [AIController::class, 'generateQuestions']);
    Route::post('/ai/generate-questions', [AIController::class, 'generateQuestions']);
    Route::get('/school-config', [HomeController::class, 'schoolConfig']);
    Route::post('/school-config', [HomeController::class, 'updateSchoolConfig']);
    Route::get('/subjects', [HomeController::class, 'subjects']);
    Route::get('/curriculum/classes', function () { return response()->json(['classes' => \App\Helpers\CurriculumData::getClasses()]); });
    Route::get('/curriculum/terms', function () { return response()->json(['terms' => \App\Helpers\CurriculumData::getTerms()]); });
    Route::get('/curriculum/weeks', function () { return response()->json(['weeks' => \App\Helpers\CurriculumData::getWeeks()]); });
    Route::get('/report-sheets', [ReportSheetController::class, 'apiIndex']);
    Route::post('/report-sheets', [ReportSheetController::class, 'store']);
    Route::post('/report-sheets/collate', [ReportSheetController::class, 'collate']);
    Route::post('/report-sheets/delete', [ReportSheetController::class, 'delete']);
    Route::get('/users', [AdminController::class, 'apiUsers']);
    Route::get('/questions/sets', [AIController::class, 'getQuestionSets']);
    Route::get('/questions/sets/{id}', [AIController::class, 'getQuestionSet']);
    Route::post('/questions/save', [AIController::class, 'saveGeneratedQuestions']);
    Route::post('/questions/convert-to-exam', [AIController::class, 'convertQuestionsToExam']);
    Route::get('/admin/stats', [AdminController::class, 'apiStats']);
    Route::get('/admin/activities', [AdminController::class, 'apiActivities']);
    Route::post('/admin/users/{userId}/update', [AdminController::class, 'apiUpdateUser']);
    Route::post('/admin/users/{userId}/delete', [AdminController::class, 'apiDeleteUser']);
    Route::post('/admin/feedback/{feedbackId}/delete', [AdminController::class, 'apiDeleteFeedback']);
    Route::get('/admin/ai-settings', [AdminController::class, 'apiAiSettings']);
    Route::post('/admin/ai-settings', [AdminController::class, 'apiUpdateAiSettings']);
    Route::post('/admin/ai-test', [AdminController::class, 'apiTestAi']);
    Route::post('/exams', [ExamController::class, 'apiStore']);
    Route::post('/exams/{examId}/settings', [ExamController::class, 'apiUpdateSettings']);
    Route::post('/exams/{examId}/publish', [ExamController::class, 'apiPublish']);
    Route::delete('/exams/{examId}', [ExamController::class, 'apiDestroy']);
    Route::post('/exams/{examId}/submit', [ExamController::class, 'apiSubmitExam']);
    Route::post('/exams/{examId}/autosave', [ExamController::class, 'apiAutoSave']);
    Route::post('/exams/{examId}/autosave/load', [ExamController::class, 'apiLoadAutoSave']);
    Route::get('/results', [ResultController::class, 'apiIndex'])->name('api.results.index');
    Route::get('/results/{resultId}', [ResultController::class, 'apiShow'])->name('api.results.show');
    Route::get('/results/student/{studentId}', [ResultController::class, 'apiStudentResults'])->name('api.results.student');
    Route::get('/notifications/user/{userId}', [HomeController::class, 'apiUserNotifications']);
    Route::post('/notifications/{id}/read', [HomeController::class, 'apiReadNotification']);
    Route::get('/lesson-notes', [HomeController::class, 'apiAllLessonNotes']);
    Route::get('/teachers/{teacherId}/lesson-plans', [HomeController::class, 'apiTeacherLessonPlans']);
    Route::get('/teachers/{teacherId}/lesson-notes', [HomeController::class, 'apiTeacherLessonNotes']);
    Route::get('/teacher/dashboard', [HomeController::class, 'apiTeacherDashboard']);
    Route::get('/teacher/init', [HomeController::class, 'apiTeacherInit']);
    Route::post('/feedback', [HomeController::class, 'submitFeedback']);
    Route::post('/feedback/chat', [HomeController::class, 'chatFeedback']);
    Route::get('/download/lesson-note/{id}/{format}', [DownloadController::class, 'downloadLessonNote']);
    Route::get('/download/lesson-plan/{id}/{format}', [DownloadController::class, 'downloadLessonPlan']);
    Route::get('/download/exam/{id}/{format}', [DownloadController::class, 'downloadExam']);
    Route::get('/download/graded-script/{examId}/{resultId}/{format?}', [DownloadController::class, 'downloadGradedScript']);
    Route::delete('/lesson-notes/{id}', [AIController::class, 'deleteLessonNote']);
    Route::delete('/lesson-plans/{id}', [AIController::class, 'deleteLessonPlan']);
    Route::delete('/questions/sets/{id}', [AIController::class, 'deleteQuestionSet']);

    // CSV Import
    Route::get('/csv-import/template', [CsvImportController::class, 'downloadTemplate']);
    Route::post('/csv-import/preview', [CsvImportController::class, 'preview']);
    Route::post('/csv-import/import', [CsvImportController::class, 'import']);
    Route::post('/csv-import/convert-json', [CsvImportController::class, 'convertJsonToExam']);
});

// Migrate legacy plaintext passwords to hashed
Route::get('/migrate-passwords', function () {
    $count = 0;
    \App\Helpers\JsonDb::init();
    $db = \App\Helpers\JsonDb::get();
    foreach ($db['users'] as &$user) {
        if (!empty($user['password']) && !\Illuminate\Support\Facades\Hash::isHashed($user['password'])) {
            $user['password'] = \Illuminate\Support\Facades\Hash::make($user['password']);
            $count++;
        }
    }
    \App\Helpers\JsonDb::save($db);
    return "$count passwords hashed.";
});

// Clear all caches (visit this URL after deployment to refresh)
Route::get('/clear-cache', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    $output = \Illuminate\Support\Facades\Artisan::output();
    return '<h2>Cache Cleared</h2><pre>' . $output . '</pre><p><a href="/login">Go to Login</a></p>';
});

// Shared note view
Route::get('/shared/note/{id}', [HomeController::class, 'sharedNoteView'])->name('shared.note');

// Direct portal access
Route::get('/portal/student', function () {
    return redirect()->route('student.dashboard');
})->name('portal.student');

Route::get('/portal/teacher', function () {
    return redirect()->route('teacher.dashboard');
})->name('portal.teacher');

Route::get('/portal/admin', function () {
    return redirect()->route('admin.dashboard');
})->name('portal.admin');

// API fallback for unmatched API routes
Route::fallback(function (Request $request) {
    if (str_starts_with($request->path(), 'api/')) {
        return response()->json(['success' => false, 'error' => 'Route not found.'], 404);
    }
    return redirect()->route('landing');
});
