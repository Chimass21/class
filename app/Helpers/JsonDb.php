<?php
namespace App\Helpers;

use App\Models\User;
use App\Models\Exam;
use App\Models\Result;
use App\Models\LessonNote;
use App\Models\LessonPlan;
use App\Models\Notification;
use App\Models\QuestionSet;
use App\Models\ReportSheet;

class JsonDb {
    private static $dbPath;
    private static $db;

    public static function init() {
        self::$dbPath = base_path('brain_db.json');
        if (!self::$db) {
            self::ensureFileExists();
            self::$db = self::loadFromDb();
        }
    }

    public static function ensureFileExists(): void {
        if (!file_exists(self::$dbPath)) {
            $example = base_path('brain_db.example.json');
            $source = file_exists($example) ? $example : null;
            if ($source) {
                @copy($source, self::$dbPath);
            } else {
                @file_put_contents(self::$dbPath, json_encode(self::defaultData(), JSON_PRETTY_PRINT));
            }
        }
    }

    public static function get() {
        if (!self::$db) self::init();
        return self::$db;
    }

    public static function save($data = null) {
        if ($data !== null) {
            self::$db = $data;
        }
        self::syncToDb();
        $written = @file_put_contents(self::$dbPath, json_encode(self::$db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($written === false) {
            throw new \RuntimeException('Failed to write database file: ' . self::$dbPath . '. Check file permissions.');
        }
    }

    /**
     * Save without syncing to SQLite — used for bulk imports where
     * per-row Eloquent operations are the dominant bottleneck.
     * The SQLite sync will happen on the next regular save() call.
     */
    public static function saveWithoutSync($data = null) {
        if ($data !== null) {
            self::$db = $data;
        }
        $written = @file_put_contents(self::$dbPath, json_encode(self::$db, JSON_UNESCAPED_UNICODE));
        if ($written === false) {
            throw new \RuntimeException('Failed to write database file: ' . self::$dbPath . '. Check file permissions.');
        }
    }

    public static function findUserByEmail($email) {
        self::init();
        foreach (self::$db['users'] as $user) {
            if (strtolower($user['email']) === strtolower($email)) {
                return $user;
            }
        }
        return null;
    }

    public static function findUserByUsername($username) {
        self::init();
        foreach (self::$db['users'] as $user) {
            if (isset($user['username']) && strtolower($user['username']) === strtolower($username)) {
                return $user;
            }
        }
        return null;
    }

    public static function findUserByLogin($login) {
        self::init();
        foreach (self::$db['users'] as $user) {
            if (strtolower($user['email']) === strtolower($login)) {
                return $user;
            }
            if (isset($user['username']) && strtolower($user['username']) === strtolower($login)) {
                return $user;
            }
        }
        return null;
    }

    public static function findUserById($id) {
        self::init();
        foreach (self::$db['users'] as $user) {
            if ($user['id'] === $id) {
                return $user;
            }
        }
        return null;
    }

    private static function loadFromDb() {
        // Load from SQLite if available
        $sqliteData = self::loadFromSqlite();
        // Load from JSON file if available
        $jsonData = null;
        if (file_exists(self::$dbPath)) {
            $json = @file_get_contents(self::$dbPath);
            if ($json !== false) {
                $decoded = json_decode($json, true);
                if (is_array($decoded)) $jsonData = $decoded;
            }
        }
        // Merge: JSON takes precedence (it has live data); SQLite fills gaps
        if ($sqliteData !== null && $jsonData !== null) {
            return array_replace_recursive($sqliteData, $jsonData);
        }
        if ($jsonData !== null) return $jsonData;
        if ($sqliteData !== null) return $sqliteData;
        return self::defaultData();
    }

    private static function loadFromSqlite() {
        $users = [];
        try {
            // If this fails (no driver, no tables), return null so caller falls back to JSON
            $userModels = User::all();
        } catch (\Exception $e) {
            return null;
        }
        foreach ($userModels as $u) {
            $users[] = [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'username' => $u->username,
                'password' => $u->password,
                'role' => $u->role,
                'regNumber' => $u->reg_number,
                'classLevel' => $u->class_level,
                'isSuspended' => $u->is_suspended,
                'walletBalance' => $u->wallet_balance,
                'createdAt' => $u->created_at?->toIso8601String(),
            ];
        }

        $exams = [];
        try {
            foreach (Exam::all() as $e) {
                $exams[] = [
                    'id' => $e->id,
                    'title' => $e->title,
                    'subject' => $e->subject,
                    'level' => $e->level,
                    'duration' => $e->duration,
                    'totalMarks' => $e->total_marks,
                    'instructions' => $e->instructions,
                    'questions' => $e->questions ?? [],
                    'creatorId' => $e->creator_id,
                    'creatorName' => $e->creator_name,
                    'isPublished' => $e->is_published,
                    'createdAt' => $e->created_at?->toIso8601String(),
                ];
            }
        } catch (\Exception $e) {}

        $results = [];
        try {
            foreach (Result::all() as $r) {
                $results[] = [
                    'id' => $r->id,
                    'examId' => $r->exam_id,
                    'examTitle' => $r->exam_title,
                    'subject' => $r->subject,
                    'studentId' => $r->student_id,
                    'studentName' => $r->student_name,
                    'score' => $r->score,
                    'percentage' => $r->percentage,
                    'totalQuestions' => $r->total_questions,
                    'correctAnswers' => $r->correct_answers,
                    'failedQuestions' => $r->failed_questions ?? [],
                    'date' => $r->date?->toIso8601String(),
                    'timeSpent' => $r->time_spent ?? 0,
                    'totalPossibleMarks' => $r->total_possible_marks ?? 0,
                ];
            }
        } catch (\Exception $e) {}

        $lessonNotes = [];
        try {
            foreach (LessonNote::all() as $n) {
                $content = $n->content ?? [];
                $lessonNotes[] = array_merge([
                    'id' => $n->id,
                    'teacherId' => $n->teacher_id,
                    'subject' => $n->subject,
                    'classLevel' => $n->class_level,
                    'topic' => $n->topic,
                    'subTopic' => $n->sub_topic,
                    'periods' => $n->periods,
                    'difficulty' => $n->difficulty,
                    'createdAt' => $n->created_at?->toIso8601String(),
                ], $content);
            }
        } catch (\Exception $e) {}

        $lessonPlans = [];
        try {
            foreach (LessonPlan::all() as $p) {
                $content = $p->content ?? [];
                $lessonPlans[] = array_merge([
                    'id' => $p->id,
                    'teacherId' => $p->teacher_id,
                    'schoolName' => $p->school_name,
                    'teacherName' => $p->teacher_name,
                    'subject' => $p->subject,
                    'classLevel' => $p->class_level,
                    'topic' => $p->topic,
                    'subTopic' => $p->sub_topic,
                    'duration' => $p->duration,
                    'week' => $p->week,
                    'createdAt' => $p->created_at?->toIso8601String(),
                ], $content);
            }
        } catch (\Exception $e) {}

        $notifications = [];
        try {
            foreach (Notification::all() as $n) {
                $notifications[] = [
                    'id' => $n->id,
                    'userId' => $n->user_id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'read' => $n->read,
                    'date' => $n->date?->toIso8601String(),
                ];
            }
        } catch (\Exception $e) {}

        $reportSheets = [];
        try {
            foreach (ReportSheet::all() as $r) {
                $reportSheets[] = [
                    'id' => $r->id,
                    'studentId' => $r->student_id,
                    'studentName' => $r->student_name,
                    'classLevel' => $r->class_level,
                    'term' => $r->term,
                    'scores' => $r->scores ?? [],
                    'studentAverage' => $r->student_average,
                    'classAverage' => $r->class_average,
                    'psychomotor' => $r->psychomotor ?? [],
                    'cognitive' => $r->cognitive ?? [],
                    'teacherRemark' => $r->teacher_remark,
                    'principalRemark' => $r->principal_remark,
                    'createdAt' => $r->created_at?->toIso8601String(),
                ];
            }
        } catch (\Exception $e) {}

        $questionSets = [];
        try {
            foreach (QuestionSet::all() as $qs) {
                $questionSets[] = [
                    'id' => $qs->id,
                    'teacherId' => $qs->teacher_id,
                    'source' => $qs->source,
                    'sourceId' => $qs->source_id,
                    'questions' => $qs->questions ?? [],
                    'createdAt' => $qs->created_at?->toIso8601String(),
                ];
            }
        } catch (\Exception $e) {}

        return array_merge(self::defaultData(), [
            'users' => $users,
            'exams' => $exams,
            'results' => $results,
            'lessonNotes' => $lessonNotes,
            'lessonPlans' => $lessonPlans,
            'notifications' => $notifications,
            'reportSheets' => $reportSheets,
            'questionSets' => $questionSets,
        ]);
    }

    private static function defaultData() {
        return [
            'users' => [],
            'exams' => [],
            'results' => [],
            'lessonNotes' => [],
            'lessonPlans' => [],
            'notifications' => [],
            'reportSheets' => [],
            'transactions' => [],
            'questionSets' => [],
            'importLogs' => [],
            'subjects' => ['Mathematics','Physics','Chemistry','Biology','English Language','Accounting','Economics','Government','ICT','Literature','Commerce','Agriculture','Civic Education','Social Studies','Intermediate Science','Cultural and Creative Arts (CCA)','Nigerian History','Artificial Intelligence','Phonics','Vocational Studies','Physical and Health Education (PHE)','Coding','Verbal Reasoning','Craft and Art','Home Economics','Business Studies','Basic Science','Basic Technology','Geography','History','Christian Religious Studies','Islamic Studies','French','Yoruba','Igbo','Hausa','Music','Art & Design','Literacy','Numeracy'],
            'schoolConfig' => ['name' => 'Covenant Foundation School', 'address' => '', 'motto' => 'Excellence in Education'],
        ];
    }

    private static function syncToDb() {
        if (!self::$db) return;
        try {
        foreach (self::$db['users'] as $uData) {
            User::updateOrCreate(
                ['id' => $uData['id']],
                [
                    'name' => $uData['name'],
                    'email' => $uData['email'],
                    'username' => $uData['username'] ?? null,
                    'password' => $uData['password'],
                    'role' => $uData['role'],
                    'reg_number' => $uData['regNumber'] ?? null,
                    'class_level' => $uData['classLevel'] ?? null,
                    'is_suspended' => $uData['isSuspended'] ?? false,
                    'wallet_balance' => $uData['walletBalance'] ?? 0,
                ]
            );
        }
        foreach (self::$db['exams'] as $eData) {
            Exam::updateOrCreate(
                ['id' => $eData['id']],
                [
                    'title' => $eData['title'],
                    'subject' => $eData['subject'],
                    'level' => $eData['level'] ?? null,
                    'duration' => $eData['duration'] ?? 0,
                    'total_marks' => $eData['totalMarks'] ?? 0,
                    'instructions' => $eData['instructions'] ?? null,
                    'questions' => $eData['questions'] ?? [],
                    'creator_id' => $eData['creatorId'] ?? null,
                    'creator_name' => $eData['creatorName'] ?? null,
                    'is_published' => $eData['isPublished'] ?? false,
                ]
            );
        }
        foreach (self::$db['results'] as $rData) {
            Result::updateOrCreate(
                ['id' => $rData['id']],
                [
                    'exam_id' => $rData['examId'],
                    'exam_title' => $rData['examTitle'],
                    'subject' => $rData['subject'],
                    'student_id' => $rData['studentId'],
                    'student_name' => $rData['studentName'],
                    'score' => $rData['score'],
                    'percentage' => $rData['percentage'],
                    'total_questions' => $rData['totalQuestions'],
                    'correct_answers' => $rData['correctAnswers'],
                    'failed_questions' => $rData['failedQuestions'] ?? [],
                    'time_spent' => $rData['timeSpent'] ?? 0,
                    'total_possible_marks' => $rData['totalPossibleMarks'] ?? 0,
                ]
            );
        }
        foreach (self::$db['lessonNotes'] as $nData) {
            $content = array_diff_key($nData, array_flip(['id','teacherId','subject','classLevel','topic','subTopic','periods','difficulty','createdAt']));
            LessonNote::updateOrCreate(
                ['id' => $nData['id']],
                [
                    'teacher_id' => $nData['teacherId'],
                    'subject' => $nData['subject'],
                    'class_level' => $nData['classLevel'],
                    'topic' => $nData['topic'],
                    'sub_topic' => $nData['subTopic'] ?? null,
                    'periods' => $nData['periods'] ?? null,
                    'difficulty' => $nData['difficulty'] ?? null,
                    'content' => $content,
                ]
            );
        }
        foreach (self::$db['lessonPlans'] as $pData) {
            $content = array_diff_key($pData, array_flip(['id','teacherId','schoolName','teacherName','subject','classLevel','topic','subTopic','duration','week','createdAt']));
            LessonPlan::updateOrCreate(
                ['id' => $pData['id']],
                [
                    'teacher_id' => $pData['teacherId'],
                    'school_name' => $pData['schoolName'] ?? null,
                    'teacher_name' => $pData['teacherName'] ?? null,
                    'subject' => $pData['subject'],
                    'class_level' => $pData['classLevel'],
                    'topic' => $pData['topic'],
                    'sub_topic' => $pData['subTopic'] ?? null,
                    'term' => $pData['term'] ?? null,
                    'duration' => $pData['duration'] ?? '40 Minutes',
                    'week' => $pData['week'] ?? 1,
                    'content' => $content,
                ]
            );
        }
        foreach (self::$db['questionSets'] as $qsData) {
            QuestionSet::updateOrCreate(
                ['id' => $qsData['id']],
                [
                    'teacher_id' => $qsData['teacherId'],
                    'source' => $qsData['source'] ?? null,
                    'source_id' => $qsData['sourceId'] ?? null,
                    'questions' => $qsData['questions'] ?? [],
                ]
            );
        }
        } catch (\Exception $e) {}
    }

    public static function createUser($userData) {
        try {
            return User::create([
                'id' => $userData['id'],
                'name' => $userData['name'],
                'email' => $userData['email'],
                'username' => $userData['username'] ?? null,
                'password' => $userData['password'],
                'role' => $userData['role'],
                'reg_number' => $userData['regNumber'] ?? null,
                'wallet_balance' => $userData['walletBalance'] ?? 0,
                'is_suspended' => $userData['isSuspended'] ?? false,
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function updateUserPassword($userId, $newHashedPassword) {
        try {
            User::where('id', $userId)->update(['password' => $newHashedPassword]);
        } catch (\Exception $e) {}
    }
}
