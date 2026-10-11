@extends('layouts.app')

@section('content')
<style>
    select, input[type="text"], input[type="number"], textarea {
        background: linear-gradient(135deg, #f0fdf4 0%, #fefce8 25%, #fff5f5 50%, #f0f4ff 75%, #fdf2f8 100%) !important;
        border: 2px solid transparent !important;
        background-clip: padding-box !important;
        border-image: linear-gradient(135deg, #10b981, #f59e0b, #ef4444, #8b5cf6) 1 !important;
        transition: all 0.3s ease !important;
        font-weight: 600 !important;
        color: #1e293b !important;
    }
    select:hover, input:hover, textarea:hover {
        box-shadow: 0 0 20px rgba(245,158,11,0.25), 0 0 40px rgba(16,185,129,0.1) !important;
        transform: translateY(-1px);
    }
    select:focus, input:focus, textarea:focus {
        box-shadow: 0 0 0 3px rgba(139,92,246,0.3), 0 0 30px rgba(239,68,68,0.2) !important;
        border-image: linear-gradient(135deg, #22c55e, #eab308, #f43f5e, #a855f7) 2 !important;
        outline: none !important;
    }
    button[type="submit"], .gen-btn, .action-btn {
        background: linear-gradient(135deg, #f59e0b, #ec4899, #8b5cf6) !important;
        border: none !important;
        box-shadow: 0 4px 15px rgba(236,72,153,0.4), 0 0 30px rgba(245,158,11,0.2) !important;
        transition: all 0.3s ease !important;
        font-weight: 700 !important;
        letter-spacing: 0.5px !important;
        position: relative !important;
        overflow: hidden !important;
        color: white !important;
    }
    button[type="submit"]:hover, .gen-btn:hover, .action-btn:hover {
        transform: translateY(-2px) scale(1.02) !important;
        box-shadow: 0 6px 25px rgba(236,72,153,0.5), 0 0 50px rgba(139,92,246,0.3) !important;
    }
    button[type="submit"]:active, .gen-btn:active, .action-btn:active {
        transform: translateY(0) scale(0.98) !important;
    }
    button[type="submit"]::after, .gen-btn::after, .action-btn::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
        transform: rotate(45deg) translateX(-100%);
        transition: 0.6s;
    }
    button[type="submit"]:hover::after, .gen-btn:hover::after, .action-btn:hover::after {
        transform: rotate(45deg) translateX(100%);
    }
    .tab-btn {
        transition: all 0.3s ease !important;
    }
    .tab-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(99,102,241,0.3);
    }
    .ltab-btn {
        transition: all 0.3s ease !important;
    }
    .ltab-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99,102,241,0.25);
    }
    #join-exam-btn, .start-exam-btn {
        background: linear-gradient(135deg, #06b6d4, #8b5cf6) !important;
        box-shadow: 0 4px 15px rgba(6,182,212,0.4), 0 0 30px rgba(139,92,246,0.2) !important;
        transition: all 0.3s ease !important;
    }
    #join-exam-btn:hover, .start-exam-btn:hover {
        transform: translateY(-2px) scale(1.02) !important;
        box-shadow: 0 6px 25px rgba(6,182,212,0.5), 0 0 50px rgba(139,92,246,0.3) !important;
    }
    [class*="bg-gradient"] {
        transition: all 0.3s ease !important;
        position: relative !important;
        overflow: hidden !important;
    }
    [class*="bg-gradient"]:hover {
        transform: translateY(-2px) scale(1.02) !important;
        filter: brightness(1.1) saturate(1.2) !important;
        box-shadow: 0 6px 25px rgba(139,92,246,0.4), 0 0 50px rgba(236,72,153,0.2) !important;
    }
    [class*="bg-gradient"]::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
        transform: rotate(45deg) translateX(-100%);
        transition: 0.6s;
    }
    [class*="bg-gradient"]:hover::after {
        transform: rotate(45deg) translateX(100%);
    }
</style>
<div class="flex flex-col md:flex-row min-h-screen w-full font-sans bg-slate-50 text-slate-800">
    <!-- Sidebar Navigation -->
    <aside class="w-full md:w-64 bg-indigo-900 text-white flex flex-col shrink-0 border-b md:border-b-0 md:border-r border-indigo-950">
        <div class="p-6 flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-tr from-cyan-400 to-indigo-500 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                <span class="text-2xl font-bold">S</span>
            </div>
            <span class="text-2xl font-black tracking-tight">ClassPortal</span>
        </div>
        <nav class="flex-1 px-4 py-4 space-y-1" id="sidebar-nav">
            <button onclick="switchTab('exams')" data-tab="exams" class="tab-btn w-full flex items-center space-x-3 p-3 rounded-lg text-xs font-bold transition text-left cursor-pointer bg-indigo-800 text-white">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                <span class="flex-1">Take CBT Exams</span>
            </button>
            <button onclick="switchTab('lesson_notes')" data-tab="lesson_notes" class="tab-btn w-full flex items-center space-x-3 p-3 rounded-lg text-xs font-bold transition text-left cursor-pointer text-indigo-300 hover:bg-indigo-800 hover:text-white">
                <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span class="flex-1">Subject Lesson Notes</span>
            </button>
            <button onclick="switchTab('report_card')" data-tab="report_card" class="tab-btn w-full flex items-center space-x-3 p-3 rounded-lg text-xs font-bold transition text-left cursor-pointer text-indigo-300 hover:bg-indigo-800 hover:text-white">
                <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="flex-1">My Term Report Card</span>
            </button>
            <button onclick="switchTab('results')" data-tab="results" class="tab-btn w-full flex items-center space-x-3 p-3 rounded-lg text-xs font-bold transition text-left cursor-pointer text-indigo-300 hover:bg-indigo-800 hover:text-white">
                <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                <span class="flex-1">Scores & Reports</span>
            </button>
            <button onclick="switchTab('practice')" data-tab="practice" class="tab-btn w-full flex items-center space-x-3 p-3 rounded-lg text-xs font-bold transition text-left cursor-pointer text-indigo-300 hover:bg-indigo-800 hover:text-white">
                <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span class="flex-1">Study Revision</span>
            </button>
            <button onclick="switchTab('library')" data-tab="library" class="tab-btn w-full flex items-center space-x-3 p-3 rounded-lg text-xs font-bold transition text-left cursor-pointer text-indigo-300 hover:bg-indigo-800 hover:text-white">
                <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l3 3h7a2 2 0 012 2v7a2 2 0 01-2 2H5z"/></svg>
                <span class="flex-1">My Library Portal</span>
            </button>
            <button onclick="switchTab('notifications')" data-tab="notifications" class="tab-btn w-full flex items-center space-x-3 p-3 rounded-lg text-xs font-bold transition text-left cursor-pointer text-indigo-300 hover:bg-indigo-800 hover:text-white">
                <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="flex-1">Notifications</span>
                <span id="notif-badge" class="hidden bg-rose-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full">0</span>
            </button>
        </nav>
        <div class="p-6 border-t border-indigo-950/45">
            <div class="bg-indigo-800/50 rounded-2xl p-4 border border-indigo-700/50">
                <div class="text-[10px] text-indigo-300 uppercase font-black tracking-wider mb-1">Pass Ratio</div>
                <div class="text-xl font-bold text-white" id="pass-ratio">0%</div>
                <div class="text-[9px] text-indigo-400 font-semibold mt-1" id="avg-grade">Average Grade: 0%</div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0">
        <!-- Top Header -->
        <header class="h-20 bg-white border-b border-slate-200 px-6 md:px-8 flex items-center justify-between shrink-0">
            <div class="flex items-center space-x-4">
                <div class="bg-slate-200 px-4 py-2 rounded-lg text-xs font-semibold text-slate-700">
                    ⚡ Student Dashboard
                </div>
            </div>
            <div class="flex items-center space-x-3">
                @if(Session::get('user._switched'))
                    <form action="{{ route('switch.back') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-amber-50 text-amber-700 rounded-lg text-xs font-bold hover:bg-amber-100 transition cursor-pointer">
                            ⬅ Back to {{ ucfirst(Session::get('user._original_role')) }}
                        </button>
                    </form>
                @endif
                <div class="text-right">
                    <div class="text-sm font-bold text-slate-900">{{ session('user')['name'] ?? 'Student' }}</div>
                    <div class="text-[10px] text-slate-400 uppercase tracking-widest font-black">Active Student</div>
                </div>
                <div class="w-10 h-10 rounded-full bg-indigo-100 border-2 border-indigo-200 flex items-center justify-center font-bold text-indigo-800 text-base">
                    {{ strtoupper(substr(session('user')['name'] ?? 'S', 0, 1)) }}
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 rounded-xl text-xs font-bold transition cursor-pointer border-none">Sign Out</button>
                </form>
            </div>
        </header>

        <!-- Content Container -->
        <div class="flex-grow p-6 md:p-8 space-y-6 overflow-y-auto">

            <!-- TAB: EXAMS -->
            <div id="tab-exams" class="tab-content space-y-6">
                <div class="flex items-end justify-between border-b border-slate-100 pb-2">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Take CBT Exams</h1>
                        <p class="text-xs text-slate-500 font-medium">Prepare or join ongoing published timed computer-based tests.</p>
                    </div>
                </div>

                <!-- Overview Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="p-6 bg-white border border-slate-150 rounded-3xl shadow-xs space-y-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Streak & Level Badges</h3>
                            <span class="text-[10px] bg-indigo-50 text-indigo-700 px-2.5 py-0.5 rounded-full font-black uppercase">Level 1 Scholar</span>
                        </div>
                        <div class="p-4 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl text-white space-y-2 shadow-sm relative overflow-hidden">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-6 h-6 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/></svg>
                                <span class="text-lg font-black" id="streak-count">0 Days Study Streak!</span>
                            </div>
                            <p class="text-[11px] text-amber-50 leading-snug">Keep up your daily review drills to earn the Habit Titan badge.</p>
                            <div class="pt-3 border-t border-white/25 mt-2 space-y-1.5 text-[10px] font-bold">
                                <label class="flex items-center gap-2"><input type="checkbox" class="rounded accent-orange-700" /> <span>Complete Today's Review Drill</span></label>
                                <label class="flex items-center gap-2"><input type="checkbox" class="rounded accent-orange-700" /> <span>Read Study Notes</span></label>
                                <label class="flex items-center gap-2"><input type="checkbox" class="rounded accent-orange-700" /> <span>Watch Video Lesson</span></label>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <span class="text-[10px] text-slate-400 font-extrabold uppercase block tracking-wider">Earned Achievement Badges</span>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="p-2 bg-slate-50 border border-slate-150 rounded-xl text-center"><span class="text-lg block">🧠</span><span class="text-[9px] font-black block text-slate-800">Cognitive Champion</span></div>
                                <div class="p-2 bg-indigo-50 border border-indigo-150 rounded-xl text-center"><span class="text-lg block">🔥</span><span class="text-[9px] font-black block text-indigo-900">Habit Titan</span></div>
                                <div class="p-2 bg-amber-50 border border-amber-200 rounded-xl text-center"><span class="text-lg block">📘</span><span class="text-[9px] font-black block text-amber-900">Syllabus Crusher</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2 p-6 bg-white border border-slate-150 rounded-3xl shadow-xs space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Progress Overview</h4>
                                <span class="text-sm font-black text-slate-800">Your Recent CBT Scores Comparison</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[9px] text-slate-450 uppercase block font-black">AVERAGE COMPILATION</span>
                                <span class="text-base font-black text-indigo-600" id="avg-compilation">0%</span>
                            </div>
                        </div>
                        <div class="h-44 w-full" id="chart-container">
                            <div class="h-full flex flex-col items-center justify-center text-slate-400 text-xs gap-1">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                <span>No tests taken yet. Completed CBT scores will map here.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Learning Portal -->
                <div class="p-6 bg-white border border-slate-150 rounded-3xl shadow-sm space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                Academic Learning & Study Portal
                            </h3>
                            <p class="text-[11px] text-slate-450 font-bold mt-0.5">Access video tutorials, upload study materials or notes, and submit homework assignments.</p>
                        </div>
                        <div class="flex bg-slate-100 p-1 rounded-xl" id="learning-tabs">
                            <button onclick="switchLearningTab('videos')" data-ltab="videos" class="ltab-btn flex items-center gap-1.5 py-1.5 px-3.5 rounded-lg text-xs font-bold transition cursor-pointer border-none bg-white text-indigo-950 shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <span>Video Tutorials</span>
                            </button>
                            <button onclick="switchLearningTab('materials')" data-ltab="materials" class="ltab-btn flex items-center gap-1.5 py-1.5 px-3.5 rounded-lg text-xs font-bold transition cursor-pointer border-none text-slate-700 hover:text-slate-900">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <span>Study Materials</span>
                            </button>
                            <button onclick="switchLearningTab('assignments')" data-ltab="assignments" class="ltab-btn flex items-center gap-1.5 py-1.5 px-3.5 rounded-lg text-xs font-bold transition cursor-pointer border-none text-slate-700 hover:text-slate-900">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <span>Homework Submissions</span>
                            </button>
                        </div>
                    </div>
                    <div id="ltab-videos" class="ltab-content grid grid-cols-1 md:grid-cols-3 gap-6" id="video-list"></div>
                    <div id="ltab-materials" class="ltab-content hidden space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-5 bg-slate-50 border border-dashed border-slate-300 rounded-2xl text-center space-y-3 flex flex-col items-center justify-center min-h-[160px]">
                                <svg class="w-10 h-10 text-indigo-500 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <div>
                                    <h5 class="text-xs font-black text-slate-800">Upload Personal Study Notes</h5>
                                    <p class="text-[10px] text-slate-500 max-w-[240px] mt-0.5">Select a file (PDF, DOCX, PNG) from your device to save to your cloud portal folder.</p>
                                </div>
                                <label class="inline-block py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-[11px] rounded-xl cursor-pointer shadow-xs transition">
                                    Choose File
                                    <input type="file" class="hidden" id="file-upload-input" />
                                </label>
                            </div>
                            <div class="space-y-3">
                                <span class="text-[10px] text-slate-400 font-extrabold uppercase block tracking-wider">Available Library Study Guides</span>
                                <div id="study-materials-list" class="space-y-3"></div>
                            </div>
                        </div>
                    </div>
                    <div id="ltab-assignments" class="ltab-content hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <span class="text-[10px] text-slate-400 font-extrabold uppercase block tracking-wider">Assigned Homework Assignments</span>
                            <div id="assignments-list" class="space-y-3"></div>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 p-5 rounded-2xl space-y-4">
                            <h5 class="text-xs font-black text-slate-800 flex items-center gap-1.5 uppercase tracking-wide">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Homework Submit Console
                            </h5>
                            <div class="space-y-1">
                                <span class="text-[11px] text-slate-400 block font-bold">Selected Assignment:</span>
                                <strong class="text-xs text-indigo-700 font-extrabold block" id="selected-assignment-title">Select homework task left</strong>
                            </div>
                            <form id="homework-form" class="space-y-3">
                                <div class="relative">
                                    <label class="text-[10px] text-slate-500 font-bold block mb-1">Your Answers Summary:</label>
                                    <textarea required rows="4" id="homework-answer" class="w-full p-3 text-xs bg-white border border-slate-250 rounded-xl focus:border-indigo-600 outline-none font-semibold" placeholder="Paste or write your homework answers essay here..."></textarea>
                                    <div class="absolute right-2 top-7 z-10 flex items-center voice-btn-container" data-input="homework-answer"></div>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] text-slate-500 font-bold block">Optional File attachment:</label>
                                    <input type="file" class="text-[11px] font-semibold text-slate-500 bg-white p-2 border border-slate-200 rounded-lg w-full" />
                                </div>
                                <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-indigo-650 to-indigo-800 text-white hover:from-indigo-700 hover:to-indigo-900 font-extrabold text-xs uppercase tracking-wider rounded-xl transition shadow-md shadow-indigo-100 cursor-pointer border-none">Submit Assignment</button>
                            </form>
                            <div id="homework-status" class="hidden text-xs p-2.5 bg-emerald-50 text-emerald-800 rounded-lg border border-emerald-150 font-bold"></div>
                        </div>
                    </div>
                </div>

                <!-- Join Assigned Exam -->
                <div class="p-6 bg-gradient-to-r from-violet-600 to-indigo-700 text-white rounded-3xl shadow-lg space-y-4">
                    <div>
                        <h3 class="text-base font-extrabold font-sans">Join Assigned CBT Exam</h3>
                        <p class="text-xs text-indigo-100 font-medium">Have an active exam link or code? Paste it here or enter the code to go straight to the exam.</p>
                    </div>
                    <form id="join-exam-form" class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-grow flex items-center">
                            <input type="text" id="join-exam-input" required placeholder="Paste exam link here or insert Code" class="w-full bg-white/10 backdrop-blur-md border border-white/20 text-white placeholder-white/60 rounded-xl py-3 pl-4 pr-12 text-xs focus:outline-none focus:ring-2 focus:ring-cyan-300 transition" />
                            <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center voice-btn-container" data-input="join-exam-input"></div>
                        </div>
                        <button type="submit" class="bg-cyan-400 hover:bg-cyan-500 text-slate-900 font-extrabold px-6 py-3 rounded-xl text-xs transition shadow-md whitespace-nowrap cursor-pointer border-none">Start CBT Exam</button>
                    </form>
                    <div id="join-exam-error" class="hidden text-xs bg-rose-500/20 text-rose-200 p-2.5 rounded-lg border border-rose-500/30 font-medium"></div>
                </div>

                <!-- Active Exams -->
                <div class="p-6 bg-white border border-slate-150 rounded-3xl shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Active Published CBT Exams</h3>
                            <p class="text-xs text-slate-500">Select any ongoing test to enter into the timed testing block.</p>
                        </div>
                        <div class="relative flex items-center w-full sm:w-64">
                            <input type="text" id="exam-search" placeholder="Search exam..." class="bg-slate-50 border border-slate-200 rounded-xl py-2 pl-3 pr-10 text-xs w-full focus:outline-none focus:border-indigo-600" />
                            <div class="absolute right-1.5 top-1/2 -translate-y-1/2 flex items-center voice-btn-container" data-input="exam-search"></div>
                        </div>
                    </div>
                    <div id="exams-grid" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
                    <div id="exams-empty-state" class="hidden p-12 text-center text-slate-400 space-y-3">
                        <svg class="w-10 h-10 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                        <p class="text-xs font-bold">No timed exams published matching search criteria.</p>
                        <p class="text-[10px] text-slate-400 max-w-sm mx-auto font-medium">Ask your educator to publish standard CBT links using the School dashboard.</p>
                    </div>
                </div>
            </div>

            <!-- TAB: LESSON NOTES -->
            <div id="tab-lesson_notes" class="tab-content hidden space-y-6">
                <div class="flex items-end justify-between border-b border-slate-100 pb-2">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Subject Lesson Notes</h1>
                        <p class="text-xs text-slate-500 font-medium">Browse or generate detailed lesson notes by subject.</p>
                    </div>
                </div>
                <div id="lesson-notes-content">
                    <div class="bg-gradient-to-br from-indigo-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 border border-slate-800 space-y-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <h3 class="text-lg font-black">Generate Lesson Note</h3>
                        </div>
                        <form id="student-note-form" class="space-y-4 text-slate-900">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-bold text-indigo-200 block mb-1.5">Subject</label>
                                    <select id="student-note-subject" required class="bg-white border-none rounded-xl py-3 px-3 text-sm w-full font-semibold focus:outline-none"></select>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-indigo-200 block mb-1.5">Class</label>
                                    <select id="student-note-class" required class="bg-white border-none rounded-xl py-3 px-3 text-sm w-full font-semibold focus:outline-none">
                                        <option value="Primary 1">Primary 1</option>
                                        <option value="Primary 2">Primary 2</option>
                                        <option value="Primary 3">Primary 3</option>
                                        <option value="Primary 4">Primary 4</option>
                                        <option value="Primary 5">Primary 5</option>
                                        <option value="Primary 6">Primary 6</option>
                                        <option value="JSS1">JSS1</option>
                                        <option value="JSS2">JSS2</option>
                                        <option value="JSS3">JSS3</option>
                                        <option value="SS1" selected>SS1</option>
                                        <option value="SS2">SS2</option>
                                        <option value="SS3">SS3</option>
                                    </select>
                                </div>
                            </div>
                            <hr class="border-indigo-800/40">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="relative">
                                    <label class="text-xs font-bold text-indigo-200 block mb-1.5">Topic</label>
                                    <input type="text" id="student-note-topic" required placeholder="e.g. Algebra, Photosynthesis" class="bg-white border-none rounded-xl py-3 px-3 text-sm w-full focus:outline-none" />
                                    <div class="absolute right-2 top-8 z-10 flex items-center voice-btn-container" data-input="student-note-topic"></div>
                                </div>
                                <div class="relative">
                                    <label class="text-xs font-bold text-indigo-200 block mb-1.5">Sub-topic (Optional)</label>
                                    <input type="text" id="student-note-subtopic" placeholder="e.g., Quadratic Equations" class="bg-white border-none rounded-xl py-3 px-3 text-sm w-full focus:outline-none" />
                                    <div class="absolute right-2 top-8 z-10 flex items-center voice-btn-container" data-input="student-note-subtopic"></div>
                                </div>
                            </div>
                            <div class="relative">
                                <label class="text-xs font-bold text-indigo-200 block mb-1">Sub-topics (optional — one per line)</label>
                                <textarea id="student-note-subtopics" rows="3" placeholder="e.g.&#10;Definition and types of algebra&#10;Algebraic expressions&#10;Solving linear equations" class="bg-white border-none rounded-xl py-2.5 px-3 text-xs w-full focus:outline-none resize-none"></textarea>
                                <div class="absolute right-2 top-7 z-10 flex items-center voice-btn-container" data-input="student-note-subtopics"></div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-bold text-indigo-200 block mb-1">Difficulty</label>
                                    <select id="student-note-difficulty" class="bg-white border-none rounded-xl py-2.5 px-3 text-xs w-full font-semibold focus:outline-none">
                                        <option value="Easy">Simple</option>
                                        <option value="Medium" selected>Standard</option>
                                        <option value="Hard">Deep</option>
                                    </select>
                                </div>
                                <button type="submit" class="mt-1 py-3 bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 text-white hover:from-indigo-700 hover:to-indigo-800 font-extrabold text-sm rounded-xl transition shadow-lg cursor-pointer border-none">Generate Lesson Note</button>
                            </div>
                        </form>
                    </div>
                    <div id="student-note-result" class="hidden mt-4"></div>
                    <div class="bg-white p-4 rounded-3xl border border-slate-150 shadow-sm space-y-3 mt-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-700">Browse Existing Notes</h2>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 overflow-x-auto pb-2" id="subjects-scroll"></div>
                    </div>
                    <div id="notes-list" class="mt-4 space-y-3"></div>
                    <div id="note-viewer" class="hidden mt-4"></div>
                </div>
            </div>

            <!-- TAB: REPORT CARD -->
            <div id="tab-report_card" class="tab-content hidden space-y-6">
                <div class="flex items-end justify-between border-b border-slate-100 pb-2">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">My Terminal Report Card</h1>
                        <p class="text-xs text-slate-500 font-medium">View your official terminal results, cognitive rankings, and printed progress sheets.</p>
                    </div>
                </div>
                <div id="report-card-content">
                    <div class="bg-white border border-slate-150 rounded-3xl p-6 space-y-4">
                        <p class="text-xs text-slate-400">Loading report card data...</p>
                    </div>
                </div>
            </div>

            <!-- TAB: RESULTS -->
            <div id="tab-results" class="tab-content hidden space-y-6">
                <div class="flex items-end justify-between border-b border-slate-100 pb-2">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Scores & Reports</h1>
                        <p class="text-xs text-slate-500 font-medium">Track performance grades, subject progressions, and print certificates.</p>
                    </div>
                </div>
                <div id="results-content">
                    <div class="bg-white border border-slate-150 rounded-3xl p-6 space-y-4">
                        <p class="text-xs text-slate-400">Loading results...</p>
                    </div>
                </div>
            </div>

            <!-- TAB: PRACTICE -->
            <div id="tab-practice" class="tab-content hidden space-y-6">
                <div class="flex items-end justify-between border-b border-slate-100 pb-2">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Study Revision</h1>
                        <p class="text-xs text-slate-500 font-medium">Generate custom revision drills for self-study.</p>
                    </div>
                </div>
                <div id="practice-content">
                    <div class="p-8 bg-gradient-to-br from-indigo-900 via-indigo-950 to-slate-900 text-white rounded-3xl shadow-xl space-y-6 border border-slate-800">
                        <div class="flex items-center gap-2">
                            <svg class="w-6 h-6 text-cyan-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <h3 class="text-xl font-black">Study Revision Drill</h3>
                        </div>
                        <form id="practice-form" class="space-y-4 pt-4 text-slate-900">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-bold text-indigo-200 block mb-1.5">Subject</label>
                                    <select id="practice-subject" class="bg-white border-none rounded-xl py-3 px-3 text-sm w-full font-semibold focus:outline-none"></select>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-indigo-200 block mb-1.5">Class</label>
                                    <select id="practice-class" class="bg-white border-none rounded-xl py-3 px-3 text-sm w-full font-semibold focus:outline-none">
                                        <option value="Primary 1">Primary 1</option>
                                        <option value="Primary 2">Primary 2</option>
                                        <option value="Primary 3">Primary 3</option>
                                        <option value="Primary 4">Primary 4</option>
                                        <option value="Primary 5">Primary 5</option>
                                        <option value="Primary 6">Primary 6</option>
                                        <option value="JSS1">JSS1</option>
                                        <option value="JSS2">JSS2</option>
                                        <option value="JSS3">JSS3</option>
                                        <option value="SS1" selected>SS1</option>
                                        <option value="SS2">SS2</option>
                                        <option value="SS3">SS3</option>
                                    </select>
                                </div>
                            </div>
                            <hr class="border-indigo-800/40">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="relative">
                                    <label class="text-xs font-bold text-indigo-200 block mb-1.5">Topic</label>
                                    <input required type="text" id="practice-topic" placeholder="e.g. 'Algebra', 'Calculus'" class="bg-white border-none rounded-xl py-3 px-3 text-sm w-full focus:outline-none" />
                                    <div class="absolute right-2 top-8 z-10 flex items-center voice-btn-container" data-input="practice-topic"></div>
                                </div>
                                <div class="relative">
                                    <label class="text-xs font-bold text-indigo-200 block mb-1.5">Sub-topic (Optional)</label>
                                    <input type="text" id="practice-subtopic" placeholder="e.g., Solving by Substitution" class="bg-white border-none rounded-xl py-3 px-3 text-sm w-full focus:outline-none" />
                                    <div class="absolute right-2 top-8 z-10 flex items-center voice-btn-container" data-input="practice-subtopic"></div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-bold text-indigo-200 block mb-1">Questions count</label>
                                    <select id="practice-count" class="bg-white border-none rounded-xl py-3 px-3 text-sm w-full font-semibold focus:outline-none">
                                        <option value="10">10 Standard questions</option>
                                        <option value="20">20 Detailed questions</option>
                                        <option value="30">30 Intensive questions</option>
                                        <option value="50">50 Exam simulation</option>
                                        <option value="100">100 Full exam</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs font-bold text-indigo-200 block mb-1">Time limit</label>
                                    <select id="practice-time" class="bg-white border-none rounded-xl py-3 px-3 text-sm w-full font-semibold focus:outline-none">
                                        <option value="0">No limit</option>
                                        <option value="5">5 minutes</option>
                                        <option value="10" selected>10 minutes</option>
                                        <option value="15">15 minutes</option>
                                        <option value="30">30 minutes</option>
                                        <option value="60">1 hour</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="py-3 bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 text-white hover:from-indigo-700 hover:to-indigo-800 font-extrabold text-sm rounded-xl transition shadow-lg cursor-pointer border-none">Generate Revision Questions</button>
                        </form>
                    </div>
                    <div id="practice-questions" class="hidden mt-6"></div>
                </div>
            </div>

            <!-- TAB: LIBRARY -->
            <div id="tab-library" class="tab-content hidden space-y-6">
                <div class="flex items-end justify-between border-b border-slate-100 pb-2">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">My Personal Library</h1>
                        <p class="text-xs text-slate-500 font-medium">Persistent database of all your class notes, worksheets, and resources.</p>
                    </div>
                </div>
                <div id="library-content">
                    <div class="bg-white border border-slate-150 rounded-3xl p-6 space-y-4">
                        <p class="text-xs text-slate-400">Loading library...</p>
                    </div>
                </div>
            </div>

            <!-- TAB: NOTIFICATIONS -->
            <div id="tab-notifications" class="tab-content hidden space-y-6">
                <div class="flex items-end justify-between border-b border-slate-100 pb-2">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Notifications</h1>
                        <p class="text-xs text-slate-500 font-medium">Check updates, invitations, and alerts from your educators.</p>
                    </div>
                </div>
                <div id="notifications-content">
                    <div class="bg-white border border-slate-150 rounded-3xl p-6 space-y-4">
                        <p class="text-xs text-slate-400">Loading notifications...</p>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
// ============================================================
// Student Dashboard JavaScript
// ============================================================
const STUDENT_USER = @json(session('user'));

// Tab switching
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('bg-indigo-800', 'text-white');
        el.classList.add('text-indigo-300', 'hover:bg-indigo-800', 'hover:text-white');
        el.querySelector('svg')?.classList.remove('text-cyan-400');
    });
    const target = document.getElementById('tab-' + tabId);
    if (target) target.classList.remove('hidden');
    const btn = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
    if (btn) {
        btn.classList.add('bg-indigo-800', 'text-white');
        btn.classList.remove('text-indigo-300', 'hover:bg-indigo-800', 'hover:text-white');
        const svg = btn.querySelector('svg');
        if (svg) svg.classList.add('text-cyan-400');
    }
}

// Learning tab switching
function switchLearningTab(tabId) {
    document.querySelectorAll('.ltab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.ltab-btn').forEach(el => {
        el.classList.remove('bg-white', 'text-indigo-950', 'shadow-xs');
        el.classList.add('text-slate-500', 'hover:text-slate-900');
    });
    const target = document.getElementById('ltab-' + tabId);
    if (target) target.classList.remove('hidden');
    const btn = document.querySelector(`.ltab-btn[data-ltab="${tabId}"]`);
    if (btn) {
        btn.classList.add('bg-white', 'text-indigo-950', 'shadow-xs');
        btn.classList.remove('text-slate-500', 'hover:text-slate-900');
    }
}

// Voice input
function createVoiceInput(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const container = document.querySelector(`[data-input="${inputId}"]`);
    if (!container) return;
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SR) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>';
        btn.className = 'inline-flex items-center justify-center opacity-60 text-slate-500 cursor-help p-1 rounded-md bg-slate-200';
        btn.title = 'Speech not supported';
        container.appendChild(btn);
        return;
    }
    let recognition = null, listening = false;
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>';
    btn.className = 'inline-flex items-center justify-center transition-all cursor-pointer border-none shrink-0 bg-slate-200 text-slate-700 hover:bg-slate-300 p-1 rounded-md';
    btn.title = 'Use speech-to-text';
    btn.onclick = function(e) {
        e.preventDefault(); e.stopPropagation();
        if (listening) { try { recognition.stop(); } catch(e) {} listening = false; resetBtn(); return; }
        if (!recognition) {
            recognition = new SR();
            recognition.continuous = false; recognition.interimResults = false; recognition.lang = 'en-US';
            recognition.onend = function() { listening = false; resetBtn(); };
            recognition.onresult = function(ev) {
                const t = ev.results[0][0].transcript;
                if (t) { const cur = input.value.trim(); input.value = cur ? cur + ' ' + t : t; input.dispatchEvent(new Event('input')); }
            };
            recognition.onerror = function() { listening = false; resetBtn(); };
        }
        try { recognition.start(); listening = true; btn.className = 'inline-flex items-center justify-center transition-all cursor-pointer border-none shrink-0 bg-rose-500 text-white animate-pulse p-1 rounded-md'; btn.innerHTML = '<span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span><svg class="w-3.5 h-3.5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg></span>'; } catch(e) {}
    };
    function resetBtn() { btn.className = 'inline-flex items-center justify-center transition-all cursor-pointer border-none shrink-0 bg-slate-200 text-slate-700 hover:bg-slate-300 p-1 rounded-md'; btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>'; }
    container.appendChild(btn);
}

// Data state
let state = { exams: [], results: [], notifications: [], notes: [], subjects: [], reportSheets: [] };

function escapeHtml(t) { if (!t) return ''; var d = document.createElement('div'); d.appendChild(document.createTextNode(t)); return d.innerHTML; }

// Load all data
async function loadData() {
    try {
        const [exRes, resRes, notifRes, notesRes, subRes, reportRes] = await Promise.all([
            fetch('/api/exams').then(r => r.json()).catch(() => ({ exams: [] })),
            fetch('/api/results').then(r => r.json()).catch(() => ({ results: [] })),
            (STUDENT_USER?.id ? fetch('/api/notifications/user/' + STUDENT_USER.id).then(r => r.json()) : Promise.resolve({ notifications: [] })).catch(() => ({ notifications: [] })),
            fetch('/api/lesson-notes').then(r => r.json()).catch(() => ({ lessonNotes: [] })),
            fetch('/api/subjects').then(r => r.json()).catch(() => ({ subjects: [] })),
            fetch('/api/report-sheets').then(r => r.json()).catch(() => ({ reportSheets: [] })),
        ]);
        state.exams = exRes.exams || [];
        state.results = resRes.results || [];
        state.notifications = notifRes.notifications || [];
        state.notes = notesRes.lessonNotes || [];
        state.subjects = (subRes.subjects && subRes.subjects.length) ? subRes.subjects : defaultSubjects();
        state.reportSheets = reportRes.reportSheets || [];
    } catch(e) { console.error('Data load error:', e); }
    if (!state.subjects || !state.subjects.length) {
        state.subjects = defaultSubjects();
    }
    renderAll();
}

function renderAll() {
    populateSubjectSelects();
    renderExams();
    renderResults();
    renderNotifications();
    renderNotes();
    renderSubjects();
    renderPractice();
    renderReportCards();
    renderSidebarStats();
}

// Render Exams
function renderExams() {
    const grid = document.getElementById('exams-grid');
    const empty = document.getElementById('exams-empty-state');
    const published = state.exams.filter(e => e.isPublished);
    if (published.length === 0) { grid.innerHTML = ''; empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');
    grid.innerHTML = published.map(ex => `
        <div class="p-5 bg-white border border-slate-100 rounded-3xl space-y-4 hover:border-indigo-300 transition hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="text-[10px] bg-indigo-50 text-indigo-700 py-0.5 px-2.5 rounded-full font-bold uppercase border border-indigo-100">${escapeHtml(ex.subject)}</span>
                <span class="text-xs text-slate-400 font-mono font-bold">⏱ ${ex.duration} Min</span>
            </div>
            <div>
                <h4 class="text-sm font-black text-slate-800 line-clamp-1">${escapeHtml(ex.title)}</h4>
                <p class="text-[10px] text-slate-400 font-bold tracking-wide uppercase mt-0.5">Author: ${escapeHtml(ex.creatorName)}</p>
            </div>
            <div class="flex items-center justify-between text-xs font-semibold pt-1">
                <span class="text-slate-500">${ex.questions?.length || 0} questions</span>
                <a href="/student/exam/${ex.id}" class="flex items-center gap-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition shadow-md cursor-pointer">Join Timed Exam <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
            </div>
        </div>
    `).join('');
}

// Render Results
function renderResults() {
    const container = document.getElementById('results-content');
    const results = state.results;
    if (results.length === 0) {
        container.innerHTML = '<div class="bg-white border border-slate-150 rounded-3xl p-12 text-center text-slate-400 space-y-2"><svg class="w-8 h-8 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg><p class="text-xs font-bold">No exams completed yet.</p></div>';
        return;
    }
    const avg = Math.round(results.reduce((s, r) => s + r.percentage, 0) / results.length);
    container.innerHTML = `
        <div class="p-6 bg-white border border-slate-150 rounded-3xl shadow-sm space-y-4">
            <div class="flex justify-between items-center">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Grading & Score History</h4>
                <span class="text-sm font-black text-indigo-600">Average: ${avg}%</span>
            </div>
            <div class="space-y-3">
                ${results.map(r => `
                    <div class="p-4 bg-white border border-slate-100 rounded-3xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:border-indigo-200 transition">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] bg-slate-100 text-slate-700 py-0.5 px-2 rounded-full font-bold">${escapeHtml(r.subject)}</span>
                                <span class="text-[10px] text-slate-400 font-bold">${new Date(r.date).toLocaleDateString()}</span>
                            </div>
                            <h4 class="text-xs font-black text-slate-800">${escapeHtml(r.examTitle)}</h4>
                            <p class="text-[10px] text-slate-400 font-semibold">Correct: ${r.correctAnswers}/${r.totalQuestions} questions</p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <div class="text-right">
                                <p class="text-base font-black text-slate-900">${r.percentage}%</p>
                                <p class="text-[10px] font-bold ${r.percentage >= 50 ? 'text-indigo-600' : 'text-rose-500'}">${r.percentage >= 50 ? 'Passed' : 'Retake'}</p>
                            </div>
                            <button onclick="window.open('/api/download/graded-script/${r.examId}/${r.id}', '_blank')" class="px-3.5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition font-black text-xs flex items-center gap-1.5 cursor-pointer shadow-xs border-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Script
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

// Render Notifications
function renderNotifications() {
    const container = document.getElementById('notifications-content');
    const notifs = state.notifications;
    const unread = notifs.filter(n => !n.read).length;
    const badge = document.getElementById('notif-badge');
    if (unread > 0) { badge.classList.remove('hidden'); badge.textContent = unread; } else { badge.classList.add('hidden'); }
    if (notifs.length === 0) {
        container.innerHTML = '<div class="bg-white border border-slate-150 rounded-3xl p-12 text-center text-slate-400 space-y-2"><svg class="w-8 h-8 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg><p class="text-xs font-bold">No notifications yet.</p></div>';
        return;
    }
    container.innerHTML = `
        <div class="bg-white border border-slate-150 rounded-3xl p-6 space-y-4">
            <h3 class="text-sm font-black text-slate-900">Notifications (${notifs.length})</h3>
            <div class="space-y-2">
                ${notifs.map(n => `
                    <div class="p-4 ${n.read ? 'bg-white' : 'bg-indigo-50'} border border-slate-150 rounded-2xl flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <h5 class="text-xs font-bold text-slate-900">${escapeHtml(n.title)}</h5>
                            <p class="text-[11px] text-slate-600">${escapeHtml(n.message)}</p>
                            <span class="text-[10px] text-slate-400">${new Date(n.date).toLocaleDateString()}</span>
                        </div>
                        ${!n.read ? `<button onclick="markNotifRead('${n.id}')" class="px-2 py-1 bg-indigo-600 text-white text-[10px] rounded-lg font-bold cursor-pointer border-none hover:bg-indigo-700">Mark Read</button>` : ''}
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

async function markNotifRead(id) {
    try { await fetch('/api/notifications/' + id + '/read', { method: 'POST' }); state.notifications = state.notifications.map(n => n.id === id ? { ...n, read: true } : n); renderNotifications(); } catch(e) {}
}

// Render Lesson Notes subjects
function renderSubjects() {
    const scroll = document.getElementById('subjects-scroll');
    const subs = state.subjects && state.subjects.length ? state.subjects : defaultSubjects();
    scroll.innerHTML = subs.map(s => `<button onclick="selectSubject('${s}')" class="subj-btn py-2 px-4 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer border bg-blue-50 text-blue-700 hover:bg-blue-100 border-blue-200" data-subj="${s}">${s}</button>`).join('');
}

function renderNotes() {
    populateSubjectSelects();
    const list = document.getElementById('notes-list');
    const filtered = state.notes.filter(n => n.subject === (state.selectedSubject || 'Mathematics'));
    if (filtered.length === 0) {
        list.innerHTML = '<div class="p-6 text-center text-slate-400 text-xs">No lesson notes found for this subject.</div>';
        return;
    }
    list.innerHTML = filtered.map(n => `
        <div onclick="viewNote('${n.id}')" class="p-4 bg-white border border-slate-150 rounded-2xl hover:shadow-sm cursor-pointer transition">
            <div class="flex items-center justify-between">
                <span class="text-[10px] bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded font-bold">${escapeHtml(n.subject)}</span>
                <span class="text-[10px] text-slate-400">${new Date(n.createdAt).toLocaleDateString()}</span>
            </div>
            <h4 class="text-sm font-bold text-slate-800 mt-1">${escapeHtml(n.topic)}</h4>
            <p class="text-xs text-slate-500 mt-0.5">${escapeHtml(n.subTopic || '')}</p>
        </div>
    `).join('');
}

function selectSubject(subj) {
    state.selectedSubject = subj;
    document.querySelectorAll('.subj-btn').forEach(b => {
        b.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-700');
        b.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-200');
        if (b.dataset.subj === subj) {
            b.classList.add('bg-indigo-600', 'text-white', 'border-indigo-700');
            b.classList.remove('bg-blue-50', 'text-blue-700', 'border-blue-200');
        }
    });
    renderNotes();
}

function viewNote(id) {
    const n = state.notes.find(n => n.id === id);
    if (!n) return;
    const viewer = document.getElementById('note-viewer');
    viewer.classList.remove('hidden');
    viewer.innerHTML = `
        <div class="bg-white border border-slate-150 rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between border-b pb-4">
                <div>
                    <span class="text-[10px] bg-indigo-50 text-indigo-700 px-3 py-1 rounded-lg font-black uppercase">${escapeHtml(n.subject)}</span>
                    <h3 class="text-xl font-black text-slate-900 mt-1">${escapeHtml(n.topic)}</h3>
                </div>
                <button onclick="document.getElementById('note-viewer').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold cursor-pointer border-none">Back</button>
            </div>
            <div class="whitespace-pre-wrap text-sm leading-relaxed text-slate-800 p-5 bg-slate-50 rounded-2xl border border-slate-150">${renderMath(n.content?.detailedNote || n.content?.explanation || 'No content')}</div>
        </div>
    `;
}

// Student generate lesson note
document.getElementById('student-note-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const subj = document.getElementById('student-note-subject').value;
    const cls = document.getElementById('student-note-class').value;
    const topic = document.getElementById('student-note-topic').value;
    const subtopics = document.getElementById('student-note-subtopics').value.trim();
    const difficulty = document.getElementById('student-note-difficulty').value;
    const container = document.getElementById('student-note-result');
    container.classList.remove('hidden');
    container.innerHTML = '<div class="p-8 text-center"><div class="animate-spin w-8 h-8 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto"></div><p class="text-xs text-slate-400 mt-3 font-bold">Generating lesson note...</p></div>';
    btn.disabled = true;
    btn.textContent = 'Generating...';
    try {
        const body = { subject: subj, topic, class: cls, subTopic: document.getElementById('student-note-subtopic').value, difficulty };
        if (subtopics) body.subtopics = subtopics;
        const res = await fetch('/api/ai/lesson-note', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(body)
        });
        const data = await res.json();
        if (data.success) {
            renderStudentNote(data.note);
            loadData();
        } else {
            container.innerHTML = '<div class="p-6 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-700 font-bold">' + (data.error || 'Failed to generate lesson note.') + '</div>';
        }
    } catch(e) {
        container.innerHTML = '<div class="p-6 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-700 font-bold">Connection error.</div>';
    }
    btn.disabled = false;
    btn.textContent = 'Generate Lesson Note';
});

function renderStudentNote(note) {
    const container = document.getElementById('student-note-result');
    const evaluationRaw = note.evaluationQuestions || note.evaluation || [];
    const evaluation = Array.isArray(evaluationRaw) ? evaluationRaw : [evaluationRaw];
    const definitions = note.definitions || [];
    const keyPoints = note.keyPoints || note.key_points || [];
    const sections = note.sections || [];
    const noteContent = note.content || note.detailedNote || note.body || note.noteContent || note.htmlContent || note.lessonContent || note.lesson_content || note.definition || '';

    let definitionsHtml = definitions.length ? `<div class="mt-4"><h3 class="text-base font-bold text-slate-800 mb-2">Definitions of Key Terms</h3><table class="w-full text-sm border-collapse">${definitions.map(d => `<tr class="border-b border-slate-200"><td class="py-2 pr-3 font-semibold text-indigo-700 w-1/3">${escapeHtml(d.term || '')}</td><td class="py-2 text-slate-600">${escapeHtml(d.definition || '')}</td></tr>`).join('')}</table></div>` : '';
    let keyPointsHtml = keyPoints.length ? `<div class="mt-4 p-4 bg-indigo-50 border border-indigo-200 rounded-xl"><h3 class="text-sm font-bold text-indigo-800 mb-2">Key Points to Remember</h3><ul class="text-sm space-y-1 list-disc pl-5 text-indigo-700">${keyPoints.map(k => `<li>${renderMath(k)}</li>`).join('')}</ul></div>` : '';

    let extraHtml = '';
    sections.forEach(sec => {
        if (sec.heading && sec.content) {
            extraHtml += `<div class="mt-6"><h3 class="text-base font-bold text-slate-800 mb-3">${escapeHtml(sec.heading)}</h3><div class="text-sm">${sec.content}</div></div>`;
        }
    });

    container.innerHTML = `
        <div class="bg-white border border-slate-150 rounded-3xl p-6 sm:p-8 space-y-6">
            <div class="text-center border-b-2 border-indigo-200 pb-4 mb-2">
                <span class="text-[10px] bg-indigo-50 text-indigo-700 px-3 py-1 rounded-lg font-black uppercase">${escapeHtml(note.subject)}</span>
                <h2 class="text-xl font-black text-slate-900 mt-2">${escapeHtml(note.topic)}</h2>
                <p class="text-xs text-slate-400 mt-1">${escapeHtml(note.class || '')}${note.class ? ' | ' : ''}${escapeHtml(note.term || '')}${note.term ? ' | ' : ''}Week ${note.week || ''}</p>
            </div>
            ${noteContent}
            ${definitionsHtml}
            ${extraHtml}
            ${evaluation.length ? `<div class="mt-6"><h3 class="text-base font-bold text-slate-800 mb-3">Evaluation Questions</h3><ol class="text-sm pl-5 space-y-1 text-slate-700 list-decimal">${evaluation.map(eq => `<li>${renderMath(eq)}</li>`).join('')}</ol></div>` : ''}
            ${note.assignment ? `<div class="mt-6"><h3 class="text-base font-bold text-slate-800 mb-2">Assignment</h3><div class="text-sm whitespace-pre-wrap text-slate-700 p-4 bg-slate-50 rounded-2xl border">${renderMath(note.assignment)}</div></div>` : ''}
            ${keyPointsHtml}
        </div>
    `;
}

// Full Nigerian curriculum subjects
function defaultSubjects() {
    return ['Mathematics','English Language','Physics','Chemistry','Biology','Agricultural Science','Economics','Government','Civic Education','Literature in English','Commerce','Accounting','Computer Studies/ICT','Geography','History','Home Economics','Christian Religious Studies','Islamic Studies','Social Studies','Social and Citizenship Education','Basic Science','Basic Technology','Physical & Health Education','Business Studies','French','Fine Arts/Creative Arts','Music','Yoruba','Hausa','Igbo','Further Mathematics','Intermediate Science','Cultural and Creative Arts (CCA)','Nigerian History','Artificial Intelligence','Phonics','Vocational Studies','Physical and Health Education (PHE)','Coding','Verbal Reasoning','Craft and Art'];
}

// Populate subject dropdowns
function populateSubjectSelects() {
    const subs = (state.subjects && state.subjects.length) ? state.subjects : defaultSubjects();
    const options = subs.map(s => `<option value="${s}">${s}</option>`).join('');
    const practiceSel = document.getElementById('practice-subject');
    if (practiceSel && !practiceSel.options.length) practiceSel.innerHTML = options;
    const noteSel = document.getElementById('student-note-subject');
    if (noteSel && !noteSel.options.length) noteSel.innerHTML = options;
}

// Render Practice form
function renderPractice() {
    populateSubjectSelects();
}

// Render Report Cards
function renderReportCards() {
    const container = document.getElementById('report-card-content');
    const sheets = state.reportSheets.filter(r => r.studentName?.trim().toLowerCase() === (STUDENT_USER?.name || '').trim().toLowerCase());
    if (sheets.length === 0) {
        container.innerHTML = '<div class="bg-white border border-slate-150 rounded-3xl p-12 text-center text-slate-400"><p class="text-xs font-bold">No report card data found.</p></div>';
        return;
    }
    container.innerHTML = sheets.map(s => `
        <div class="bg-white border border-slate-150 rounded-3xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-lg font-black text-slate-900">${s.term} Report</h3>
                <span class="text-sm font-bold text-indigo-600">Avg: ${s.studentAverage}%</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-semibold">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 font-bold text-[9px] uppercase">
                            <th class="py-2">Subject</th>
                            <th class="py-2">CA1 (20)</th>
                            <th class="py-2">CA2 (20)</th>
                            <th class="py-2">Exam (60)</th>
                            <th class="py-2">Total</th>
                            <th class="py-2">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${Object.entries(s.scores || {}).map(([sub, d]) => `
                            <tr class="border-b border-slate-100">
                                <td class="py-2.5 font-bold text-slate-800">${sub}</td>
                                <td class="py-2.5 font-mono">${d.ca1}</td>
                                <td class="py-2.5 font-mono">${d.ca2}</td>
                                <td class="py-2.5 font-mono">${d.exam}</td>
                                <td class="py-2.5 font-mono font-bold text-slate-900">${d.total}</td>
                                <td class="py-2.5"><span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${d.total >= 75 ? 'bg-emerald-50 text-emerald-700' : d.total >= 50 ? 'bg-indigo-50 text-indigo-700' : 'bg-rose-50 text-rose-700'}">${d.grade}</span></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            ${s.teacherRemark ? `<div class="p-3 bg-indigo-50 rounded-xl text-xs"><strong>Teacher:</strong> "${escapeHtml(s.teacherRemark)}"</div>` : ''}
            ${s.principalRemark ? `<div class="p-3 bg-slate-50 rounded-xl text-xs"><strong>Principal:</strong> "${escapeHtml(s.principalRemark)}"</div>` : ''}
        </div>
    `).join('');
}

// Sidebar stats
function renderSidebarStats() {
    const results = state.results;
    if (results.length > 0) {
        const passed = results.filter(r => r.percentage >= 50).length;
        const ratio = Math.round((passed / results.length) * 100);
        const avg = Math.round(results.reduce((s, r) => s + r.percentage, 0) / results.length);
        document.getElementById('pass-ratio').textContent = ratio + '%';
        document.getElementById('avg-grade').textContent = 'Average Grade: ' + avg + '%';
        document.getElementById('avg-compilation').textContent = avg + '%';
    }
}

// Join exam form
document.getElementById('join-exam-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const input = document.getElementById('join-exam-input').value.trim();
    const errEl = document.getElementById('join-exam-error');
    errEl.classList.add('hidden');
    if (!input) return;
    let examId = input;
    if (input.includes('examId=')) { const p = input.split('examId='); if (p.length > 1) examId = p[1].split('&')[0]; }
    else if (input.includes('#/exam/')) { const p = input.split('#/exam/'); if (p.length > 1) examId = p[1]; }
    const match = state.exams.find(e => e.id.toLowerCase() === examId.toLowerCase() || e.title.toLowerCase().includes(examId.toLowerCase()));
    if (match) { window.location.href = '/student/exam/' + match.id; }
    else { errEl.textContent = '⚠ Exam not found.'; errEl.classList.remove('hidden'); }
});

// Practice form
document.getElementById('practice-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const subj = document.getElementById('practice-subject').value;
    const topic = document.getElementById('practice-topic').value;
    const count = parseInt(document.getElementById('practice-count').value);
    const container = document.getElementById('practice-questions');
    container.classList.remove('hidden');
    container.innerHTML = '<div class="p-6 text-center text-slate-400"><div class="animate-spin w-6 h-6 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto"></div><p class="text-xs mt-2">Generating questions...</p></div>';
    try {
        const timeLimit = parseInt(document.getElementById('practice-time').value) || 0;
        const res = await fetch('/api/ai/generate-questions', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ subject: subj, topic, subTopic: document.getElementById('practice-subtopic').value, class: document.getElementById('practice-class').value, count, difficulty: 'Medium' })
        });
        const data = await res.json();
        if (res.ok && data.success && data.questions) {
            container.innerHTML = renderPracticeQuiz(data.questions, timeLimit);
        } else { container.innerHTML = '<div class="p-6 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-700 font-bold">' + (data.error || 'Failed to generate questions.') + '</div>'; }
    } catch(e) { container.innerHTML = '<div class="p-6 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-700 font-bold">Connection error.</div>'; }
});

function renderPracticeQuiz(questions, timeLimitMinutes) {
    let idx = 0, answers = {}, completed = false, score = 0;
    let timeLeft = timeLimitMinutes > 0 ? timeLimitMinutes * 60 : 0;
    let timerInterval = null;

    function finishQuiz() {
        if (completed) return;
        if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }
        score = questions.filter((q,i) => answers[i] === (q.correctAnswer || q.answer)).length;
        completed = true;
        document.getElementById('practice-questions').innerHTML = render();
    }

    function formatTime(secs) {
        const m = Math.floor(secs / 60);
        const s = secs % 60;
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    function render() {
        if (completed) {
            const pct = Math.round((score / questions.length) * 100);
            return `<div class="text-center py-6 space-y-4">
                <svg class="w-16 h-16 text-indigo-600 mx-auto animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h3 class="text-xl font-bold text-slate-900">Revision Complete!</h3>
                <div class="p-4 bg-slate-50 rounded-2xl max-w-xs mx-auto border"><span class="text-4xl font-extrabold text-slate-800">${score} / ${questions.length}</span><span class="text-xs block text-slate-500">(${pct}%)</span></div>
                <button onclick="document.getElementById('practice-questions').classList.add('hidden')" class="px-6 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl cursor-pointer border-none">New Revision</button>
            </div>`;
        }
        const q = questions[idx];
        const timerHtml = timeLimitMinutes > 0 ? `<span class="text-[10px] font-mono font-bold px-2.5 py-0.5 rounded-full ${timeLeft <= 60 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600'}">${formatTime(timeLeft)}</span>` : '';
        return `<div class="bg-white border border-slate-150 rounded-3xl p-6 space-y-4">
            <div class="flex justify-between items-center border-b pb-3">
                <span class="text-[10px] bg-indigo-50 text-indigo-700 px-2.5 py-0.5 rounded-full font-bold">Q ${idx+1} of ${questions.length}</span>
                ${timerHtml}
            </div>
            <p class="text-base font-extrabold text-slate-800">${renderMath(q.question)}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                ${['A','B','C','D'].map(k => `
                    <button onclick="practiceSelect('${k}')" class="p-3.5 rounded-xl border text-left font-semibold text-xs flex items-center gap-2.5 transition cursor-pointer ${answers[idx] === k ? 'bg-slate-900 text-white border-slate-900' : 'bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100'}">
                        <span class="w-6 h-6 rounded-md flex items-center justify-center font-bold font-mono border text-[11px] ${answers[idx] === k ? 'bg-white text-slate-900' : 'bg-blue-50 text-blue-700 border-blue-200'}">${k}</span>
                        ${renderMath(q['option'+k] || q[k] || (q.options && q.options[k]) || '')}
                    </button>
                `).join('')}
            </div>
            <div class="flex justify-between pt-4 border-t">
                <button onclick="practicePrev()" ${idx === 0 ? 'disabled' : ''} class="py-1.5 px-4 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition disabled:opacity-40 cursor-pointer">Previous</button>
                ${idx === questions.length - 1
                    ? `<button onclick="practiceSubmit()" class="py-1.5 px-5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition cursor-pointer">Finish & Submit</button>`
                    : `<button onclick="practiceNext()" class="py-1.5 px-4 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition cursor-pointer">Next</button>`
                }
            </div>
        </div>`;
    }

    if (timeLimitMinutes > 0) {
        timerInterval = setInterval(function() {
            timeLeft--;
            const timerEl = document.querySelector('#practice-questions .font-mono.font-bold');
            if (timerEl) {
                const m = Math.floor(timeLeft / 60);
                const s = timeLeft % 60;
                timerEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;
                if (timeLeft <= 60) timerEl.className = 'text-[10px] font-mono font-bold px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-700';
            }
            if (timeLeft <= 0) { clearInterval(timerInterval); timerInterval = null; finishQuiz(); }
        }, 1000);
    }

    window.practiceSelect = function(k) { answers[idx] = k; document.getElementById('practice-questions').innerHTML = render(); };
    window.practiceNext = function() { if (idx < questions.length - 1) { idx++; document.getElementById('practice-questions').innerHTML = render(); } };
    window.practicePrev = function() { if (idx > 0) { idx--; document.getElementById('practice-questions').innerHTML = render(); } };
    window.practiceSubmit = function() { if (timerInterval) { clearInterval(timerInterval); timerInterval = null; } score = questions.filter((q,i) => answers[i] === (q.correctAnswer || q.answer)).length; completed = true; document.getElementById('practice-questions').innerHTML = render(); };
    return render();
}

// Homework form
document.getElementById('homework-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const status = document.getElementById('homework-status');
    status.classList.remove('hidden');
    status.textContent = '✅ Submitted! Waiting for academic scoring.';
    document.getElementById('homework-answer').value = '';
});

// File upload
document.getElementById('file-upload-input')?.addEventListener('change', function() {
    if (this.files?.[0]) alert('File "' + this.files[0].name + '" uploaded successfully!');
});

// Exam search
document.getElementById('exam-search')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    const cards = document.querySelectorAll('#exams-grid > div');
    cards.forEach(c => {
        const text = c.textContent.toLowerCase();
        c.style.display = text.includes(q) ? '' : 'none';
    });
});

// ============================================================
// Init
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    createVoiceInput('join-exam-input');
    createVoiceInput('exam-search');
    createVoiceInput('practice-topic');
    createVoiceInput('practice-subtopic');
    createVoiceInput('homework-answer');
    createVoiceInput('student-note-topic');
    createVoiceInput('student-note-subtopic');
    createVoiceInput('student-note-subtopics');
    loadData();
});
</script>
@endsection