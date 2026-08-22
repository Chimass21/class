@extends('layouts.app')

@section('content')
<style>
    * { font-family: 'Inter', sans-serif; }

    .sidebar-item {
        color: #64748b;
        transition: all 0.2s;
    }
    .sidebar-item:hover {
        background: rgba(124,58,237,0.06);
        color: #7c3aed;
    }
    .sidebar-item-active {
        background: linear-gradient(135deg, rgba(124,58,237,0.1), rgba(236,72,153,0.08));
        color: #7c3aed;
        font-weight: 700;
        border: 1px solid rgba(124,58,237,0.12);
    }

    .stat-card { transition: all 0.3s; }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(124,58,237,0.12); }

    #sidebar.collapsed .nav-label { display: none; }
    #sidebar.collapsed .nav-badge { display: none; }
    #sidebar.collapsed .nav-btn { justify-content: center; padding-left: 0; padding-right: 0; }
    #sidebar.collapsed .sidebar-header h1,
    #sidebar.collapsed .sidebar-header p { display: none; }
    #sidebar.collapsed .sidebar-header { justify-content: center; }
    #sidebar.collapsed hr + a span { display: none; }
    #sidebar.collapsed hr + a { justify-content: center; }

    .input-bright {
        background: linear-gradient(135deg, #f0f4ff 0%, #fdf2f8 50%, #fefce8 100%) !important;
        border: 2px solid transparent !important;
        background-clip: padding-box !important;
        border-image: linear-gradient(135deg, #8b5cf6, #ec4899, #f59e0b) 1 !important;
        color: #1e293b !important;
        font-weight: 600 !important;
    }
    .input-bright:hover {
        box-shadow: 0 0 20px rgba(139,92,246,0.25), 0 0 40px rgba(236,72,153,0.1) !important;
        transform: translateY(-1px);
    }
    .input-bright:focus {
        border-image: linear-gradient(135deg, #a855f7, #f43f5e, #eab308) 2 !important;
        box-shadow: 0 0 0 3px rgba(139,92,246,0.3), 0 0 30px rgba(236,72,153,0.2) !important;
        outline: none !important;
    }

    select, input:not([type="hidden"]):not([type="file"]), textarea {
        background: linear-gradient(135deg, #f0f4ff 0%, #fdf2f8 50%, #fefce8 100%) !important;
        border: 2px solid transparent !important;
        background-clip: padding-box !important;
        border-image: linear-gradient(135deg, #8b5cf6, #ec4899, #f59e0b) 1 !important;
        transition: all 0.3s ease !important;
        font-weight: 600 !important;
        color: #1e293b !important;
    }
    select:hover, input:hover, textarea:hover {
        box-shadow: 0 0 20px rgba(139,92,246,0.25), 0 0 40px rgba(236,72,153,0.1) !important;
        transform: translateY(-1px);
    }
    select:focus, input:focus, textarea:focus {
        box-shadow: 0 0 0 3px rgba(139,92,246,0.3), 0 0 30px rgba(236,72,153,0.2) !important;
        border-image: linear-gradient(135deg, #a855f7, #f43f5e, #eab308) 2 !important;
        outline: none !important;
    }

    button[type="submit"], .btn-primary, .action-btn {
        background: linear-gradient(135deg, #8b5cf6, #ec4899, #f59e0b) !important;
        border: none !important;
        box-shadow: 0 4px 15px rgba(139,92,246,0.4), 0 0 30px rgba(236,72,153,0.2) !important;
        transition: all 0.3s ease !important;
        font-weight: 700 !important;
        letter-spacing: 0.5px !important;
        position: relative !important;
        overflow: hidden !important;
    }
    button[type="submit"]:hover, .btn-primary:hover, .action-btn:hover {
        transform: translateY(-2px) scale(1.02) !important;
        box-shadow: 0 6px 25px rgba(139,92,246,0.5), 0 0 50px rgba(245,158,11,0.3) !important;
    }
    button[type="submit"]:active, .btn-primary:active, .action-btn:active {
        transform: translateY(0) scale(0.98) !important;
    }
    button[type="submit"]::after, .btn-primary::after, .action-btn::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.15), transparent);
        transform: rotate(45deg) translateX(-100%);
        transition: 0.6s;
    }
    button[type="submit"]:hover::after, .btn-primary:hover::after, .action-btn:hover::after {
        transform: rotate(45deg) translateX(100%);
    }
    .nav-btn, .sidebar-item {
        transition: all 0.3s ease !important;
    }
    .nav-btn:hover, .sidebar-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(124,58,237,0.2);
    }

    table thead th {
        background: rgba(124,58,237,0.03);
        border-bottom: 1px solid rgba(124,58,237,0.08);
        white-space: nowrap;
    }
    table tbody tr { border-bottom: 1px solid rgba(0,0,0,0.03); }
    table tbody tr:hover { background: rgba(124,58,237,0.03); }

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
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.15), transparent);
        transform: rotate(45deg) translateX(-100%);
        transition: 0.6s;
    }
    [class*="bg-gradient"]:hover::after {
        transform: rotate(45deg) translateX(100%);
    }
    .badge-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
    #sidebar {
        position: fixed; inset: 0 auto 0 0; z-index: 50;
        width: 14rem; transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    #sidebar.open { transform: translateX(0); }
    #sidebar-overlay { position: fixed; inset: 0; z-index: 45; }
</style>

<div class="min-h-screen flex flex-col" style="background: linear-gradient(135deg, #f0f4ff 0%, #fdf2f8 40%, #eff6ff 100%);">

    {{-- Top Header Bar --}}
    <div class="flex items-center justify-between px-4 py-3 bg-white/90 border-b border-purple-100/50 sticky top-0 z-30 shrink-0">
        <div class="flex items-center gap-3">
            <button id="sidebar-toggle" onclick="toggleSidebar()" class="p-2 hover:bg-purple-50 rounded-lg cursor-pointer text-slate-600" title="Toggle sidebar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-purple-500 via-pink-500 to-amber-500 flex items-center justify-center text-white font-bold text-[10px]">CP</div>
                <span class="text-sm font-bold text-slate-800">Admin Panel</span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs text-purple-500 font-semibold hidden sm:inline">{{ Session::get('user.name') }}</span>
            <span class="text-xs text-purple-500 font-semibold sm:hidden">{{ substr(Session::get('user.name'), 0, 1) }}</span>
        </div>
    </div>

    {{-- Sidebar Overlay --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-40 bg-black/20 hidden" onclick="toggleSidebar()"></div>

    {{-- Sidebar --}}
    <aside id="sidebar" class="bg-white border-r border-purple-100/50 flex flex-col shadow-2xl">
        <div class="sidebar-header px-3 py-3 border-b border-purple-100/50 flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 via-pink-500 to-amber-500 flex items-center justify-center text-white font-black text-xs shadow shadow-purple-500/30 shrink-0">CP</div>
            <div class="min-w-0">
                <h1 class="font-bold text-slate-800 text-xs tracking-tight truncate">ClassPortal</h1>
                <p class="text-[9px] text-purple-500/60 font-semibold tracking-widest uppercase truncate">Admin</p>
            </div>
        </div>

        <nav class="flex-1 p-2 space-y-0.5 overflow-y-auto">
            <button onclick="navigateTo('overview')" id="nav-overview" class="nav-btn w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition-all duration-200 cursor-pointer sidebar-item-active">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                <span class="nav-label">Dashboard</span>
            </button>
            <button onclick="navigateTo('users')" id="nav-users" class="nav-btn w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition-all duration-200 cursor-pointer sidebar-item">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span class="nav-label">Users</span>
                <span id="user-count-badge" class="nav-badge ml-auto px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-purple-100 text-purple-600">0</span>
            </button>
            <button onclick="navigateTo('activity')" id="nav-activity" class="nav-btn w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition-all duration-200 cursor-pointer sidebar-item">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span class="nav-label">Activity Log</span>
            </button>
            <button onclick="navigateTo('exams')" id="nav-exams" class="nav-btn w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition-all duration-200 cursor-pointer sidebar-item">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                <span class="nav-label">Exams</span>
                <span id="exam-count-badge" class="nav-badge ml-auto px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-cyan-100 text-cyan-600">0</span>
            </button>
            <button onclick="navigateTo('content')" id="nav-content" class="nav-btn w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition-all duration-200 cursor-pointer sidebar-item">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span class="nav-label">Content</span>
            </button>
            <button onclick="navigateTo('results')" id="nav-results" class="nav-btn w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition-all duration-200 cursor-pointer sidebar-item">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span class="nav-label">Results</span>
            </button>
            <button onclick="navigateTo('feedback')" id="nav-feedback" class="nav-btn w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition-all duration-200 cursor-pointer sidebar-item">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span class="nav-label">Feedback</span>
                <span id="feedback-count-badge" class="nav-badge ml-auto px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-600">0</span>
            </button>
            <button onclick="navigateTo('settings')" id="nav-settings" class="nav-btn w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold transition-all duration-200 cursor-pointer sidebar-item">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="nav-label">Settings</span>
            </button>
            <hr class="my-2 border-purple-100/50">
            <a href="{{ route('logout') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-pink-500 hover:bg-pink-50 w-full">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span class="nav-label">Logout</span>
            </a>
        </nav>
    </aside>

    {{-- Main Content --}}
    <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8">
        <div class="max-w-7xl mx-auto space-y-6">

            {{-- Loading --}}
            <div id="loading" class="py-20 text-center">
                <div class="w-10 h-10 border-4 border-purple-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
                <p class="text-sm text-purple-400 mt-4 font-medium">Loading dashboard...</p>
            </div>

            {{-- Content --}}
            <div id="content" class="hidden space-y-6">

                {{-- === OVERVIEW === --}}
                <div id="page-overview" class="page-panel">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-purple-500/60">Administrator Console</span>
                            </div>
                            <h1 class="text-2xl font-extrabold text-slate-800">Dashboard Overview</h1>
                            <p class="text-sm text-slate-500">Welcome, <span class="font-semibold text-purple-600">{{ Session::get('user.name') }}</span>. Here is your system summary.</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span id="last-refresh" class="text-xs text-slate-400"></span>
                            <button onclick="fetchData()" class="p-2 rounded-xl text-slate-400 hover:text-purple-600 hover:bg-purple-50 transition cursor-pointer" title="Refresh">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="stat-card bg-white/90 rounded-2xl p-5 border border-white shadow-sm cursor-default">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-bold text-purple-500/60 uppercase tracking-widest">Total Users</p>
                                    <p class="text-3xl font-black text-slate-800 mt-1" id="stat-users">0</p>
                                </div>
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-400 to-blue-500 flex items-center justify-center text-white shadow-sm shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </div>
                            </div>
                            <div class="mt-3 flex gap-3 text-xs">
                                <span class="text-emerald-600 font-semibold"><span id="stat-students">0</span> students</span>
                                <span class="text-blue-600 font-semibold"><span id="stat-teachers">0</span> teachers</span>
                            </div>
                        </div>
                        <div class="stat-card bg-white/90 rounded-2xl p-5 border border-white shadow-sm cursor-default">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-bold text-purple-500/60 uppercase tracking-widest">Exams</p>
                                    <p class="text-3xl font-black text-slate-800 mt-1" id="stat-exams">0</p>
                                </div>
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center text-white shadow-sm shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-slate-500 font-medium"><span id="stat-published">0</span> published</p>
                        </div>
                        <div class="stat-card bg-white/90 rounded-2xl p-5 border border-white shadow-sm cursor-default">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-bold text-purple-500/60 uppercase tracking-widest">Lesson Notes</p>
                                    <p class="text-3xl font-black text-slate-800 mt-1" id="stat-notes">0</p>
                                </div>
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white shadow-sm shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-slate-500 font-medium"><span id="stat-plans">0</span> lesson plans</p>
                        </div>
                        <div class="stat-card bg-white/90 rounded-2xl p-5 border border-white shadow-sm cursor-default">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-bold text-purple-500/60 uppercase tracking-widest">Results</p>
                                    <p class="text-3xl font-black text-slate-800 mt-1" id="stat-results">0</p>
                                </div>
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white shadow-sm shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-slate-500 font-medium">Exam submissions</p>
                        </div>
                    </div>

                    <div class="bg-white/90 rounded-2xl border border-white shadow-sm">
                        <div class="px-6 py-4 border-b border-purple-100/50 flex items-center justify-between">
                            <h3 class="font-bold text-slate-800 text-sm">Recent Activities</h3>
                            <button onclick="navigateTo('activity')" class="text-xs text-purple-600 hover:text-purple-700 font-semibold cursor-pointer transition">View All &rarr;</button>
                        </div>
                        <div id="overview-activity-list" class="divide-y divide-purple-100/30 max-h-[300px] overflow-y-auto">
                            <div class="text-center py-8 text-sm text-slate-400">No activities yet.</div>
                        </div>
                    </div>
                </div>

                {{-- === USERS === --}}
                <div id="page-users" class="page-panel hidden">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">User Management</h2>
                            <p class="text-xs text-slate-500">Manage all registered users</p>
                        </div>
                        <span class="text-xs text-slate-500 bg-white px-3 py-1 rounded-full font-semibold border border-slate-200 shrink-0"><span id="users-total-count">0</span> total</span>
                    </div>
                    <div class="bg-white/90 rounded-2xl border border-white shadow-sm overflow-hidden">
                        <div class="p-4 border-b border-purple-100/50 flex flex-col sm:flex-row gap-3">
                            <div class="relative flex-1">
                                <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text" id="user-search" oninput="filterUsers()" placeholder="Search..." class="w-full pl-10 pr-4 py-2.5 input-bright rounded-xl text-sm transition">
                            </div>
                            <select id="role-filter" onchange="filterUsers()" class="px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:border-purple-500 ring-2 ring-transparent focus:ring-purple-500/10 shrink-0">
                                <option value="all">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                            </select>
                            <button onclick="exportUsersCSV()" class="px-4 py-2.5 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white rounded-xl text-sm font-semibold transition shadow-lg shadow-purple-500/20 cursor-pointer shrink-0">Export CSV</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-slate-500 font-semibold text-xs uppercase tracking-wider">
                                        <th class="px-4 py-3 text-left">User</th>
                                        <th class="px-4 py-3 text-left">Role</th>
                                        <th class="px-4 py-3 text-left">ID</th>
                                        <th class="px-4 py-3 text-left">Wallet</th>
                                        <th class="px-4 py-3 text-left">Joined</th>
                                        <th class="px-4 py-3 text-left">Status</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="users-tbody" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- === ACTIVITY === --}}
                <div id="page-activity" class="page-panel hidden">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">Activity Log</h2>
                            <p class="text-xs text-slate-500">Monitor all system activities</p>
                        </div>
                        <span class="text-xs text-slate-500 bg-white px-3 py-1 rounded-full font-semibold border border-slate-200 shrink-0"><span id="activity-total-count">0</span> events</span>
                    </div>
                    <div class="bg-white/90 rounded-2xl border border-white shadow-sm">
                        <div id="full-activity-list" class="divide-y divide-purple-100/30 max-h-[600px] overflow-y-auto">
                            <div class="text-center py-12 text-sm text-slate-400">No activities yet.</div>
                        </div>
                    </div>
                </div>

                {{-- === EXAMS === --}}
                <div id="page-exams" class="page-panel hidden">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">Exam Management</h2>
                            <p class="text-xs text-slate-500">View all created examinations</p>
                        </div>
                        <span class="text-xs text-slate-500 bg-white px-3 py-1 rounded-full font-semibold border border-slate-200 shrink-0"><span id="exams-total-count">0</span> exams</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="exams-grid"></div>
                </div>

                {{-- === CONTENT === --}}
                <div id="page-content" class="page-panel hidden">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">Content Library</h2>
                            <p class="text-xs text-slate-500">All lesson notes and lesson plans</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white/90 rounded-2xl border border-white shadow-sm">
                            <div class="px-5 py-4 border-b border-purple-100/50 flex items-center justify-between">
                                <h3 class="font-bold text-slate-800 text-sm">Lesson Notes</h3>
                                <span class="text-xs text-slate-400" id="notes-count">0</span>
                            </div>
                            <div id="content-notes" class="divide-y divide-purple-100/30 max-h-[500px] overflow-y-auto"></div>
                        </div>
                        <div class="bg-white/90 rounded-2xl border border-white shadow-sm">
                            <div class="px-5 py-4 border-b border-purple-100/50 flex items-center justify-between">
                                <h3 class="font-bold text-slate-800 text-sm">Lesson Plans</h3>
                                <span class="text-xs text-slate-400" id="plans-count">0</span>
                            </div>
                            <div id="content-plans" class="divide-y divide-purple-100/30 max-h-[500px] overflow-y-auto"></div>
                        </div>
                    </div>
                </div>

                {{-- === RESULTS === --}}
                <div id="page-results" class="page-panel hidden">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">Exam Results</h2>
                            <p class="text-xs text-slate-500">Student performance records</p>
                        </div>
                        <span class="text-xs text-slate-500 bg-white px-3 py-1 rounded-full font-semibold border border-slate-200 shrink-0"><span id="results-total-count">0</span> results</span>
                    </div>
                    <div class="bg-white/90 rounded-2xl border border-white shadow-sm overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-slate-500 font-semibold text-xs uppercase tracking-wider">
                                    <th class="px-4 py-3 text-left">Student</th>
                                    <th class="px-4 py-3 text-left">Exam</th>
                                    <th class="px-4 py-3 text-left">Subject</th>
                                    <th class="px-4 py-3 text-left">Score</th>
                                    <th class="px-4 py-3 text-left">Percentage</th>
                                    <th class="px-4 py-3 text-left">Date</th>
                                </tr>
                            </thead>
                            <tbody id="results-tbody" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                </div>

                {{-- === FEEDBACK === --}}
                <div id="page-feedback" class="page-panel hidden">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">User Feedback</h2>
                            <p class="text-xs text-slate-500">Support tickets and messages</p>
                        </div>
                        <span class="text-xs text-slate-500 bg-white px-3 py-1 rounded-full font-semibold border border-slate-200 shrink-0"><span id="feedback-total-count">0</span> messages</span>
                    </div>
                    <div class="bg-white/90 rounded-2xl border border-white shadow-sm">
                        <div id="feedback-list" class="divide-y divide-purple-100/30 max-h-[600px] overflow-y-auto"></div>
                    </div>
                </div>

                {{-- === SETTINGS === --}}
                <div id="page-settings" class="page-panel hidden">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">School Settings</h2>
                            <p class="text-xs text-slate-500">Manage school configuration</p>
                        </div>
                    </div>
                    <div class="bg-white/90 rounded-2xl border border-white shadow-sm p-6 max-w-2xl">
                        <form id="settings-form" onsubmit="saveSettings(event)" class="space-y-4">
                            <div>
                                <label class="text-xs font-semibold text-slate-600 block mb-1.5 uppercase tracking-wider">School Name</label>
                                <input type="text" id="set-school-name" class="w-full px-4 py-2.5 input-bright rounded-xl text-sm transition">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600 block mb-1.5 uppercase tracking-wider">School Address</label>
                                <input type="text" id="set-school-address" class="w-full px-4 py-2.5 input-bright rounded-xl text-sm transition">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600 block mb-1.5 uppercase tracking-wider">School Motto</label>
                                <input type="text" id="set-school-motto" class="w-full px-4 py-2.5 input-bright rounded-xl text-sm transition">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600 block mb-1.5 uppercase tracking-wider">Subjects (comma separated)</label>
                                <textarea id="set-subjects" rows="4" class="w-full px-4 py-2.5 input-bright rounded-xl text-sm transition"></textarea>
                            </div>
                            <div id="settings-msg" class="hidden p-3 rounded-xl text-xs font-bold"></div>
                            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white text-sm font-bold rounded-xl transition shadow-lg shadow-purple-500/20 cursor-pointer active:scale-[0.98]">Save Settings</button>
                        </form>
                    </div>

                    {{-- === AI PROVIDER SETTINGS === --}}
                    <div class="bg-white/90 rounded-2xl border border-white shadow-sm p-6 max-w-2xl mt-6">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">AI Provider</h3>
                            <span id="ai-key-badge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600"></span>
                        </div>
                        <p class="text-xs text-slate-500 mb-4">Controls lesson notes, lesson plans and question generation. Overrides the server .env. Get free keys at <a href="https://console.groq.com/keys" target="_blank" rel="noopener" class="text-purple-600 font-semibold underline">Groq</a> or <a href="https://platform.deepseek.com/api_keys" target="_blank" rel="noopener" class="text-purple-600 font-semibold underline">DeepSeek</a>.</p>
                        <form onsubmit="saveAiSettings(event)" class="space-y-4">
                            <div>
                                <label class="text-xs font-semibold text-slate-600 block mb-1.5 uppercase tracking-wider">API Base URL</label>
                                <input type="url" id="ai-base-url" placeholder="https://api.groq.com/openai" class="w-full px-4 py-2.5 input-bright rounded-xl text-sm transition">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600 block mb-1.5 uppercase tracking-wider">Model</label>
                                <input type="text" id="ai-model" placeholder="openai/gpt-oss-120b" class="w-full px-4 py-2.5 input-bright rounded-xl text-sm transition">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600 block mb-1.5 uppercase tracking-wider">API Key</label>
                                <input type="password" id="ai-api-key" placeholder="Paste full API key to replace" autocomplete="off" class="w-full px-4 py-2.5 input-bright rounded-xl text-sm transition">
                                <p class="text-[11px] text-slate-400 mt-1">Current: <span id="ai-key-preview"></span> — paste a new key only when changing it.</p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600 block mb-1.5 uppercase tracking-wider">Token Budget Per Request (0 = unlimited)</label>
                                <input type="number" id="ai-tpm-budget" value="7800" class="w-full px-4 py-2.5 input-bright rounded-xl text-sm transition">
                                <p class="text-[11px] text-slate-400 mt-1">Groq free tier = 8000 (keep 7800). Paid tiers / DeepSeek = 0.</p>
                            </div>
                            <div id="ai-msg" class="hidden p-3 rounded-xl text-xs font-bold"></div>
                            <div class="flex gap-2">
                                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white text-sm font-bold rounded-xl transition shadow-lg cursor-pointer active:scale-[0.98]">Save &amp; Apply</button>
                                <button type="button" onclick="testAiProvider(this)" class="px-6 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold rounded-xl transition cursor-pointer active:scale-[0.98]">Test Connection</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>{{-- /content --}}
        </div>
    </main>
</div>

{{-- Edit User Modal --}}
<div id="edit-modal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 bg-black/30 backdrop-blur-sm">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4 border border-slate-200">
        <div class="flex justify-between items-center">
            <div>
                <h4 class="font-bold text-slate-800">Edit User</h4>
                <p class="text-xs text-slate-400" id="modal-user-email"></p>
            </div>
            <button onclick="closeEditModal()" class="p-1 px-2 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-500 text-sm cursor-pointer transition">&times;</button>
        </div>
        <div id="modal-error" class="hidden p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs"></div>
        <div class="space-y-3">
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1 uppercase tracking-wider">Current Balance</label>
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800" id="modal-current-balance"></div>
            </div>
            <div>
                <label class="text-xs font-semibold text-slate-600 block mb-1 uppercase tracking-wider">Add Wallet Credit (&#8358;)</label>
                <input type="number" id="wallet-amount" value="5000" class="w-full px-4 py-2.5 input-bright rounded-xl text-sm">
            </div>
        </div>
        <div class="flex gap-2 pt-2">
            <button onclick="handleTopUp()" id="topup-btn" class="flex-1 py-2.5 bg-gradient-to-r from-purple-500 to-blue-500 hover:from-purple-600 hover:to-blue-600 text-white font-bold text-sm rounded-xl transition cursor-pointer active:scale-[0.98]">Recharge Wallet</button>
            <button onclick="handleToggleRole()" id="toggle-role-btn" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl border border-slate-200 transition cursor-pointer">Toggle Role</button>
        </div>
    </div>
</div>

<script>
let data = { users: [], exams: [], results: [], feedback: [], lessonPlans: [], lessonNotes: [], schoolConfig: {} };
let editingUserId = null;
let currentPage = 'overview';
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('hidden');
}

function navigateTo(page) {
    currentPage = page;
    document.querySelectorAll('.page-panel').forEach(p => p.classList.add('hidden'));
    const el = document.getElementById('page-' + page);
    if (el) el.classList.remove('hidden');
    document.querySelectorAll('.nav-btn').forEach(b => {
        const isActive = b.id === 'nav-' + page;
        b.className = 'nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 cursor-pointer ' + (isActive ? 'sidebar-item-active' : 'sidebar-item');
    });
    if (document.getElementById('sidebar').classList.contains('open')) toggleSidebar();
    if (page === 'feedback') renderFeedback();
    if (page === 'results') renderResults();
    if (page === 'settings') loadSettings();
}

function fetchData() {
    document.getElementById('loading').classList.remove('hidden');
    document.getElementById('content').classList.add('hidden');
    Promise.all([
        fetch('/api/admin/stats').then(r => r.json()),
        fetch('/api/admin/activities').then(r => r.json())
    ]).then(([stats, activityData]) => {
        data = { ...stats, activities: activityData.activities || [] };
        loadAiSettings();
        document.getElementById('last-refresh').textContent = 'Updated ' + new Date().toLocaleTimeString();
        renderStats();
        renderUsers();
        renderActivities();
        renderExams();
        renderContent();
        updateBadges();
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('content').classList.remove('hidden');
    }).catch(() => {
        document.getElementById('loading').innerHTML = '<p class="text-sm text-red-600 font-medium">Failed to load. <button onclick="fetchData()" class="underline cursor-pointer">Retry</button></p>';
    });
}

function updateBadges() {
    const users = data.users || [];
    const exams = data.exams || [];
    const feedback = data.feedback || [];
    ['user-count-badge','exam-count-badge','feedback-count-badge'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            if (id.includes('user')) el.textContent = users.length;
            else if (id.includes('exam')) el.textContent = exams.length;
            else if (id.includes('feedback')) el.textContent = feedback.length;
        }
    });
    document.getElementById('users-total-count').textContent = users.length;
    document.getElementById('exams-total-count').textContent = exams.length;
    document.getElementById('feedback-total-count').textContent = feedback.length;
    document.getElementById('activity-total-count').textContent = (data.activities || []).length;
    document.getElementById('results-total-count').textContent = data.results.length;
    document.getElementById('notes-count').textContent = (data.lessonNotes || []).length;
    document.getElementById('plans-count').textContent = (data.lessonPlans || []).length;
}

function renderStats() {
    document.getElementById('stat-users').textContent = data.users.length;
    document.getElementById('stat-students').textContent = data.users.filter(u => u.role === 'student').length;
    document.getElementById('stat-teachers').textContent = data.users.filter(u => u.role === 'teacher').length;
    document.getElementById('stat-exams').textContent = data.exams.length;
    document.getElementById('stat-published').textContent = data.exams.filter(e => e.isPublished).length;
    document.getElementById('stat-notes').textContent = (data.lessonNotes || []).length;
    document.getElementById('stat-plans').textContent = (data.lessonPlans || []).length;
    document.getElementById('stat-results').textContent = data.results.length;
}

function renderActivities() {
    const activities = data.activities || [];
    const preview = document.getElementById('overview-activity-list');
    const full = document.getElementById('full-activity-list');
    const iconMap = { user: '👤', exam: '📝', note: '📖', plan: '📋', feedback: '💬', import: '📥' };
    const html = (list, limit) => (list.slice(0, limit || list.length).map(a => {
        const ts = a.timestamp ? new Date(a.timestamp).toLocaleString() : '';
        const roleColor = a.userRole === 'admin' ? 'bg-red-100 text-red-700' : a.userRole === 'teacher' ? 'bg-emerald-100 text-emerald-700' : a.userRole === 'student' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600';
        return `<div class="flex items-start gap-3 p-3 rounded-xl hover:bg-purple-50/50 transition">
            <span class="text-lg shrink-0">${iconMap[a.icon] || '📌'}</span>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-slate-800">${a.title}</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold ${roleColor}">${a.userRole}</span>
                    <span class="text-xs text-slate-400">${ts}</span>
                </div>
            </div>
        </div>`;
    }).join('')) || '<div class="text-center py-8 text-sm text-slate-400">No activities yet.</div>';
    preview.innerHTML = html(activities, 10);
    full.innerHTML = html(activities);
}

function renderUsers() {
    const query = (document.getElementById('user-search').value || '').toLowerCase();
    const roleFilter = document.getElementById('role-filter').value;
    const filtered = (data.users || []).filter(u => {
        const s = (u.name + ' ' + u.email + ' ' + u.id + ' ' + (u.regNumber || '')).toLowerCase();
        if (roleFilter !== 'all' && u.role !== roleFilter) return false;
        return s.includes(query);
    });
    const tbody = document.getElementById('users-tbody');
    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-sm text-slate-400">No users found.</td></tr>';
        return;
    }
    tbody.innerHTML = filtered.map(u => {
        const roleBadge = u.role === 'admin' ? 'bg-red-100 text-red-700' : u.role === 'teacher' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700';
        const joined = u.createdAt ? new Date(u.createdAt).toLocaleDateString() : 'N/A';
        return `<tr class="hover:bg-purple-50/50">
            <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center text-xs font-bold text-white shrink-0">${(u.name || '?')[0].toUpperCase()}</div>
                    <div class="min-w-0">
                        <div class="font-medium text-slate-800 text-sm truncate">${u.name || 'N/A'}</div>
                        <div class="text-xs text-slate-400 truncate">${u.email}</div>
                    </div>
                </div>
            </td>
            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs font-semibold ${roleBadge}">${u.role}</span></td>
            <td class="px-4 py-3 text-xs text-slate-500 font-mono">${u.regNumber || u.id.substring(0, 10) + '...'}</td>
            <td class="px-4 py-3 text-sm font-semibold text-emerald-700 whitespace-nowrap">&#8358;${(u.walletBalance || 0).toLocaleString()}</td>
            <td class="px-4 py-3 text-xs text-slate-400 whitespace-nowrap">${joined}</td>
            <td class="px-4 py-3">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium ${u.isSuspended ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700'}">
                    <span class="badge-dot ${u.isSuspended ? 'bg-amber-500' : 'bg-emerald-500'}"></span>${u.isSuspended ? 'Suspended' : 'Active'}
                </span>
            </td>
            <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-1">
                    <button onclick="openEditModal('${u.id}')" class="p-1.5 hover:bg-purple-50 rounded-lg text-slate-500 cursor-pointer transition" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                    <button onclick="handleSuspend('${u.id}')" class="p-1.5 hover:bg-purple-50 rounded-lg ${u.isSuspended ? 'text-emerald-600' : 'text-amber-600'} cursor-pointer transition" title="${u.isSuspended ? 'Unsuspend' : 'Suspend'}">
                        ${u.isSuspended ? '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>' : '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>'}
                    </button>
                    <button onclick="handleDeleteUser('${u.id}')" class="p-1.5 hover:bg-red-50 rounded-lg text-red-500 cursor-pointer transition" title="Delete"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

function filterUsers() { renderUsers(); }

function renderExams() {
    const container = document.getElementById('exams-grid');
    const exams = data.exams || [];
    if (exams.length === 0) {
        container.innerHTML = '<p class="text-sm text-slate-400 text-center py-8 col-span-2">No exams created yet.</p>';
        return;
    }
    container.innerHTML = exams.map(e => `
        <div class="bg-white/90 rounded-2xl p-5 border border-white shadow-sm stat-card">
            <div class="flex justify-between items-start gap-3">
                <div class="min-w-0">
                    <h5 class="font-bold text-slate-800 truncate">${e.title}</h5>
                    <p class="text-xs text-slate-500 mt-0.5">${e.subject || 'N/A'} &middot; ${e.level || 'N/A'}</p>
                </div>
                <span class="px-2 py-0.5 rounded text-xs font-semibold shrink-0 ${e.isPublished ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}">${e.isPublished ? 'Published' : 'Draft'}</span>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                <span>${e.questions?.length || 0} questions</span>
                <span>${e.duration || 0} min</span>
                <span>By ${e.creatorName || 'Unknown'}</span>
            </div>
            <div class="mt-2 text-xs text-slate-400">Created ${e.createdAt ? new Date(e.createdAt).toLocaleDateString() : ''}</div>
        </div>
    `).join('');
}

function renderContent() {
    const notes = data.lessonNotes || [];
    const plans = data.lessonPlans || [];
    document.getElementById('content-notes').innerHTML = notes.length ? notes.map(n => `
        <div class="p-3 rounded-xl hover:bg-purple-50/50 transition">
            <div class="font-medium text-slate-800 text-sm truncate">${n.topic || 'Untitled'}</div>
            <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-2 flex-wrap">
                <span>${n.subject || 'N/A'}</span>
                <span class="w-1 h-1 rounded-full bg-slate-300 shrink-0"></span>
                <span>${n.classLevel || 'N/A'}</span>
                <span class="w-1 h-1 rounded-full bg-slate-300 shrink-0"></span>
                <span>${n.createdAt ? new Date(n.createdAt).toLocaleDateString() : ''}</span>
            </div>
        </div>
    `).join('') : '<div class="text-center py-8 text-sm text-slate-400">No lesson notes yet.</div>';
    document.getElementById('content-plans').innerHTML = plans.length ? plans.map(p => `
        <div class="p-3 rounded-xl hover:bg-purple-50/50 transition">
            <div class="font-medium text-slate-800 text-sm truncate">${p.topic || 'Untitled'}</div>
            <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-2 flex-wrap">
                <span>${p.subject || 'N/A'}</span>
                <span class="w-1 h-1 rounded-full bg-slate-300 shrink-0"></span>
                <span>${p.classLevel || 'N/A'}</span>
                <span class="w-1 h-1 rounded-full bg-slate-300 shrink-0"></span>
                <span>Week ${p.week || 1}</span>
            </div>
        </div>
    `).join('') : '<div class="text-center py-8 text-sm text-slate-400">No lesson plans yet.</div>';
}

function renderResults() {
    const tbody = document.getElementById('results-tbody');
    const results = data.results || [];
    if (results.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-sm text-slate-400">No results yet.</td></tr>';
        return;
    }
    tbody.innerHTML = results.map(r => {
        const pct = r.percentage || 0;
        const color = pct >= 70 ? 'text-emerald-600' : pct >= 50 ? 'text-amber-600' : 'text-red-600';
        return `<tr class="hover:bg-purple-50/50">
            <td class="px-4 py-3 font-medium text-slate-800">${r.studentName || 'Unknown'}</td>
            <td class="px-4 py-3 text-slate-600">${r.examTitle || 'N/A'}</td>
            <td class="px-4 py-3 text-slate-500">${r.subject || 'N/A'}</td>
            <td class="px-4 py-3 text-slate-800 whitespace-nowrap">${r.score || 0}/${r.totalQuestions || 0}</td>
            <td class="px-4 py-3 font-bold ${color} whitespace-nowrap">${pct}%</td>
            <td class="px-4 py-3 text-xs text-slate-400 whitespace-nowrap">${r.date ? new Date(r.date).toLocaleDateString() : ''}</td>
        </tr>`;
    }).join('');
}

function renderFeedback() {
    const container = document.getElementById('feedback-list');
    const feedback = data.feedback || [];
    if (feedback.length === 0) {
        container.innerHTML = '<div class="text-center py-12 text-sm text-slate-400">No feedback messages yet.</div>';
        return;
    }
    container.innerHTML = feedback.map(f => `
        <div class="p-4 hover:bg-purple-50/50 transition">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center text-xs font-bold text-white shrink-0">${(f.name || '?')[0].toUpperCase()}</div>
                    <div class="min-w-0">
                        <span class="font-semibold text-slate-800 text-sm">${f.name || 'Anonymous'}</span>
                        <span class="text-xs text-slate-400 ml-2">${f.email || ''}</span>
                    </div>
                </div>
                <button onclick="deleteFeedback('${f.id}')" class="p-1 hover:bg-red-50 rounded-lg text-red-400 cursor-pointer transition shrink-0" title="Delete"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
            <p class="text-sm text-slate-700 mt-2">${f.message || ''}</p>
            <p class="text-xs text-slate-400 mt-2">${f.date ? new Date(f.date).toLocaleString() : ''}</p>
        </div>
    `).join('');
}

function deleteFeedback(id) {
    if (!confirm('Delete this feedback?')) return;
    fetch('/api/admin/feedback/' + id + '/delete', { method: 'POST' })
        .then(r => r.json()).then(d => { if (d.success) fetchData(); });
}

function loadSettings() {
    fetch('/api/school-config').then(r => r.json()).then(d => {
        const cfg = d.config || d.schoolConfig || {};
        document.getElementById('set-school-name').value = cfg.name || '';
        document.getElementById('set-school-address').value = cfg.address || '';
        document.getElementById('set-school-motto').value = cfg.motto || '';
        document.getElementById('set-subjects').value = (d.subjects || data.subjects || []).join(', ');
    });
}

function saveSettings(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type=submit]');
    btn.disabled = true; btn.textContent = 'Saving...';
    fetch('/api/school-config', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            name: document.getElementById('set-school-name').value,
            address: document.getElementById('set-school-address').value,
            motto: document.getElementById('set-school-motto').value,
            subjects: document.getElementById('set-subjects').value.split(',').map(s => s.trim()).filter(Boolean),
        })
    }).then(r => r.json()).then(d => {
        const msg = document.getElementById('settings-msg');
        msg.className = 'hidden p-3 rounded-xl text-xs font-bold ' + (d.success ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200');
        msg.textContent = d.success ? 'Settings saved successfully!' : d.error || 'Failed to save.';
        msg.classList.remove('hidden');
        setTimeout(() => msg.classList.add('hidden'), 3000);
    }).finally(() => { btn.disabled = false; btn.textContent = 'Save Settings'; });
}

// ===== AI Provider Settings =====
function loadAiSettings() {
    fetch('/api/admin/ai-settings').then(r => r.json()).then(d => {
        if (!d.success) return;
        const s = d.settings;
        document.getElementById('ai-base-url').value = s.base_url || '';
        document.getElementById('ai-model').value = s.model || '';
        document.getElementById('ai-tpm-budget').value = s.tpm_budget ?? 7800;
        document.getElementById('ai-key-preview').textContent = s.key_preview || 'not set';
        const badge = document.getElementById('ai-key-badge');
        badge.textContent = s.key_source === 'override' ? 'Override active' : 'Using .env';
        badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full ' + (s.key_source === 'override' ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-600');
    }).catch(() => {});
}

function saveAiSettings(e) {
    e.preventDefault();
    const keyInput = document.getElementById('ai-api-key');
    if (!keyInput.value.trim()) {
        const msg = document.getElementById('ai-msg');
        msg.className = 'p-3 rounded-xl text-xs font-bold bg-red-50 text-red-700 border border-red-200';
        msg.textContent = 'Paste the full API key (keys cannot be read back after saving).';
        return;
    }
    fetch('/api/admin/ai-settings', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            base_url: document.getElementById('ai-base-url').value.trim(),
            model: document.getElementById('ai-model').value.trim(),
            api_key: keyInput.value.trim(),
            tpm_budget: parseInt(document.getElementById('ai-tpm-budget').value) || 0,
        })
    }).then(r => r.json()).then(d => {
        const msg = document.getElementById('ai-msg');
        msg.className = 'p-3 rounded-xl text-xs font-bold ' + (d.success ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200');
        msg.textContent = d.success ? (d.message + ' Takes effect immediately.') : (d.error || 'Failed to save.');
        msg.classList.remove('hidden');
        if (d.success) { keyInput.value = ''; loadAiSettings(); }
    }).catch(err => alert('Error: ' + err.message));
}

function testAiProvider(btn) {
    btn.disabled = true; btn.textContent = 'Testing...';
    const msg = document.getElementById('ai-msg');
    msg.classList.remove('hidden');
    msg.className = 'p-3 rounded-xl text-xs font-bold bg-slate-50 text-slate-600 border border-slate-200';
    msg.textContent = 'Sending a tiny test request to the AI provider...';
    fetch('/api/admin/ai-test', { method: 'POST' }).then(r => r.json()).then(d => {
        msg.className = 'p-3 rounded-xl text-xs font-bold ' + (d.success ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200');
        msg.textContent = d.success ? d.message : (d.error || 'Test failed.');
    }).catch(err => {
        msg.className = 'p-3 rounded-xl text-xs font-bold bg-red-50 text-red-700 border border-red-200';
        msg.textContent = 'Error: ' + err.message;
    }).finally(() => { btn.disabled = false; btn.textContent = 'Test Connection'; });
}

function exportUsersCSV() {
    const users = data.users || [];
    if (!users.length) return;
    let csv = 'Name,Email,Role,RegNumber,WalletBalance,Status,Joined\n';
    users.forEach(u => { csv += '"' + (u.name || '') + '","' + (u.email || '') + '","' + u.role + '","' + (u.regNumber || '') + '",' + (u.walletBalance || 0) + ',"' + (u.isSuspended ? 'Suspended' : 'Active') + '","' + (u.createdAt || '') + '"\n'; });
    const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'users_export.csv'; a.click();
}

function openEditModal(userId) {
    const user = data.users.find(u => u.id === userId);
    if (!user) return;
    editingUserId = userId;
    document.getElementById('modal-user-email').textContent = user.name + ' (' + user.email + ')';
    document.getElementById('modal-current-balance').textContent = '\u20A6' + (user.walletBalance || 0).toLocaleString();
    document.getElementById('wallet-amount').value = '5000';
    document.getElementById('modal-error').classList.add('hidden');
    document.getElementById('edit-modal').classList.remove('hidden');
    document.getElementById('toggle-role-btn').textContent = 'Make ' + (user.role === 'teacher' ? 'Student' : 'Teacher');
}

function closeEditModal() {
    document.getElementById('edit-modal').classList.add('hidden');
    editingUserId = null;
}

function handleTopUp() {
    if (!editingUserId) return;
    const user = data.users.find(u => u.id === editingUserId);
    if (!user) return;
    const amount = Number(document.getElementById('wallet-amount').value);
    if (!amount || amount <= 0) {
        document.getElementById('modal-error').textContent = 'Enter a valid amount.';
        document.getElementById('modal-error').classList.remove('hidden');
        return;
    }
    const newBalance = (user.walletBalance || 0) + amount;
    const btn = document.getElementById('topup-btn');
    btn.disabled = true; btn.textContent = 'Processing...';
    fetch('/api/admin/users/' + editingUserId + '/update', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ walletBalance: newBalance })
    }).then(r => r.json()).then(d => {
        if (d.success) { closeEditModal(); fetchData(); }
        else { document.getElementById('modal-error').textContent = d.error || 'Failed.'; document.getElementById('modal-error').classList.remove('hidden'); }
    }).catch(() => {
        document.getElementById('modal-error').textContent = 'Network error.';
        document.getElementById('modal-error').classList.remove('hidden');
    }).finally(() => { btn.disabled = false; btn.textContent = 'Recharge Wallet'; });
}

function handleToggleRole() {
    if (!editingUserId) return;
    const user = data.users.find(u => u.id === editingUserId);
    if (!user) return;
    const newRole = user.role === 'teacher' ? 'student' : 'teacher';
    fetch('/api/admin/users/' + editingUserId + '/update', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ role: newRole })
    }).then(r => r.json()).then(d => { if (d.success) { closeEditModal(); fetchData(); } });
}

function handleSuspend(userId) {
    const user = data.users.find(u => u.id === userId);
    if (!user) return;
    if (!confirm((user.isSuspended ? 'Unsuspend' : 'Suspend') + ' ' + (user.name || user.email) + '?')) return;
    fetch('/api/admin/users/' + userId + '/update', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ isSuspended: !user.isSuspended })
    }).then(r => r.json()).then(d => { if (d.success) fetchData(); });
}

function handleDeleteUser(userId) {
    const user = data.users.find(u => u.id === userId);
    if (!confirm('Delete ' + (user?.name || 'this user') + ' permanently?')) return;
    fetch('/api/admin/users/' + userId + '/delete', { method: 'POST' })
        .then(r => r.json()).then(d => { if (d.success) fetchData(); });
}

fetchData();
</script>
@endsection
