@extends('layouts.app')

@section('content')
<style>
    select, input[type="text"], input[type="number"], textarea {
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 50%, #f1f5f9 100%) !important;
        border: 2px solid #cbd5e1 !important;
        border-radius: 0.5rem !important;
        transition: all 0.3s ease !important;
        font-weight: 600 !important;
        color: #0f172a !important;
    }
    select:hover, input:hover, textarea:hover {
        border-color: #1e3a5f !important;
        box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1) !important;
        transform: translateY(-1px);
    }
    select:focus, input:focus, textarea:focus {
        border-color: #1e3a5f !important;
        box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.2), 0 0 20px rgba(37, 99, 235, 0.1) !important;
        outline: none !important;
    }
    button[type="submit"], .generate-btn, .action-btn {
        background: linear-gradient(135deg, #1e3a5f, #2563eb) !important;
        border: none !important;
        box-shadow: 0 4px 15px rgba(30, 58, 95, 0.3), 0 0 30px rgba(37, 99, 235, 0.15) !important;
        transition: all 0.3s ease !important;
        font-weight: 700 !important;
        letter-spacing: 0.5px !important;
        position: relative !important;
        overflow: hidden !important;
    }
    button[type="submit"]:hover, .generate-btn:hover, .action-btn:hover {
        transform: translateY(-2px) scale(1.02) !important;
        box-shadow: 0 6px 25px rgba(30, 58, 95, 0.4), 0 0 50px rgba(37, 99, 235, 0.2) !important;
    }
    button[type="submit"]:active, .generate-btn:active, .action-btn:active {
        transform: translateY(0) scale(0.98) !important;
    }
    button[type="submit"]::after, .generate-btn::after, .action-btn::after {
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
    button[type="submit"]:hover::after, .generate-btn:hover::after, .action-btn:hover::after {
        transform: rotate(45deg) translateX(100%);
    }
    .tab-btn {
        transition: all 0.3s ease !important;
    }
    .tab-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(30, 58, 95, 0.2);
    }
    .tab-btn.border-slate-800 {
        background: linear-gradient(135deg, #eef2ff, #dbeafe) !important;
    }
    #plan-action-buttons button, #note-action-buttons button, #q-action-buttons button {
        background: linear-gradient(135deg, #991b1b, #b91c1c) !important;
        border: none !important;
        box-shadow: 0 3px 10px rgba(153, 27, 27, 0.3) !important;
        transition: all 0.3s ease !important;
    }
    #plan-action-buttons button:hover, #note-action-buttons button:hover, #q-action-buttons button:hover {
        transform: translateY(-2px) scale(1.05) !important;
        box-shadow: 0 5px 20px rgba(153, 27, 27, 0.4) !important;
    }
    .lp-table select, .lp-table input, .lp-table textarea {
        background: white !important;
        border: 1px solid #e2e8f0 !important;
    }
    [class*="bg-gradient"] {
        transition: all 0.3s ease !important;
        position: relative !important;
        overflow: hidden !important;
    }
    [class*="bg-gradient"]:hover {
        transform: translateY(-2px) scale(1.02) !important;
        filter: brightness(1.05) saturate(1.1) !important;
        box-shadow: 0 6px 25px rgba(30, 58, 95, 0.3), 0 0 50px rgba(37, 99, 235, 0.15) !important;
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
</style>
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-white">
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Educator Dashboard</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900">Teacher Portal</h1>
                <p class="text-sm text-slate-500">Generate curriculum-based lesson plans, notes, and CBT exams</p>
            </div>
            <div class="flex items-center gap-2">
                @if(Session::get('user._switched'))
                    <form action="{{ route('switch.back') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-slate-800/10 text-slate-800 rounded-lg text-xs font-bold hover:bg-slate-800/20 transition cursor-pointer">
                            â¬… Back to {{ ucfirst(Session::get('user._original_role')) }}
                        </button>
                    </form>
                @else
                    <form action="{{ route('switch.to.student') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-bold hover:bg-red-900 transition cursor-pointer shadow-sm">
                            ðŸ‘¤ Student Portal
                        </button>
                    </form>
                @endif
                <span class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-bold">{{ Session::get('user.name') }}</span>
                <button onclick="initTeacherDashboard()" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-slate-600 transition cursor-pointer" title="Refresh">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
            </div>
        </div>

        {{-- Loading --}}
        <div id="loading" class="py-20 text-center">
            <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
            <p class="text-sm text-slate-400 mt-3 font-medium">Loading dashboard...</p>
        </div>

        {{-- Content --}}
        <div id="content" class="hidden space-y-6">

            {{-- Tabs --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="border-b border-slate-200 overflow-x-auto">
                    <div class="flex min-w-max">
                        <button onclick="switchTab('lesson-planner')" id="tab-lesson-planner-btn" class="tab-btn px-4 py-3 text-sm font-semibold border-b-2 border-slate-800 text-slate-800 bg-white transition cursor-pointer whitespace-nowrap">Lesson Planner</button>
                        <button onclick="switchTab('lesson-notes')" id="tab-lesson-notes-btn" class="tab-btn px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition cursor-pointer whitespace-nowrap">Lesson Notes</button>
                        <button onclick="switchTab('questions')" id="tab-questions-btn" class="tab-btn px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition cursor-pointer whitespace-nowrap">Question Pool</button>
                        <button onclick="switchTab('cbt-engine')" id="tab-cbt-engine-btn" class="tab-btn px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition cursor-pointer whitespace-nowrap">CBT Engine</button>
                        <button onclick="switchTab('results')" id="tab-results-btn" class="tab-btn px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition cursor-pointer whitespace-nowrap">Results</button>
                    </div>
                </div>

                {{-- === LESSON PLANNER TAB === --}}
                <div id="tab-lesson-planner" class="tab-panel p-5">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-slate-900">Generate Curriculum Lesson Plan</h3>
                            <p class="text-sm text-slate-500">Create professional curriculum-based lesson plans in tabular A4 format.</p>
                            <form id="lesson-plan-form" class="space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Subject</label>
                                        <select id="plan-subject" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600">
                                            <option value="">Select subject...</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Class</label>
                                        <select id="plan-class" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600"></select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Term</label>
                                        <select id="plan-term" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600"></select>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Week</label>
                                        <select id="plan-week" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600"></select>
                                    </div>
                                    <div class="relative">
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Duration</label>
                                        <input type="text" id="plan-duration" value="40 Minutes" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600">
                                        <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center voice-btn-container" data-input="plan-duration"></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="relative">
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Topic</label>
                                        <input type="text" id="plan-topic" required placeholder="e.g., Addition of Whole Numbers" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600">
                                        <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center voice-btn-container" data-input="plan-topic"></div>
                                    </div>
                                    <div class="relative">
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Sub-topic (Optional)</label>
                                        <input type="text" id="plan-subtopic" placeholder="e.g., Addition of 3-digit numbers" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600">
                                        <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center voice-btn-container" data-input="plan-subtopic"></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="relative">
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">School Name</label>
                                        <input type="text" id="plan-school" value="Covenant Foundation School" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600">
                                        <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center voice-btn-container" data-input="plan-school"></div>
                                    </div>
                                    <div class="relative">
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Teacher's Name</label>
                                        <input type="text" id="plan-teacher" value="{{ Session::get('user.name') }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600">
                                        <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center voice-btn-container" data-input="plan-teacher"></div>
                                    </div>
                                </div>
                                <button type="submit" id="plan-submit-btn" class="w-full py-2.5 bg-blue-600 text-white font-bold text-sm rounded-lg transition cursor-pointer">Generate Lesson Plan</button>
                            </form>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2 overflow-x-auto pb-2 mb-3" id="plans-subjects-scroll"></div>
                            <h4 class="text-sm font-bold text-slate-800 mb-3">Saved Lesson Plans</h4>
                            <div id="plans-list" class="space-y-2 max-h-[500px] overflow-y-auto">
                                <div class="text-center py-8 text-sm text-slate-400">No lesson plans yet. Generate one!</div>
                            </div>
                        </div>
                    </div>

                    {{-- Lesson Plan Preview --}}
                    <div id="plan-preview" class="hidden mt-6 bg-white border border-slate-200 rounded-xl p-6">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-4 border-b border-slate-200 pb-4">
                            <h3 class="text-lg font-bold text-slate-900">Lesson Plan Preview</h3>
                            <div class="flex flex-wrap gap-2" id="plan-action-buttons"></div>
                        </div>
                        <div id="plan-content" class="overflow-x-auto"></div>
                    </div>
                </div>

                {{-- === LESSON NOTES TAB === --}}
                <div id="tab-lesson-notes" class="tab-panel p-5 hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-slate-900">Generate Lesson Note</h3>
                            <p class="text-sm text-slate-500">Create detailed, comprehensive lesson notes aligned with the standard curriculum.</p>
                            <form id="lesson-note-form" class="space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Subject</label>
                                        <select id="note-subject" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600"></select>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Class</label>
                                        <select id="note-class" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600"></select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Term</label>
                                        <select id="note-term" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600"></select>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Week</label>
                                        <select id="note-week" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600"></select>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Periods</label>
                                        <select id="note-periods" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none">
                                            <option>1 Period</option><option selected>2 Periods</option><option>3 Periods</option><option>4 Periods</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="relative">
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Topic</label>
                                        <input type="text" id="note-topic" required placeholder="e.g., Linear Equations" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600">
                                        <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center voice-btn-container" data-input="note-topic"></div>
                                    </div>
                                    <div class="relative">
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Sub-topic (Optional)</label>
                                        <input type="text" id="note-subtopic" placeholder="e.g., Solving by Substitution" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600">
                                        <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center voice-btn-container" data-input="note-subtopic"></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Difficulty</label>
                                        <select id="note-difficulty" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                                             <option>Simple</option><option selected>Standard</option><option>Deep</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" id="note-submit-btn" class="w-full py-2.5 bg-blue-600 hover:bg-blue-600 text-white font-bold text-sm rounded-lg transition cursor-pointer">Generate Lesson Note</button>
                            </form>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2 overflow-x-auto pb-2 mb-3" id="notes-subjects-scroll"></div>
                            <h4 class="text-sm font-bold text-slate-800 mb-3">Saved Lesson Notes</h4>
                            <div id="notes-list" class="space-y-2 max-h-[500px] overflow-y-auto"></div>
                        </div>
                    </div>

                    <div id="note-preview" class="hidden mt-6 bg-white border border-slate-200 rounded-xl p-6">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-4 border-b border-slate-200 pb-4">
                            <h3 class="text-lg font-bold text-slate-900">Lesson Note Preview</h3>
                            <div class="flex flex-wrap gap-2" id="note-action-buttons"></div>
                        </div>
                        <div id="note-content" class="prose max-w-none text-sm"></div>
                    </div>
                </div>

                {{-- === QUESTION POOL TAB === --}}
                <div id="tab-questions" class="tab-panel p-5 hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-slate-900">Generate Questions</h3>
                            <p class="text-sm text-slate-500">Create curriculum-aligned objective and theory questions for exams and practice.</p>
                            <form id="questions-form" class="space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Subject</label>
                                        <select id="q-subject" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600"></select>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Class</label>
                                        <select id="q-class" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none"></select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Term</label>
                                        <select id="q-term" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none"></select>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Week</label>
                                        <select id="q-week" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none"></select>
                                    </div>
                                    <div class="relative">
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Count</label>
                                        <input type="number" id="q-count" required min="1" max="200" value="20" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600">
                                        <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center voice-btn-container" data-input="q-count"></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="relative">
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Topic</label>
                                        <input type="text" id="q-topic" required placeholder="e.g. Algebra" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600">
                                        <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center voice-btn-container" data-input="q-topic"></div>
                                    </div>
                                    <div class="relative">
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Sub-topic (Optional)</label>
                                        <input type="text" id="q-subtopic" placeholder="e.g., Quadratic Equations" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600">
                                        <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center voice-btn-container" data-input="q-subtopic"></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 block mb-1">Difficulty</label>
                                        <select id="q-difficulty" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-blue-600">
                                            <option value="">Standard</option>
                                            <option value="Easy">Easy</option>
                                            <option value="Standard">Standard</option>
                                            <option value="Hard">Hard</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-2 mt-6">
                                        <input type="checkbox" id="q-theory" class="rounded border-slate-300">
                                        <label for="q-theory" class="text-sm text-slate-700">Include Theory / Essay / Structured Questions</label>
                                    </div>
                                </div>
                                <button type="submit" id="q-submit-btn" class="w-full py-2.5 bg-blue-600 text-white font-bold text-sm rounded-lg transition cursor-pointer">Generate Questions</button>
                                <div id="q-error" class="hidden mt-2 p-3 bg-red-800/10 border border-red-700/20 text-red-700 text-xs rounded-lg"></div>
                            </form>
                            <div id="q-save-section" class="hidden p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-sm text-blue-700 font-medium" id="q-save-msg"></p>
                                <div class="flex gap-2 mt-2">
                                    <button onclick="saveQuestions()" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 cursor-pointer">Save to Pool</button>
                                    <button onclick="convertToCBT()" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 cursor-pointer">Convert to CBT</button>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2 overflow-x-auto pb-2 mb-3" id="qs-subjects-scroll"></div>
                            <h4 class="text-sm font-bold text-slate-800 mb-3">Saved Question Sets</h4>
                            <div id="q-sets-list" class="space-y-2 max-h-[500px] overflow-y-auto"></div>
                        </div>
                    </div>

                    {{-- Questions Preview --}}
                    <div id="q-preview" class="hidden mt-6 bg-white border border-slate-200 rounded-xl p-6">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-4 border-b border-slate-200 pb-4">
                            <h3 class="text-lg font-bold text-slate-900">Generated Questions</h3>
                            <div class="flex flex-wrap gap-2" id="q-action-buttons"></div>
                        </div>
                        <div id="q-content"></div>
                    </div>
                </div>

                {{-- === CBT ENGINE TAB === --}}
                <div id="tab-cbt-engine" class="tab-panel p-5 hidden">
                    <div class="space-y-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h3 class="text-lg font-bold text-slate-900">CBT Exam Manager</h3>
                        </div>

                        {{-- CSV Import Card --}}
                        <div class="bg-blue-600 rounded-xl p-4 sm:p-5 shadow-md">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center text-white shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-white">Bulk Import Questions via CSV</h4>
                                        <p class="text-xs text-blue-100 mt-0.5">Upload a CSV file with your questions, options, and answers. Supports up to 5,000 questions at once.</p>
                                    </div>
                                </div>
                                <button onclick="openCsvImport()" class="w-full sm:w-auto px-5 py-2.5 bg-white hover:bg-blue-50 text-blue-700 text-sm font-bold rounded-lg transition-all duration-200 cursor-pointer flex items-center justify-center gap-2 shadow-md shrink-0 border border-blue-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    Select CSV File
                                </button>
                            </div>
                        </div>
                        <div class="bg-white border border-slate-300 rounded-xl overflow-hidden shadow-sm">
                            <button onclick="this.nextElementSibling.classList.toggle('hidden');this.querySelector('svg').classList.toggle('rotate-180')" class="w-full flex items-center justify-between p-4 text-xs font-bold text-white hover:bg-blue-700 transition cursor-pointer border-b border-slate-300 bg-blue-600">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    CSV Format Reference
                                </span>
                                <svg class="w-4 h-4 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div class="hidden p-4 text-xs space-y-3">
                                <p class="text-slate-600 font-medium">Your CSV file must have these columns (header row required):</p>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-xs border-collapse">
                                        <thead>
                                            <tr class="bg-slate-100 text-slate-700 font-bold">
                                                <th class="p-2 border border-slate-200 text-left">Question</th>
                                                <th class="p-2 border border-slate-200 text-left">Option A</th>
                                                <th class="p-2 border border-slate-200 text-left">Option B</th>
                                                <th class="p-2 border border-slate-200 text-left">Option C</th>
                                                <th class="p-2 border border-slate-200 text-left">Option D</th>
                                                <th class="p-2 border border-slate-200 text-left">Correct Answer</th>
                                                <th class="p-2 border border-slate-200 text-left">Explanation</th>
                                                <th class="p-2 border border-slate-200 text-left">Marks</th>
                                                <th class="p-2 border border-slate-200 text-left">Difficulty</th>
                                                <th class="p-2 border border-slate-200 text-left">Topic</th>
                                                <th class="p-2 border border-slate-200 text-left">Sub Topic</th>
                                                <th class="p-2 border border-slate-200 text-left">Image URL</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="bg-white text-slate-600">
                                                <td class="p-2 border border-slate-200 font-medium text-slate-800">What is the capital of France?</td>
                                                <td class="p-2 border border-slate-200">London</td>
                                                <td class="p-2 border border-slate-200">Paris</td>
                                                <td class="p-2 border border-slate-200">Kano</td>
                                                <td class="p-2 border border-slate-200">Ibadan</td>
                                                <td class="p-2 border border-slate-200 font-bold text-blue-600">B</td>
                                                <td class="p-2 border border-slate-200">Abuja is the capital city of Nigeria.</td>
                                                <td class="p-2 border border-slate-200">1</td>
                                                <td class="p-2 border border-slate-200">Easy</td>
                                                <td class="p-2 border border-slate-200">Geography</td>
                                                <td class="p-2 border border-slate-200 text-slate-400">â€”</td>
                                            </tr>
                                            <tr class="bg-slate-50 text-slate-600">
                                                <td class="p-2 border border-slate-200 font-medium text-slate-800">Which planet is known as the Red Planet?</td>
                                                <td class="p-2 border border-slate-200">Earth</td>
                                                <td class="p-2 border border-slate-200">Mars</td>
                                                <td class="p-2 border border-slate-200">Venus</td>
                                                <td class="p-2 border border-slate-200">Jupiter</td>
                                                <td class="p-2 border border-slate-200 font-bold text-blue-600">B</td>
                                                <td class="p-2 border border-slate-200">Mars is called the Red Planet due to its reddish appearance.</td>
                                                <td class="p-2 border border-slate-200">1</td>
                                                <td class="p-2 border border-slate-200">Easy</td>
                                                <td class="p-2 border border-slate-200">Space</td>
                                                <td class="p-2 border border-slate-200 text-slate-400">â€”</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="flex flex-wrap gap-4 text-slate-500">
                                    <span><strong class="text-slate-700">Correct Answer:</strong> Letter (A, B, C, D)</span>
                                    <span><strong class="text-slate-700">Difficulty:</strong> Simple, Standard, or Deep</span>
                                    <span><strong class="text-slate-700">Explanation, Marks, Topic, Image URL</strong> are optional</span>
                                </div>
                                <div class="flex items-center gap-3 text-slate-500">
                                    <span>Download a <button type="button" onclick="downloadCsvTemplate()" class="text-slate-800 hover:underline font-medium cursor-pointer">CSV template</button> to get started.</span>
                                    <button onclick="navigator.clipboard.writeText('Question,Option A,Option B,Option C,Option D,Correct Answer,Explanation,Marks,Difficulty,Topic,Image URL');this.textContent='Copied!';setTimeout(()=>this.textContent='Copy Headers',2000)" class="px-2.5 py-1 bg-slate-100 hover:bg-blue-100 text-slate-800 border border-blue-200 rounded-md font-medium transition cursor-pointer text-xs">
                                        Copy Headers
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white border border-slate-300 rounded-xl p-5 shadow-sm">
                            <h4 class="text-sm font-bold text-slate-800 mb-3">Your Published Exams</h4>
                            <div id="cbt-exams-list" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="text-center py-8 text-sm text-slate-500 col-span-2">No exams created yet.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- === CSV Import Modal === --}}
                <div id="csv-import-modal" class="hidden fixed inset-0 z-50 bg-black/50 overflow-y-auto" style="padding-top:env(safe-area-inset-top, 0px);padding-bottom:env(safe-area-inset-bottom, 0px)">
                    <div class="min-h-full flex items-start sm:items-center justify-center p-2 sm:p-4">
                        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl mx-auto my-auto">
                        {{-- Header --}}
                        <div class="rounded-t-2xl border-b border-slate-200 px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between bg-white">
                            <div class="min-w-0">
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 truncate">Import Questions (CSV)</h3>
                                <p class="text-[11px] sm:text-xs text-slate-500" id="csv-import-step-label">Step 1 of 3: Upload File</p>
                            </div>
                            <button onclick="closeCsvImport()" class="p-2 hover:bg-slate-100 rounded-lg transition cursor-pointer shrink-0 ml-2">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="px-3 sm:px-6 py-3 sm:py-6">
                            {{-- Step 1: Upload --}}
                            <div id="csv-step-1" class="space-y-2 sm:space-y-3">
                                <div class="border-2 border-dashed border-slate-300 rounded-xl p-3 sm:p-5 text-center hover:border-blue-600 transition" id="csv-drop-zone">
                                    <input type="file" id="csv-file-input" accept=".csv" class="hidden" onchange="handleCsvFile(this)">
                                    <svg class="w-8 h-8 sm:w-10 sm:h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    <p class="text-xs sm:text-sm text-slate-500 mb-1">Drag & drop your CSV file here, or <button type="button" onclick="document.getElementById('csv-file-input').click()" class="text-blue-600 font-semibold hover:underline cursor-pointer">browse</button></p>
                                    <p class="text-[11px] sm:text-xs text-slate-400">Supports up to 5,000 questions. <button type="button" onclick="downloadCsvTemplate()" class="text-slate-800 hover:underline cursor-pointer font-medium">Download CSV Template</button></p>
                                    <div id="csv-file-info" class="hidden mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                        <p class="text-sm text-blue-700 font-medium" id="csv-file-name"></p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <div>
                                        <label class="text-[10px] font-semibold text-slate-600 block mb-0.5">Subject *</label>
                                        <select id="csv-subject" required class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:border-blue-600">
                                            <option value="">Select...</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-semibold text-slate-600 block mb-0.5">Class *</label>
                                        <select id="csv-class" required class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:border-blue-600"></select>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-semibold text-slate-600 block mb-0.5">Term *</label>
                                        <select id="csv-term" required class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:border-blue-600"></select>
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-semibold text-slate-600 block mb-0.5">Session *</label>
                                        <input type="text" id="csv-session" value="<?php echo date('Y') . '/' . (date('Y') + 1); ?>" class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:border-blue-600">
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <div class="flex-1 min-w-[80px]">
                                        <label class="text-[10px] font-semibold text-slate-600 block mb-0.5">Type *</label>
                                        <select id="csv-exam-type" required class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                                            <option value="CBT">CBT</option>
                                            <option value="Mixed">Mixed</option>
                                        </select>
                                    </div>
                                    <div class="w-[70px]">
                                        <label class="text-[10px] font-semibold text-slate-600 block mb-0.5">Duration</label>
                                        <input type="number" id="csv-duration" value="30" min="1" max="180" class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:border-blue-600">
                                    </div>
                                    <div class="w-[70px]">
                                        <label class="text-[10px] font-semibold text-slate-600 block mb-0.5">Marks/Q</label>
                                        <input type="number" id="csv-marks" value="1" min="1" max="100" class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:border-blue-600">
                                    </div>
                                    <div class="flex-1 min-w-[100px]">
                                        <label class="text-[10px] font-semibold text-slate-600 block mb-0.5">Topic</label>
                                        <input type="text" id="csv-topic" placeholder="e.g., Algebra" class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:border-blue-600">
                                    </div>
                                    <div class="flex-1 min-w-[100px]">
                                        <label class="text-[10px] font-semibold text-slate-600 block mb-0.5">Sub Topic</label>
                                        <input type="text" id="csv-subtopic" placeholder="e.g., Equations" class="w-full px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:border-blue-600">
                                    </div>
                                </div>
                            </div>

                            {{-- Step 2: Preview --}}
                            <div id="csv-step-2" class="hidden space-y-3 sm:space-y-4">
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="text-xs text-blue-700 font-semibold">Review your questions below, then import.</div>
                                    <div class="flex items-center gap-2">
                                        <button onclick="csvGoBack(1)" class="px-4 py-1.5 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-lg transition cursor-pointer border border-slate-300 shadow-sm">Back</button>
                                        <button id="csv-import-btn-top" onclick="confirmCsvImport()" class="px-5 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg transition-all duration-200 cursor-pointer shadow-sm border border-blue-500">Import Questions</button>
                                    </div>
                                </div>
                                <div id="csv-preview-stats" class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3"></div>

                                <div id="csv-preview-errors" class="hidden p-3 sm:p-4 bg-red-800/10 border border-red-700/20 rounded-lg">
                                    <button onclick="this.parentElement.classList.add('hidden')" class="float-right text-red-700 hover:text-red-700 cursor-pointer p-1">&times;</button>
                                    <h4 class="text-sm font-bold text-red-900 mb-2" id="csv-error-title">Errors Found</h4>
                                    <div id="csv-error-list" class="text-xs text-red-700 space-y-1 max-h-32 overflow-y-auto"></div>
                                </div>

                                <div>
                                    <label class="text-xs font-semibold text-slate-600 block mb-2">Duplicate Handling</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                        <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg cursor-pointer hover:border-slate-300 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 transition text-xs sm:text-sm">
                                            <input type="radio" name="duplicate_handling" value="import_all" checked class="accent-blue-600 shrink-0">
                                            <div class="min-w-0"><span class="font-medium text-slate-800">Import All</span><p class="text-[10px] sm:text-xs text-slate-500">Including duplicates</p></div>
                                        </label>
                                        <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg cursor-pointer hover:border-slate-300 has-[:checked]:border-slate-800 has-[:checked]:bg-slate-800/10 transition text-xs sm:text-sm">
                                            <input type="radio" name="duplicate_handling" value="skip" class="accent-slate-800 shrink-0">
                                            <div class="min-w-0"><span class="font-medium text-slate-800">Skip Duplicates</span><p class="text-[10px] sm:text-xs text-slate-500">Skip existing</p></div>
                                        </label>
                                        <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg cursor-pointer hover:border-slate-300 has-[:checked]:border-red-700 has-[:checked]:bg-red-800/10 transition text-xs sm:text-sm">
                                            <input type="radio" name="duplicate_handling" value="replace" class="accent-red-700 shrink-0">
                                            <div class="min-w-0"><span class="font-medium text-slate-800">Replace</span><p class="text-[10px] sm:text-xs text-slate-500">Update matching</p></div>
                                        </label>
                                    </div>
                                </div>



                                <div class="border border-slate-200 rounded-lg overflow-hidden bg-white">
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-xs">
                                            <thead class="bg-slate-100 sticky top-0">
                                                <tr>
                                                    <th class="px-2 sm:px-3 py-2 text-left font-semibold text-slate-600 whitespace-nowrap">#</th>
                                                    <th class="px-2 sm:px-3 py-2 text-left font-semibold text-slate-600 whitespace-nowrap">Question</th>
                                                    <th class="px-2 sm:px-3 py-2 text-left font-semibold text-slate-600 whitespace-nowrap">A</th>
                                                    <th class="px-2 sm:px-3 py-2 text-left font-semibold text-slate-600 whitespace-nowrap">B</th>
                                                    <th class="px-2 sm:px-3 py-2 text-left font-semibold text-slate-600 whitespace-nowrap">C</th>
                                                    <th class="px-2 sm:px-3 py-2 text-left font-semibold text-slate-600 whitespace-nowrap">D</th>
                                                    <th class="px-2 sm:px-3 py-2 text-center font-semibold text-slate-600 whitespace-nowrap">Answer</th>
                                                    <th class="px-2 sm:px-3 py-2 text-center font-semibold text-slate-600 whitespace-nowrap">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="csv-preview-table"></tbody>
                                        </table>
                                    </div>
                                    <div id="csv-pagination" class="hidden flex flex-col sm:flex-row items-center justify-between gap-2 px-3 sm:px-4 py-3 border-t border-slate-200 bg-slate-50 text-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="text-slate-500" id="csv-page-info">Page 1 of 1</span>
                                            <select id="csv-page-size" onchange="setCsvPageSize(+this.value)" class="border border-slate-300 rounded px-2 py-1 text-xs bg-white focus:outline-none focus:border-blue-600">
                                                <option value="10">10 / page</option>
                                                <option value="25" selected>25 / page</option>
                                                <option value="50">50 / page</option>
                                                <option value="100">100 / page</option>
                                            </select>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button onclick="goCsvPage(-1)" id="csv-prev-page" class="px-3 py-1.5 border border-slate-300 rounded hover:bg-white disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer">&larr; Prev</button>
                                            <span class="px-3 text-slate-600 font-medium" id="csv-page-num">1</span>
                                            <button onclick="goCsvPage(1)" id="csv-next-page" class="px-3 py-1.5 border border-slate-300 rounded hover:bg-white disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer">Next &rarr;</button>
                                        </div>
                                    </div>
                                    <div id="csv-preview-more" class="hidden p-3 text-center text-xs text-slate-400 border-t border-slate-200"></div>
                                </div>
                            </div>

                            {{-- Step 3: Result --}}
                            <div id="csv-step-3" class="hidden text-center space-y-4">
                                <div class="w-16 h-16 mx-auto bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <h3 class="text-xl font-bold text-slate-900" id="csv-result-title">Import Complete!</h3>
                                <p class="text-sm text-slate-500" id="csv-result-message"></p>
                                <div id="csv-result-details" class="grid grid-cols-3 gap-3 max-w-sm mx-auto"></div>
                                <div id="csv-result-errors" class="hidden mt-4 p-4 bg-red-800/10 border border-red-700/20 rounded-lg text-left">
                                    <h4 class="text-sm font-bold text-red-900 mb-2">Row Errors</h4>
                                    <div id="csv-result-error-list" class="text-xs text-red-700 space-y-1 max-h-40 overflow-y-auto"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer: always visible action buttons --}}
                        <div class="border-t border-slate-200 px-4 sm:px-6 py-3 bg-white rounded-b-2xl" id="csv-import-footer">
                            {{-- Step 1: Upload --}}
                            <div id="csv-footer-step-1" class="flex flex-col sm:flex-row justify-end gap-2">
                                <button onclick="closeCsvImport()" class="w-full sm:w-auto px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 text-sm font-bold rounded-lg transition cursor-pointer border border-slate-300 shadow-sm">Cancel</button>
                                <button id="csv-preview-btn" onclick="previewCsvImport()" disabled class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg opacity-60 cursor-not-allowed transition shadow-sm">Preview Import</button>
                            </div>
                            {{-- Step 2: Preview --}}
                            <div id="csv-footer-step-2" class="hidden flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                                <button onclick="csvGoBack(1)" class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition cursor-pointer text-center">Back</button>
                                <div class="flex items-center gap-3">
                                    <span id="csv-import-progress" class="hidden px-4 py-2 text-sm text-blue-600 font-semibold"><span class="animate-spin inline-block w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full mr-2 align-middle"></span>Importing...</span>
                                    <button id="csv-import-btn" onclick="confirmCsvImport()" class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg transition-all duration-200 cursor-pointer shadow-sm border border-blue-500">Import Questions</button>
                                </div>
                            </div>
                            {{-- Step 3: Complete --}}
                            <div id="csv-footer-step-3" class="hidden flex flex-col sm:flex-row justify-center gap-2">
                                <button onclick="closeCsvImport()" class="w-full sm:w-auto px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 text-sm font-bold rounded-lg transition cursor-pointer border border-slate-300 shadow-sm">Close</button>
                                <button onclick="csvGoBack(1); closeCsvImport(); openCsvImport();" class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg transition cursor-pointer shadow-sm">Import Another</button>
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                {{-- === Exam Settings Modal === --}}
                <div id="exam-settings-modal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4" onclick="event.stopPropagation()">
                        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-slate-900">Exam Settings</h3>
                            <button onclick="closeExamSettings()" class="p-2 hover:bg-slate-100 rounded-lg transition cursor-pointer">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="text-xs font-semibold text-slate-600 block mb-1">Exam Title</label>
                                <input type="text" id="exam-settings-title" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-slate-800">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-semibold text-slate-600 block mb-1">Duration (minutes)</label>
                                    <input type="number" id="exam-settings-duration" min="1" max="180" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-slate-800">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-600 block mb-1">Marks Per Question</label>
                                    <input type="number" id="exam-settings-marks" min="1" max="100" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-slate-800">
                                </div>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600 block mb-1">Instructions</label>
                                <textarea id="exam-settings-instructions" rows="3" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-slate-800"></textarea>
                            </div>
                            <div id="exam-settings-status" class="hidden text-xs font-semibold text-blue-600 bg-blue-50 p-3 rounded-lg"></div>
                        </div>
                        <div class="px-6 py-4 border-t border-slate-200 flex justify-end gap-2">
                            <button onclick="closeExamSettings()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition cursor-pointer">Cancel</button>
                            <button id="exam-settings-save-btn" onclick="saveExamSettings()" class="px-4 py-2 bg-blue-600 text-white text-sm font-bold rounded-lg transition cursor-pointer">Save Settings</button>
                        </div>
                    </div>
                </div>

                {{-- === RESULT VIEW MODAL === --}}
                <div id="result-modal" class="hidden fixed inset-0 z-50 bg-black/50 overflow-y-auto" onclick="if(event.target===this)closeResultModal()" style="padding-top:env(safe-area-inset-top, 0px);padding-bottom:env(safe-area-inset-bottom, 0px)">
                    <div class="min-h-full flex items-start sm:items-center justify-center p-2 sm:p-4">
                        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-auto my-auto" onclick="event.stopPropagation()">
                            <div class="sticky top-0 z-10 bg-white rounded-t-2xl border-b border-slate-200 px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">
                                <div class="min-w-0">
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900 truncate" id="result-modal-title">Student Result</h3>
                                    <p class="text-[11px] sm:text-xs text-slate-500" id="result-modal-subtitle"></p>
                                </div>
                                <button onclick="closeResultModal()" class="p-2 hover:bg-slate-100 rounded-lg transition cursor-pointer shrink-0 ml-2">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="px-3 sm:px-6 py-3 sm:py-6 max-h-[70vh] overflow-y-auto" id="result-modal-body">
                                <div class="text-center py-8 text-sm text-slate-500">Loading...</div>
                            </div>
                            <div class="sticky bottom-0 bg-white rounded-b-2xl border-t border-slate-200 px-4 sm:px-6 py-3 flex justify-end gap-2" id="result-modal-footer">
                                <button id="result-modal-wa-btn" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg transition cursor-pointer hidden">Send to WhatsApp</button>
                                <button onclick="closeResultModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition cursor-pointer">Close</button>
                                <button id="result-modal-download-btn" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-lg transition cursor-pointer hidden">Download Script</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- === RESULTS TAB === --}}
                <div id="tab-results" class="tab-panel p-5 hidden">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Results Dashboard</h3>
                                <p class="text-sm text-slate-500">View and download student results grouped by exam set</p>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-slate-500" id="results-pagination"></div>
                        </div>
                        <div id="results-list" class="space-y-2"></div>
                        <div id="results-pagination-bottom" class="flex items-center justify-center gap-2 pt-2"></div>
                    </div>
                </div>



            </div>
        </div>
    </div>
</div>

<script>
let teacherData = { plans: [], notes: [], exams: [], results: [], questionSets: [] };
let currentPlanId = null, currentNoteId = null, currentQsId = null, currentNote = null, generatingFromNote = false;
let currentQuestions = null;
let plansFilter = '', notesFilter = '', qsFilter = '';
const WHATSAPP_PHONE = '{{ \App\Helpers\WhatsApp::phone() }}';
const RESULTS_REFRESH_MS = 15000;
let resultsRefreshTimer = null;

function whatsappResultLink(result) {
    if (!WHATSAPP_PHONE) return '';
    const pct = Number(result.percentage || result.score / (result.totalPossibleMarks || result.totalQuestions || 1) * 100) || 0;
    const lines = [
        'EXAM RESULT NOTIFICATION',
        '----------------------------',
        'Student: ' + (result.studentName || 'Student'),
        'Exam: ' + (result.examTitle || ''),
        'Subject: ' + (result.subject || ''),
        'Score: ' + (result.score ?? 0) + ' / ' + (result.totalPossibleMarks || result.totalQuestions || 0),
        'Percentage: ' + Math.round(pct) + '%',
        'Grade: ' + (result.grade || ''),
        'Correct: ' + (result.correctAnswers || 0) + ' / ' + (result.totalQuestions || 0),
        'Date: ' + (result.date ? new Date(result.date).toLocaleString() : ''),
        'Status: ' + (pct >= 50 ? 'PASSED' : 'FAILED')
    ];
    return 'https://wa.me/' + WHATSAPP_PHONE + '?text=' + encodeURIComponent(lines.join('\n'));
}

function showToast(message) {
    let box = document.getElementById('result-toast');
    if (!box) {
        box = document.createElement('div');
        box.id = 'result-toast';
        box.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;background:#10b981;color:#fff;padding:12px 18px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.2);font-weight:600;cursor:pointer;';
        box.onclick = () => { box.remove(); };
        document.body.appendChild(box);
    }
    box.textContent = message;
    box.style.display = 'block';
    clearTimeout(box._t);
    box._t = setTimeout(() => { box.remove(); }, 8000);
}
// ====== DATA LOADING ======
async function initTeacherDashboard() {
    document.getElementById('loading').classList.remove('hidden');
    document.getElementById('content').classList.add('hidden');
    try {
        const res = await fetch('/api/teacher/init').then(r => r.json());

        // Populate selects
        document.querySelectorAll('select[id$="-subject"], select[id$="-subject"], #q-subject, #plan-subject, #note-subject').forEach(sel => {
            sel.innerHTML = '<option value="">Select subject...</option>' + (res.subjects || []).map(s => `<option value="${s}">${s}</option>`).join('');
        });
        document.querySelectorAll('#plan-class, #note-class, #q-class').forEach(sel => {
            sel.innerHTML = (res.classes || []).map(c => `<option value="${c}">${c}</option>`).join('');
        });
        document.querySelectorAll('#plan-term, #note-term, #q-term').forEach(sel => {
            sel.innerHTML = (res.terms || []).map(t => `<option value="${t}">${t}</option>`).join('');
        });
        document.querySelectorAll('#plan-week, #note-week, #q-week').forEach(sel => {
            sel.innerHTML = (res.weeks || []).map(w => `<option value="${w}">Week ${w}</option>`).join('');
        });

        // Populate data
        teacherData.plans = res.plans || [];
        teacherData.notes = res.notes || [];
        teacherData.exams = res.exams || [];
        teacherData.results = res.results || [];
        teacherData.questionSets = res.questionSets || [];

        renderPlans();
        renderNotes();
        renderExams();
        renderResults();
        renderQuestionSets();
        renderPlanFilters();
        renderNoteFilters();
        renderQsFilters();

        // Load the most recent lesson note (if any) without switching tabs
        if (teacherData.notes.length > 0) {
            const last = teacherData.notes[teacherData.notes.length - 1];
            currentNoteId = last.id;
            currentNote = last;
            displayLessonNote(last);
        }

        document.getElementById('loading').classList.add('hidden');
        document.getElementById('content').classList.remove('hidden');
    } catch(e) {
        document.getElementById('loading').innerHTML = '<p class="text-sm text-red-700 font-medium">Failed to load. <button onclick="initTeacherDashboard()" class="underline cursor-pointer">Retry</button></p>';
    }
}

async function pollResults() {
    if (!document.getElementById('content').classList.contains('hidden')) {
        try {
            const res = await fetch('/api/teacher/init').then(r => r.json());
            if (!res.results) return;
            const before = teacherData.results.length;
            teacherData.results = res.results || [];
            const after = teacherData.results.length;
            if (after > before && currentTab === 'results') {
                showToast(after - before + ' new exam result' + (after - before > 1 ? 's' : '') + ' received');
            }
            renderResults();
        } catch(e) { /* keep previous data on failure */ }
    }
}
function stopResultsPolling() {
    if (resultsRefreshTimer) { clearInterval(resultsRefreshTimer); resultsRefreshTimer = null; }
}
function startResultsPolling() {
    if (resultsRefreshTimer) return;
    resultsRefreshTimer = setInterval(pollResults, RESULTS_REFRESH_MS);
}

// ====== LESSON PLAN ======
document.getElementById('lesson-plan-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('plan-submit-btn');
    btn.disabled = true; btn.textContent = 'Generating...';
    try {
        const res = await fetch('/api/ai/lesson-plan', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({
                subject: document.getElementById('plan-subject').value,
                class: document.getElementById('plan-class').value,
                term: document.getElementById('plan-term').value,
                week: parseInt(document.getElementById('plan-week').value),
                topic: document.getElementById('plan-topic').value,
                subTopic: document.getElementById('plan-subtopic').value,
                schoolName: document.getElementById('plan-school').value,
                teacherName: document.getElementById('plan-teacher').value,
                duration: document.getElementById('plan-duration').value,
            })
        });
        const data = await res.json();
        if (data.success) {
            currentPlanId = data.planId;
            displayLessonPlan(data.plan);
            initTeacherDashboard();
        } else {
            alert(data.error || 'Generation failed.');
        }
    } catch(e) { alert('Network error.'); }
    finally { btn.disabled = false; btn.textContent = 'Generate Lesson Plan'; }
});

function displayLessonPlan(plan) {
    document.getElementById('plan-preview').classList.remove('hidden');
    const container = document.getElementById('plan-content');
    const steps = plan.lessonSteps || [];
    const objectives = plan.behaviouralObjectives || [];
    const materials = plan.instructionalMaterials || [];

    const stepsHtml = steps.map(s => `<tr>
        <td style="padding:1px 2px;border:1px solid #000;font-size:7pt;font-weight:700;vertical-align:top;text-align:center">${s.step || ''}</td>
        <td style="padding:1px 2px;border:1px solid #000;font-size:7pt;vertical-align:top">${s.teacherActivities || ''}</td>
        <td style="padding:1px 2px;border:1px solid #000;font-size:7pt;vertical-align:top">${s.learnerActivities || ''}</td>
        <td style="padding:1px 2px;border:1px solid #000;font-size:7pt;vertical-align:top">${s.learningPoints || ''}</td>
    </tr>`).join('');

    const stepsHeader = `<tr>
        <th style="padding:1px 2px;border:1px solid #000;font-size:7pt;font-weight:700;text-align:left;background:#1a56db;color:#fff">Step</th>
        <th style="padding:1px 2px;border:1px solid #000;font-size:7pt;font-weight:700;text-align:left;background:#1a56db;color:#fff">Teacher's Activities</th>
        <th style="padding:1px 2px;border:1px solid #000;font-size:7pt;font-weight:700;text-align:left;background:#1a56db;color:#fff">Learners' Activities</th>
        <th style="padding:1px 2px;border:1px solid #000;font-size:7pt;font-weight:700;text-align:left;background:#1a56db;color:#fff">Learning Points</th>
    </tr>`;

    const objItems = objectives.map(o => `<tr><td style="padding:0 2px;font-size:7.5pt;border:1px solid #000" colspan="4">${o}</td></tr>`).join('');
    const matItems = materials.length ? `<tr><td style="padding:1px 2px;font-size:7pt;border:1px solid #000" colspan="4">${materials.join('; ')}</td></tr>` : '';

    const remarksHtml = `<tr>
        <td style="padding:1px 2px;border:1px solid #000;font-size:7pt;font-weight:700;width:12%">Remarks</td>
        <td style="padding:1px 2px;border:1px solid #000;font-size:7pt" colspan="3"></td>
    </tr>`;

    const dateStr = new Date().toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
    const sigHtml = `<tr>
        <td style="padding:1px 2px;border:1px solid #000;font-size:7.5pt;width:25%"><b>Teacher's Signature:</b> _______________</td>
        <td style="padding:1px 2px;border:1px solid #000;font-size:7.5pt;width:25%"><b>Date:</b> _______________</td>
        <td style="padding:1px 2px;border:1px solid #000;font-size:7.5pt;width:25%"><b>Head Teacher's Signature:</b> _______________</td>
        <td style="padding:1px 2px;border:1px solid #000;font-size:7.5pt;width:25%"><b>Date:</b> _______________</td>
    </tr>`;

    container.innerHTML = `
    <style>
        .lp-table { width:100%; border-collapse:collapse; font-family:Arial,sans-serif; }
        .lp-table td, .lp-table th { border:1px solid #000; }
        .lp-container { max-width:210mm; margin:0 auto; background:#fff; page-break-inside:avoid; break-inside:avoid; }
        @media print {
            .lp-container { max-width:100%; margin:0; padding:0; }
            .lp-table { font-size:7pt !important; }
            .lp-table td, .lp-table th { padding:1px 2px !important; }
            .no-print { display:none !important; }
        }
    </style>
    <div class="lp-container">
        <table class="lp-table" style="font-size:7.5pt">
            <tr>
                <th colspan="4" style="padding:3px;font-size:9pt;font-weight:700;text-align:center;background:#1a56db;color:#fff;border:1px solid #000">LESSON PLAN</th>
            </tr>
            <tr>
                <td style="padding:1px 3px;font-size:7.5pt;font-weight:700;width:12%">School:</td>
                <td style="padding:1px 3px;font-size:7.5pt;width:38%">${plan.schoolName || ''}</td>
                <td style="padding:1px 3px;font-size:7.5pt;font-weight:700;width:12%">Teacher:</td>
                <td style="padding:1px 3px;font-size:7.5pt;width:38%">${plan.teacherName || ''}</td>
            </tr>
            <tr>
                <td style="padding:1px 3px;font-size:7.5pt;font-weight:700">Subject:</td>
                <td style="padding:1px 3px;font-size:7.5pt">${plan.subject || ''}</td>
                <td style="padding:1px 3px;font-size:7.5pt;font-weight:700">Class:</td>
                <td style="padding:1px 3px;font-size:7.5pt">${plan.class || ''}${plan.ageRange ? ' (' + plan.ageRange + ')' : ''}</td>
            </tr>
            <tr>
                <td style="padding:1px 3px;font-size:7.5pt;font-weight:700">Term:</td>
                <td style="padding:1px 3px;font-size:7.5pt">${plan.term || ''}</td>
                <td style="padding:1px 3px;font-size:7.5pt;font-weight:700">Week:</td>
                <td style="padding:1px 3px;font-size:7.5pt">${plan.week || ''}</td>
            </tr>
            <tr>
                <td style="padding:1px 3px;font-size:7.5pt;font-weight:700">Date:</td>
                <td style="padding:1px 3px;font-size:7.5pt">${plan.date || ''}</td>
                <td style="padding:1px 3px;font-size:7.5pt;font-weight:700">Duration:</td>
                <td style="padding:1px 3px;font-size:7.5pt">${plan.duration || ''}</td>
            </tr>
            <tr>
                <td style="padding:1px 3px;font-size:7.5pt;font-weight:700" colspan="2">Topic:</td>
                <td style="padding:1px 3px;font-size:7.5pt" colspan="2">${plan.topic || ''}</td>
            </tr>
            <tr>
                <td style="padding:1px 3px;font-size:7.5pt;font-weight:700" colspan="4">Behavioural Objectives</td>
            </tr>
            ${objItems}
            ${materials.length ? `<tr><td style="padding:1px 3px;font-size:7pt;font-weight:700" colspan="4">Instructional Materials</td></tr>${matItems}` : ''}
            ${plan.previousKnowledge ? `<tr><td style="padding:1px 3px;font-size:7pt;font-weight:700" colspan="4">Previous Knowledge</td></tr><tr><td style="padding:1px 3px;font-size:7pt" colspan="4">${plan.previousKnowledge}</td></tr>` : ''}
            <tr>
                <td style="padding:1px 3px;font-size:7.5pt;font-weight:700" colspan="4">Lesson Procedure</td>
            </tr>
            <tr>
                <td colspan="4" style="padding:0;border:0">
                    <table style="width:100%;border-collapse:collapse;font-size:7pt">
                        ${stepsHeader}
                        ${stepsHtml || '<tr><td style="padding:2px;border:1px solid #000;font-size:7pt" colspan="4">No steps available</td></tr>'}
                    </table>
                </td>
            </tr>
            ${plan.evaluation ? `<tr><td style="padding:1px 3px;font-size:7pt;font-weight:700" colspan="4">Evaluation</td></tr><tr><td style="padding:1px 3px;font-size:7pt" colspan="4">${plan.evaluation}</td></tr>` : ''}
            ${plan.assignment ? `<tr><td style="padding:1px 3px;font-size:7pt;font-weight:700" colspan="4">Assignment / Homework</td></tr><tr><td style="padding:1px 3px;font-size:7pt" colspan="4">${plan.assignment}</td></tr>` : ''}
            ${plan.summary ? `<tr><td style="padding:1px 3px;font-size:7pt;font-weight:700" colspan="4">Summary</td></tr><tr><td style="padding:1px 3px;font-size:7pt" colspan="4">${plan.summary}</td></tr>` : ''}
            ${plan.conclusion ? `<tr><td style="padding:1px 3px;font-size:7pt;font-weight:700" colspan="4">Conclusion</td></tr><tr><td style="padding:1px 3px;font-size:7pt" colspan="4">${plan.conclusion}</td></tr>` : ''}
            ${remarksHtml}
            ${sigHtml}
        </table>
    </div>
    `;

    document.getElementById('plan-action-buttons').innerHTML = `
        <button onclick="downloadPlan('pdf')" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-900 cursor-pointer no-print">PDF</button>
        <button onclick="downloadPlan('docx')" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 cursor-pointer no-print">DOCX</button>
        <button onclick="printPlan()" class="px-3 py-1.5 bg-gray-800 text-white text-xs font-bold rounded-lg hover:bg-gray-900 cursor-pointer no-print">Print</button>
        <button onclick="copyPlanContent()" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 cursor-pointer no-print">Copy</button>
        <button onclick="sharePlan()" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 cursor-pointer no-print">Share</button>
        <button onclick="readAloud('plan-content')" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 cursor-pointer no-print">Read Aloud</button>
        <button onclick="deletePlan()" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-950 cursor-pointer no-print">Delete</button>
    `;
}

function deletePlan() {
    if (!currentPlanId || !confirm('Delete this lesson plan? This cannot be undone.')) return;
    fetch('/api/lesson-plans/' + currentPlanId, { method: 'DELETE', headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                currentPlanId = null;
                document.getElementById('plan-preview').classList.add('hidden');
                initTeacherDashboard();
            } else { alert('Delete failed.'); }
        })
        .catch(() => alert('Network error.'));
}

function downloadPlan(format) { if (currentPlanId) window.open('/api/download/lesson-plan/' + currentPlanId + '/' + format, '_blank'); }
function printPlan() { window.print(); }
function copyPlanContent() {
    const text = document.getElementById('plan-content').innerText;
    navigator.clipboard.writeText(text).then(() => alert('Lesson plan copied!')).catch(() => alert('Failed to copy.'));
}
async function sharePlan() {
    if (!currentPlanId) return;
    const url = '/api/download/lesson-plan/' + currentPlanId + '/docx';
    const plan = teacherData.plans.find(p => p.id === currentPlanId);
    const title = plan ? plan.topic : 'Lesson Plan';
    // Direct DOCX file share — recipient sees file immediately (tabular, not a link)
    try {
        const res = await fetch(url, { credentials: 'same-origin' });
        if (res.ok) {
            const blob = await res.blob();
            const file = new File([blob], title.replace(/[^a-z0-9]/gi,'_').substring(0,30) + '.docx', { type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' });
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                await navigator.share({ files: [file], title: title + ' - Cfschool', text: title + ' - DOCX tabular format' });
                return;
            }
            if (navigator.share) {
                try { await navigator.share({ files: [file], title: title }); return; } catch(e) {}
            }
            // Fallback for browsers without file-share: download file so user can attach directly to WhatsApp/Email
            // This ensures recipient gets the DOCX file itself, not a link to the website
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = title.replace(/[^a-z0-9]/gi,'_').substring(0,30) + '.docx';
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            setTimeout(() => URL.revokeObjectURL(a.href), 1000);
            alert('Direct file share not supported on this browser. DOCX tabular file downloaded.\n\nPlease open WhatsApp or Email and attach the file from your Downloads folder.\nThe recipient will receive the DOCX file immediately — not a link.');
            return;
        }
    } catch(e) { console.log('File share failed', e); }
    window.open(url, '_blank');
}

// ====== LESSON NOTE ======
document.getElementById('lesson-note-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('note-submit-btn');
    btn.disabled = true; btn.textContent = 'Generating...';
    try {
        const res = await fetch('/api/ai/lesson-note', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({
                subject: document.getElementById('note-subject').value,
                class: document.getElementById('note-class').value,
                term: document.getElementById('note-term').value,
                week: parseInt(document.getElementById('note-week').value),
                topic: document.getElementById('note-topic').value,
                subTopic: document.getElementById('note-subtopic').value,
                periods: document.getElementById('note-periods').value,
                difficulty: document.getElementById('note-difficulty').value,
            })
        });
        const data = await res.json();
        if (data.success) {
            currentNoteId = data.noteId;
            currentNote = data.note;
            displayLessonNote(data.note);
            initTeacherDashboard();
        } else { alert(data.error || 'Generation failed.'); }
    } catch(e) { alert('Network error.'); }
    finally { btn.disabled = false; btn.textContent = 'Generate Lesson Note'; }
});

function displayLessonNote(note) {
    document.getElementById('note-preview').classList.remove('hidden');
    const container = document.getElementById('note-content');
    const noteContent = note.content || note.detailedNote || note.body || note.noteContent || note.htmlContent || note.lessonContent || note.lesson_content || note.definition || '';
    const sections = note.sections || [];
    const evaluationRaw = note.evaluationQuestions || note.evaluation || [];
    const evaluation = Array.isArray(evaluationRaw) ? evaluationRaw : [evaluationRaw];
    const keyPoints = note.keyPoints || note.key_points || [];

    let extraHtml = '';

    sections.forEach(sec => {
        if (sec.heading && sec.content) {
            extraHtml += `<div class="mt-4"><h3 class="text-base font-bold text-slate-800 mb-2">${sec.heading}</h3><div class="text-sm">${sec.content}</div></div>`;
        }
    });

    if (evaluation.length) {
        extraHtml += `<div class="mt-4"><h3 class="text-base font-bold text-slate-800 mb-2">Evaluation Questions</h3><ol class="text-sm pl-4 space-y-1">${evaluation.map(eq => `<li>${eq}</li>`).join('')}</ol></div>`;
    }

    if (note.assignment) {
        extraHtml += `<div class="mt-4"><h3 class="text-base font-bold text-slate-800 mb-2">Assignment</h3><div class="text-sm">${note.assignment}</div></div>`;
    }

    if (keyPoints.length) {
        extraHtml += `<div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl"><h3 class="text-sm font-bold text-blue-700 mb-2">Key Points to Remember</h3><ul class="text-sm space-y-1 list-disc pl-5 text-blue-600">${keyPoints.map(k => `<li>${k}</li>`).join('')}</ul></div>`;
    }

    container.innerHTML = `
        <div class="text-center border-b-2 border-blue-600 pb-3 mb-4">
            <h1 class="text-xl font-bold text-blue-600">${note.topic || ''}</h1>
            <p class="text-xs text-slate-500">${note.subject || ''} | ${note.class || ''} | ${note.term || ''} | Week ${note.week || ''} | ${note.periods || ''}</p>
        </div>
        ${noteContent}
        ${extraHtml}
    `;

    document.getElementById('note-action-buttons').innerHTML = `
        <button onclick="downloadNote('pdf')" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-900 cursor-pointer">PDF</button>
        <button onclick="downloadNote('docx')" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 cursor-pointer">DOCX</button>
        <button onclick="printNote()" class="px-3 py-1.5 bg-gray-800 text-white text-xs font-bold rounded-lg hover:bg-gray-900 cursor-pointer">Print</button>
        <button onclick="copyNoteContent()" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 cursor-pointer">Copy</button>
        <button onclick="copyNoteLink()" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 cursor-pointer" title="Copy note link"><svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg></button>
        <button onclick="deleteNote()" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-950 cursor-pointer">Delete</button>
        <button onclick="shareNote()" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 cursor-pointer">Share</button>
        <button onclick="readAloud('note-content')" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 cursor-pointer">Read Aloud</button>
        <button onclick="generateQuestionsFromNote()" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-900 cursor-pointer">Generate Q from Note</button>
    `;
}

function downloadNote(format) { if (currentNoteId) window.open('/api/download/lesson-note/' + currentNoteId + '/' + format, '_blank'); }
function printNote() { window.print(); }
function copyNoteContent() {
    navigator.clipboard.writeText(document.getElementById('note-content').innerText).then(() => alert('Lesson note copied!')).catch(() => {});
}
function shareNote() {
    const text = document.getElementById('note-content').innerText;
    if (navigator.share) navigator.share({ title: 'Lesson Note', text }).catch(() => {});
    else { copyNoteContent(); alert('Content copied for sharing!'); }
}
function copyNoteLink() {
    if (!currentNoteId) return;
    const url = window.location.origin + '/shared/note/' + currentNoteId;
    navigator.clipboard.writeText(url).then(() => alert('Note link copied!')).catch(() => { fallbackCopy(url); });
}
function fallbackCopy(text) {
    const ta = document.createElement('textarea'); ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
    document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); alert('Note link copied!');
}
async function deleteNote() {
    if (!currentNoteId || !confirm('Delete this lesson note? This cannot be undone.')) return;
    try {
        const res = await fetch('/api/lesson-notes/' + currentNoteId, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) {
            currentNoteId = null;
            document.getElementById('note-preview').classList.add('hidden');
            initTeacherDashboard();
        } else { alert('Delete failed.'); }
    } catch(e) { alert('Network error.'); }
}
async function generateQuestionsFromNote() {
    if (!currentNoteId) return;
    console.log('generateQuestionsFromNote: currentNoteId=', currentNoteId, 'currentNote=', currentNote?.topic, 'difficulty=', currentNote?.difficulty);
    generatingFromNote = true;
    document.getElementById('q-subject').value = document.getElementById('note-subject').value;
    document.getElementById('q-class').value = document.getElementById('note-class').value;
    document.getElementById('q-term').value = document.getElementById('note-term').value;
    document.getElementById('q-week').value = document.getElementById('note-week').value;
    document.getElementById('q-topic').value = document.getElementById('note-topic').value;
    document.getElementById('q-subtopic').value = document.getElementById('note-subtopic').value;
    document.getElementById('q-count').value = 20;
    switchTab('questions');
}

// ====== SPEECH-TO-TEXT VOICE INPUT ======
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
        btn.className = 'inline-flex items-center justify-center opacity-60 text-slate-400 cursor-help p-1 rounded-md bg-slate-100';
        btn.title = 'Speech not supported';
        container.appendChild(btn);
        return;
    }
    let recognition = null, listening = false;
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>';
    btn.className = 'inline-flex items-center justify-center transition-all cursor-pointer border-none shrink-0 bg-slate-100 text-slate-600 hover:bg-slate-200 p-1 rounded-md';
    btn.title = 'Use speech-to-text (microphone input)';
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
    function resetBtn() { btn.className = 'inline-flex items-center justify-center transition-all cursor-pointer border-none shrink-0 bg-slate-100 text-slate-600 hover:bg-slate-200 p-1 rounded-md'; btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>'; }
    container.appendChild(btn);
}

// ====== READ ALOUD ======
let speechSynth = window.speechSynthesis;
let speechUtterance = null;

function readAloud(elementId) {
    if (speechUtterance && speechSynthesis.speaking) {
        if (speechSynthesis.paused) { speechSynthesis.resume(); return; }
        speechSynthesis.cancel(); return;
    }
    const text = document.getElementById(elementId).innerText;
    if (!text) return;
    speechUtterance = new SpeechSynthesisUtterance(text);
    speechUtterance.lang = 'en-GB';
    speechUtterance.rate = 0.9;
    speechUtterance.pitch = 1;
    speechSynthesis.speak(speechUtterance);
}

// ====== QUESTIONS ======
document.getElementById('questions-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('q-submit-btn');
    const errEl = document.getElementById('q-error');
    if (errEl) { errEl.classList.add('hidden'); errEl.textContent = ''; }
    btn.disabled = true; btn.textContent = 'Generating...';
    const payload = {
        subject: document.getElementById('q-subject').value,
        topic: document.getElementById('q-topic').value,
        subTopic: document.getElementById('q-subtopic').value,
        class: document.getElementById('q-class').value,
        term: document.getElementById('q-term').value,
        week: parseInt(document.getElementById('q-week').value) || 1,
        count: parseInt(document.getElementById('q-count').value),
        includeTheory: document.getElementById('q-theory').checked,
        difficulty: document.getElementById('q-difficulty')?.value || '',
    };
    if (generatingFromNote && currentNote) {
        payload.lessonNoteId = currentNoteId;
        payload.noteContent = JSON.stringify(currentNote);
        payload.difficulty = currentNote.difficulty || '';
        generatingFromNote = false;
        // Clear note refs so manual generations don't re-use note context
        currentNoteId = null;
        currentNote = null;
    }
    try {
        const res = await fetch('/api/ai/questions', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            currentQuestions = data.questions;
            displayQuestions(data.questions);
            document.getElementById('q-save-section').classList.remove('hidden');
            document.getElementById('q-save-msg').textContent = data.message;
        } else {
            const errMsg = data.error || data.message || 'Generation failed.';
            if (errEl) {
                errEl.textContent = errMsg + (res.status !== 200 ? ' (HTTP ' + res.status + ')' : '');
                errEl.classList.remove('hidden');
            } else { alert(errMsg); }
        }
    } catch(e) {
        if (errEl) { errEl.textContent = 'Network error: ' + e.message; errEl.classList.remove('hidden'); }
        else { alert('Network error: ' + e.message); }
    }
    finally { btn.disabled = false; btn.textContent = 'Generate Questions'; }
});

function displayQuestions(qs) {
    document.getElementById('q-preview').classList.remove('hidden');
    const container = document.getElementById('q-content');
    const objectives = Array.isArray(qs) ? qs : (qs.objectives || []);
    const theory = qs.theoryQuestions || [];
    const essay = qs.essayQuestions || [];
    const structured = qs.structuredQuestions || [];

    let html = '';
    if (objectives.length) {
        html += `<h3 class="text-base font-bold text-slate-800 mb-3">Objective Questions (${objectives.length})</h3>`;
        html += objectives.map((q, idx) => `
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg mb-2">
                <p class="text-sm font-medium mb-1">${q.id || (idx + 1)}. ${renderMath(q.question || q.text || '')}</p>
                <ul class="text-xs text-slate-600 grid grid-cols-2 gap-1 pl-4">
                    <li>A. ${renderMath(q.A || q.option_a || q.options?.A || '')}</li>
                    <li>B. ${renderMath(q.B || q.option_b || q.options?.B || '')}</li>
                    <li>C. ${renderMath(q.C || q.option_c || q.options?.C || '')}</li>
                    <li>D. ${renderMath(q.D || q.option_d || q.options?.D || '')}</li>
                </ul>
                <p class="text-xs text-blue-600 font-bold mt-1">Answer: ${q.answer || q.correctAnswer || q.correct_answer || ''}</p>
            </div>
        `).join('');
    }
    if (theory.length) {
        html += `<h3 class="text-base font-bold text-slate-800 mt-4 mb-3">Theory Questions</h3>`;
        html += theory.map(q => `<div class="p-3 bg-slate-800/10 border border-slate-800/20 rounded-lg mb-2"><p class="text-sm font-medium">${renderMath(q.question)}</p><p class="text-xs text-slate-500 mt-1">Model Answer: ${renderMath(q.answer || '')}</p></div>`).join('');
    }
    if (essay.length) {
        html += `<h3 class="text-base font-bold text-slate-800 mt-4 mb-3">Essay Questions</h3>`;
        html += essay.map(q => `<div class="p-3 bg-blue-50 border border-blue-200 rounded-lg mb-2"><p class="text-sm font-medium">${renderMath(q.question)}</p><p class="text-xs text-slate-500 mt-1">Guidance: ${renderMath(q.guidance || '')}</p></div>`).join('');
    }
    if (structured.length) {
        html += `<h3 class="text-base font-bold text-slate-800 mt-4 mb-3">Structured Questions</h3>`;
        html += structured.map(q => `<div class="p-3 bg-blue-50 border border-blue-200 rounded-lg mb-2"><p class="text-sm font-medium">${renderMath(q.question)}</p>${q.parts ? Object.entries(q.parts).map(([k,v]) => `<p class="text-xs text-slate-600 ml-2">(${k}) ${renderMath(v)}</p>`).join('') : ''}</div>`).join('');
    }
    container.innerHTML = html || '<p class="text-sm text-slate-500">No questions generated.</p>';

    document.getElementById('q-action-buttons').innerHTML = `
        <button onclick="copyQuestions()" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 cursor-pointer">Copy</button>
        <button onclick="shareQuestions()" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 cursor-pointer">Share</button>
        <button onclick="printQuestions()" class="px-3 py-1.5 bg-gray-800 text-white text-xs font-bold rounded-lg hover:bg-gray-900 cursor-pointer">Print</button>
        <button onclick="readAloud('q-content')" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 cursor-pointer">Read Aloud</button>
        <button onclick="convertToCBT()" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-700 cursor-pointer">Convert to CBT</button>
    `;
}

async function saveQuestions() {
    if (!currentQuestions) return;
    try {
        const res = await fetch('/api/questions/save', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({
                subject: document.getElementById('q-subject').value,
                topic: document.getElementById('q-topic').value,
                subTopic: document.getElementById('q-subtopic').value,
                questions: Array.isArray(currentQuestions) ? currentQuestions : (currentQuestions.objectives || []),
            })
        });
        const data = await res.json();
        if (data.success) {
            currentQsId = data.questionSetId;
            document.getElementById('q-save-msg').textContent = 'Questions saved! You can now convert to CBT.';
            initTeacherDashboard();
        } else { alert(data.error || 'Save failed.'); }
    } catch(e) { alert('Network error.'); }
}

async function convertToCBT() {
    let qsId = currentQsId;
    if (!qsId && currentQuestions) {
        await saveQuestions();
        qsId = currentQsId;
    }
    if (!qsId) { alert('Please save questions first.'); return; }
    try {
        const questionCount = Array.isArray(currentQuestions) ? currentQuestions.length : (currentQuestions?.objectives?.length || 20);
        const duration = prompt('Exam duration in minutes:', Math.max(10, Math.min(60, Math.floor(questionCount / 2))));
        if (!duration) return;
        const marks = prompt('Default marks per question:', '1');
        if (!marks) return;
        const res = await fetch('/api/questions/convert-to-exam', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({
                questionSetId: qsId,
                title: document.getElementById('q-subject').value + ' CBT Exam',
                duration: parseInt(duration),
                defaultMarks: parseInt(marks),
            })
        });
        const data = await res.json();
        if (data.success) {
            alert('Exam created! ' + data.message);
            initTeacherDashboard();
            switchTab('cbt-engine');
        } else { alert(data.error || 'Conversion failed.'); }
    } catch(e) { alert('Network error.'); }
}

function copyQuestions() {
    navigator.clipboard.writeText(document.getElementById('q-content').innerText).then(() => alert('Questions copied!')).catch(() => {});
}
function shareQuestions() {
    const text = document.getElementById('q-content').innerText;
    if (navigator.share) navigator.share({ title: 'Questions', text }).catch(() => {});
    else { copyQuestions(); alert('Content copied for sharing!'); }
}
function printQuestions() { window.print(); }

// ====== SUBJECT FILTERS ======
function renderSubjectFilter(containerId, allItems, currentFilter, setFilter, renderFn) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const subs = [...new Set(allItems.map(i => i.subject).filter(Boolean))];
    const allBtn = `<button onclick="setFilter('');${renderFn}()" class="py-1.5 px-3 rounded-lg text-xs font-bold whitespace-nowrap transition cursor-pointer border ${!currentFilter ? 'bg-blue-600 text-white border-blue-700' : 'bg-blue-100 text-blue-800 border-blue-200 hover:bg-blue-200'}">All</button>`;
    const btns = subs.map(s => `<button onclick="setFilter('${s}');${renderFn}()" class="py-1.5 px-3 rounded-lg text-xs font-bold whitespace-nowrap transition cursor-pointer border ${currentFilter === s ? 'bg-blue-600 text-white border-blue-700' : 'bg-blue-100 text-blue-800 border-blue-200 hover:bg-blue-200'}">${s}</button>`);
    container.innerHTML = allBtn + btns.join('');
}

function setPlansFilter(s) { plansFilter = s; }
function setNotesFilter(s) { notesFilter = s; }
function setQsFilter(s) { qsFilter = s; }

function renderPlanFilters() { renderSubjectFilter('plans-subjects-scroll', teacherData.plans, plansFilter, 'setPlansFilter', 'renderPlans'); }
function renderNoteFilters() { renderSubjectFilter('notes-subjects-scroll', teacherData.notes, notesFilter, 'setNotesFilter', 'renderNotes'); }
function renderQsFilters() { renderSubjectFilter('qs-subjects-scroll', teacherData.questionSets, qsFilter, 'setQsFilter', 'renderQuestionSets'); }

// ====== RENDER SAVED ITEMS ======
function renderPlans() {
    const container = document.getElementById('plans-list');
    const filtered = plansFilter ? teacherData.plans.filter(p => p.subject === plansFilter) : teacherData.plans;
    if (!filtered.length) {
        container.innerHTML = '<div class="text-center py-4 text-sm text-slate-500">No lesson plans yet.</div>';
        return;
    }
    container.innerHTML = filtered.slice().reverse().map(p => {
        const objs = p.behaviouralObjectives || [];
        const subHtml = objs.length ? `<div class="flex flex-wrap gap-1 mt-1.5">${objs.slice(0, 3).map(o => `<span class="text-[10px] bg-blue-50 text-slate-800 px-2 py-0.5 rounded-full border border-blue-100">${o.replace(/^By the end of the lesson, students should be able to /i, '').replace(/^Students will /i, '').substring(0, 40)}</span>`).join('')}${objs.length > 3 ? `<span class="text-[10px] text-slate-400">+${objs.length - 3} more</span>` : ''}</div>` : '';
        return `
        <div class="flex items-start gap-2 p-3 bg-slate-50 border border-slate-200 rounded-lg cursor-pointer hover:border-blue-600 transition" onclick="viewPlan('${p.id}')">
            <div class="flex-1 min-w-0">
                <div class="font-medium text-sm text-slate-900">${p.topic || 'Lesson Plan'}</div>
                <div class="text-xs text-slate-400">${p.subject || ''} | ${p.class || ''} | Week ${p.week || ''} | ${p.createdAt ? new Date(p.createdAt).toLocaleDateString() : ''}</div>
                ${subHtml}
            </div>
            <button onclick="event.stopPropagation();deletePlan('${p.id}')" class="shrink-0 p-1.5 bg-white border border-slate-200 rounded-lg hover:bg-red-100 hover:border-red-500 hover:text-red-600 text-slate-500 transition cursor-pointer" title="Delete">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </div>`;
    }).join('');
}

function deletePlan(id) {
    if (!confirm('Delete this lesson plan?')) return;
    fetch('/api/lesson-plans/' + id, { method: 'DELETE' }).then(r => r.json()).then(d => {
        if (d.success) { teacherData.plans = teacherData.plans.filter(p => p.id !== id); renderPlans(); }
    }).catch(() => alert('Failed to delete.'));
}

function viewPlan(id) {
    const plan = teacherData.plans.find(p => p.id === id);
    if (plan) { currentPlanId = id; displayLessonPlan(plan); }
}

function renderNotes() {
    const container = document.getElementById('notes-list');
    const filtered = notesFilter ? teacherData.notes.filter(n => n.subject === notesFilter) : teacherData.notes;
    if (!filtered.length) {
        container.innerHTML = '<div class="text-center py-4 text-sm text-slate-500">No lesson notes yet.</div>';
        return;
    }
    container.innerHTML = filtered.slice().reverse().map(n => {
        const subs = n.subtopics || [];
        const subHtml = subs.length ? `<div class="flex flex-wrap gap-1 mt-1.5">${subs.slice(0, 3).map(s => `<span class="text-[10px] bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full border border-slate-300">${s}</span>`).join('')}${subs.length > 3 ? `<span class="text-[10px] text-slate-400">+${subs.length - 3} more</span>` : ''}</div>` : '';
        return `
        <div class="flex items-start gap-2 p-3 bg-slate-50 border border-slate-200 rounded-lg cursor-pointer hover:border-blue-600 transition" onclick="viewNote('${n.id}')">
            <div class="flex-1 min-w-0">
                <div class="font-medium text-sm text-slate-900">${n.topic || 'Lesson Note'}</div>
                <div class="text-xs text-slate-400">${n.subject || ''} | ${n.class || ''} | ${n.createdAt ? new Date(n.createdAt).toLocaleDateString() : ''}</div>
                ${subHtml}
            </div>
            <button onclick="event.stopPropagation();deleteNote('${n.id}')" class="shrink-0 p-1.5 bg-white border border-slate-200 rounded-lg hover:bg-red-100 hover:border-red-500 hover:text-red-600 text-slate-500 transition cursor-pointer" title="Delete">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </div>`;
    }).join('');
}

function deleteNote(id) {
    if (!confirm('Delete this lesson note?')) return;
    fetch('/api/lesson-notes/' + id, { method: 'DELETE' }).then(r => r.json()).then(d => {
        if (d.success) { teacherData.notes = teacherData.notes.filter(n => n.id !== id); renderNotes(); }
    }).catch(() => alert('Failed to delete.'));
}

function viewNote(id) {
    const note = teacherData.notes.find(n => n.id === id);
    if (note) { currentNoteId = id; currentNote = note; displayLessonNote(note); }
}

function renderQuestionSets() {
    const container = document.getElementById('q-sets-list');
    const filtered = qsFilter ? teacherData.questionSets.filter(q => q.subject === qsFilter) : teacherData.questionSets;
    if (!filtered.length) {
        container.innerHTML = '<div class="text-center py-4 text-sm text-slate-500">No question sets saved yet.</div>';
        return;
    }
    container.innerHTML = filtered.slice().reverse().map(q => `
        <div class="flex items-start gap-2 p-3 bg-slate-50 border border-slate-200 rounded-lg cursor-pointer hover:border-blue-600 transition" onclick="viewQuestionSet('${q.id}')">
            <div class="flex-1 min-w-0">
                <div class="font-medium text-sm text-slate-900">${q.topic || 'Question Set'}</div>
                <div class="text-xs text-slate-400">${q.subject || ''} | ${(q.questions || []).length} questions | ${q.createdAt ? new Date(q.createdAt).toLocaleDateString() : ''}</div>
            </div>
            <button onclick="event.stopPropagation();deleteQuestionSet('${q.id}')" class="shrink-0 p-1.5 bg-white border border-slate-200 rounded-lg hover:bg-red-100 hover:border-red-500 hover:text-red-600 text-slate-500 transition cursor-pointer" title="Delete question set">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </div>
    `).join('');
}

function deleteQuestionSet(id) {
    if (!confirm('Delete this question set? This will permanently remove the generated questions.')) return;
    fetch('/api/questions/sets/' + id, { method: 'DELETE' }).then(r => r.json()).then(d => {
        if (d.success) { teacherData.questionSets = teacherData.questionSets.filter(q => q.id !== id); renderQuestionSets(); }
        else alert(d.error || 'Failed to delete.');
    }).catch(() => alert('Failed to delete.'));
}

function viewQuestionSet(id) {
    const qs = teacherData.questionSets.find(q => q.id === id);
    if (!qs) return;
    currentQsId = id;
    currentQuestions = qs.questions || [];
    const data = qs.questions || [];
    if (Array.isArray(data) && data.length && !data.objectives) {
        displayQuestions({ objectives: data });
    } else {
        displayQuestions(data);
    }
    document.getElementById('q-save-section').classList.remove('hidden');
    document.getElementById('q-save-msg').textContent = 'Questions loaded from pool.';
}

function renderExams() {
    const container = document.getElementById('cbt-exams-list');
    if (!teacherData.exams.length) {
        container.innerHTML = '<div class="text-center py-8 text-sm text-slate-500 col-span-2">No exams created yet.</div>';
        return;
    }
    container.innerHTML = teacherData.exams.slice().reverse().map(e => {
        const examLink = window.location.origin + '/student/exam/' + e.id;
        const marks = e.defaultMarks || 5;
        return `<div class="border border-slate-300 rounded-xl overflow-hidden shadow-sm">
            <div class="px-4 py-3 flex items-center justify-between gap-2 ${e.isPublished ? 'bg-blue-600' : 'bg-red-600'}">
                <div class="min-w-0">
                    <h5 class="font-bold text-sm text-white truncate">${e.title || 'Exam'}</h5>
                    <p class="text-xs text-white/80">${e.subject || ''} | ${e.questions?.length || 0} questions | ${e.duration || 0} min | ${marks} mark(s) per Q</p>
                </div>
                <span class="px-2.5 py-1 rounded text-[10px] font-bold uppercase whitespace-nowrap shrink-0 ${e.isPublished ? 'bg-blue-600 text-white' : 'bg-red-600 text-white'}">${e.isPublished ? 'Live' : 'Draft'}</span>
            </div>
            <div class="p-3 bg-white flex flex-wrap gap-1.5">
                <button onclick="publishExam('${e.id}')" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-600 text-white text-[11px] font-bold rounded-lg cursor-pointer shadow-sm">${e.isPublished ? 'Unpublish' : 'Publish'}</button>
                <button onclick="copyExamLink('${examLink}')" class="px-3 py-1.5 bg-blue-600 text-white text-[11px] font-bold rounded-lg cursor-pointer shadow-sm">Copy Link</button>
                <button onclick="openExamSettings('${e.id}')" class="px-3 py-1.5 bg-blue-600 text-white text-[11px] font-bold rounded-lg cursor-pointer shadow-sm">Settings</button>
                <button onclick="window.open('/api/download/exam/${e.id}/pdf','_blank')" class="px-3 py-1.5 bg-red-600 hover:bg-red-600 text-white text-[11px] font-bold rounded-lg cursor-pointer shadow-sm">PDF</button>
                <button onclick="window.open('/api/download/exam/${e.id}/docx','_blank')" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-600 text-white text-[11px] font-bold rounded-lg cursor-pointer shadow-sm">DOCX</button>
                <button onclick="deleteExam('${e.id}')" class="px-3 py-1.5 bg-red-600 hover:bg-red-600 text-white text-[11px] font-bold rounded-lg cursor-pointer shadow-sm">Delete</button>
            </div>
        </div>`;
    }).join('');
}

async function publishExam(id) {
    try {
        const res = await fetch('/api/exams/' + id + '/publish', { method: 'POST', headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (data.success) initTeacherDashboard();
    } catch(e) {}
}

function copyExamLink(link) {
    navigator.clipboard.writeText(link).then(() => alert('Exam link copied: ' + link)).catch(() => {});
}

async function deleteExam(id) {
    if (!confirm('Delete this exam?')) return;
    try {
        const res = await fetch('/api/exams/' + id, { method: 'DELETE', headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (data.success) initTeacherDashboard();
    } catch(e) {}
}

// ====== RESULTS PAGINATION STATE ======
let resultsPage = 1;
const RESULTS_PAGE_SIZE = 50;

function renderResults() {
    const container = document.getElementById('results-list');
    if (!teacherData.results.length) {
        container.innerHTML = '<div class="text-center py-8 text-sm text-slate-500">No results yet.</div>';
        document.getElementById('results-pagination').innerHTML = '';
        document.getElementById('results-pagination-bottom').innerHTML = '';
        return;
    }

    // Group results by exam
    const examMap = {};
    teacherData.results.forEach(r => {
        const examId = r.examId || 'unknown';
        if (!examMap[examId]) {
            const exam = teacherData.exams.find(e => e.id === examId);
            examMap[examId] = {
                examId: examId,
                examTitle: r.examTitle || 'Unknown Exam',
                subject: r.subject || '',
                level: exam ? (exam.level || '') : '',
                createdAt: exam ? (exam.createdAt || '') : '',
                results: [],
                _accordionId: 'accordion-' + examId.replace(/[^a-zA-Z0-9-_]/g, '_')
            };
        }
        examMap[examId].results.push(r);
    });

    const examIds = Object.keys(examMap);
    if (!examIds.length) {
        container.innerHTML = '<div class="text-center py-8 text-sm text-slate-500">No results yet.</div>';
        document.getElementById('results-pagination').innerHTML = '';
        document.getElementById('results-pagination-bottom').innerHTML = '';
        return;
    }

    // Paginate exam groups
    const totalPages = Math.ceil(examIds.length / RESULTS_PAGE_SIZE);
    if (resultsPage > totalPages) resultsPage = totalPages;
    if (resultsPage < 1) resultsPage = 1;
    const pageStart = (resultsPage - 1) * RESULTS_PAGE_SIZE;
    const pageExamIds = examIds.slice(pageStart, pageStart + RESULTS_PAGE_SIZE);

    // Render pagination controls
    function renderPagination(containerId) {
        const el = document.getElementById(containerId);
        if (totalPages <= 1) { el.innerHTML = ''; return; }
        let html = '';
        if (resultsPage > 1) html += `<button onclick="resultsPage=${resultsPage - 1};renderResults()" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-xs cursor-pointer">&laquo; Prev</button>`;
        html += `<span class="text-xs text-slate-500">Page ${resultsPage} of ${totalPages} (${examIds.length} exam groups)</span>`;
        if (resultsPage < totalPages) html += `<button onclick="resultsPage=${resultsPage + 1};renderResults()" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-xs cursor-pointer">Next &raquo;</button>`;
        el.innerHTML = html;
    }
    renderPagination('results-pagination');
    renderPagination('results-pagination-bottom');

    container.innerHTML = pageExamIds.map(examId => {
        const group = examMap[examId];
        const accordionId = group._accordionId;
        const results = group.results;
        const count = results.length;
        const avgPercentage = count > 0 ? Math.round(results.reduce((sum, r) => sum + (r.percentage || 0), 0) / count) : 0;
        const bestScore = results.length > 0 ? Math.max(...results.map(r => r.percentage || 0)) : 0;
        const passedCount = results.filter(r => (r.percentage || 0) >= 50).length;
        const totalStudents = results.length;

        const studentsHtml = results.map(r => {
            const pct = r.percentage || 0;
            const isPassed = pct >= 50;
            const dateStr = r.date ? new Date(r.date).toLocaleString(undefined, { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';
            const timeSpent = r.timeSpent || 0;
            const timeStr = timeSpent < 60 ? timeSpent + 's' : Math.floor(timeSpent / 60) + 'm ' + (timeSpent % 60) + 's';
            const grade = pct >= 75 ? 'A' : pct >= 60 ? 'B' : pct >= 50 ? 'C' : pct >= 40 ? 'D' : 'F';
            const gradeColors = { 'A': 'bg-emerald-100 text-emerald-700', 'B': 'bg-blue-100 text-blue-700', 'C': 'bg-amber-100 text-amber-700', 'D': 'bg-orange-100 text-orange-700', 'F': 'bg-red-100 text-red-700' };
            const safeId = (r.id || '').replace(/[^a-zA-Z0-9-_]/g, '_');
            const safeExamId = (r.examId || '').replace(/[^a-zA-Z0-9-_]/g, '_');
            const waLink = whatsappResultLink(r);

            return `<div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 bg-white border border-slate-100 rounded-lg hover:border-slate-200 hover:shadow-sm hover:cursor-pointer transition-all gap-2" data-view-result="${safeId}" onclick="if(!event.target.closest('button'))viewResult('${safeId}')" title="Click to view result">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-semibold text-sm text-slate-900">${r.studentName || 'Student'}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded font-bold ${isPassed ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'}">${isPassed ? 'Pass' : 'Fail'}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded font-bold ${gradeColors[grade] || 'bg-slate-200 text-slate-700'}">Grade ${grade}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-200 text-slate-700 font-medium">${r.subject || ''}</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-slate-400 mt-0.5">
                        <span>Score: <strong class="text-slate-600">${r.score || 0}/${r.totalPossibleMarks || r.totalQuestions || 0}</strong></span>
                        <span>${dateStr}</span>
                        ${timeStr ? `<span>Time: ${timeStr}</span>` : ''}
                        <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 font-semibold text-[10px]">${r.totalQuestions || 0} Qs</span>
                        <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-600 font-semibold text-[10px]">Submitted</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold ${isPassed ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'bg-red-800/10 text-red-700 border border-red-700/20'}">${pct}%</span>
                    <button data-view-result="${safeId}" onclick="event.stopPropagation();viewResult('${safeId}')" class="px-3 py-1.5 bg-blue-600 text-white text-[11px] font-bold rounded-lg transition-all duration-200 cursor-pointer whitespace-nowrap flex items-center gap-1.5 shadow-md hover:shadow-lg border-2 border-white" title="View Result">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>View</span>
                    </button>
                    ${waLink ? `<button data-wa-result="${safeId}" onclick="event.stopPropagation();window.open('${waLink}','_blank')" class="px-3 py-1.5 bg-emerald-600 text-white text-[11px] font-bold rounded-lg transition-all duration-200 cursor-pointer whitespace-nowrap flex items-center gap-1.5 shadow-md hover:shadow-lg border-2 border-white" title="Send this result to WhatsApp">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        <span>WhatsApp</span>
                    </button>` : ''}
                    <button data-download-script="${safeId}" data-exam-id="${safeExamId}" onclick="event.stopPropagation();downloadGradedScript('${safeExamId}', '${safeId}')" class="px-3 py-1.5 bg-red-600 text-white text-[11px] font-bold rounded-lg transition-all duration-200 cursor-pointer whitespace-nowrap flex items-center gap-1.5 shadow-md hover:shadow-lg border-2 border-white" title="Download Graded Script PDF">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Script</span>
                    </button>
                </div>
            </div>`;
        }).join('');

        return `<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            <button onclick="toggleAccordion('${accordionId}')" class="w-full flex items-center justify-between p-4 hover:bg-slate-50 transition cursor-pointer">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-slate-800 to-blue-600 flex items-center justify-center text-white font-bold text-sm shrink-0">${(group.subject || '?').charAt(0).toUpperCase()}</div>
                    <div class="min-w-0">
                        <h4 class="font-bold text-sm text-slate-900 truncate">${group.examTitle}</h4>
                        <p class="text-xs text-slate-400 truncate">${group.subject}${group.level ? ' | ' + group.level : ''} | ${count} student${count > 1 ? 's' : ''} | ${passedCount}/${totalStudents} passed</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <div class="hidden md:flex items-center gap-2 text-xs">
                        <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-600 font-semibold whitespace-nowrap">Best: ${bestScore}%</span>
                        <span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-600 font-semibold whitespace-nowrap">Avg: ${avgPercentage}%</span>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 chevron transition transform duration-200 shrink-0" id="${accordionId}-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </button>
            <div id="${accordionId}-body" class="hidden border-t border-slate-100">
                <div class="p-3 space-y-2 bg-slate-50/50">
                    ${studentsHtml}
                </div>
            </div>
        </div>`;
    }).join('');
}

function toggleAccordion(accordionId) {
    const body = document.getElementById(accordionId + '-body');
    const chevron = document.getElementById(accordionId + '-chevron');
    if (body) body.classList.toggle('hidden');
    if (chevron) chevron.classList.toggle('rotate-180');
}

function downloadGradedScript(examId, resultId) {
    const url = '/api/download/graded-script/' + examId + '/' + resultId;
    window.open(url, '_blank');
}

function viewResult(resultId) {
    if (!resultId) return;
    const modal = document.getElementById('result-modal');
    const body = document.getElementById('result-modal-body');
    const title = document.getElementById('result-modal-title');
    const subtitle = document.getElementById('result-modal-subtitle');
    const downloadBtn = document.getElementById('result-modal-download-btn');
    const waBtn = document.getElementById('result-modal-wa-btn');

    if (!modal || !body || !title) return;

    body.innerHTML = '<div class="text-center py-8 text-sm text-slate-500"><div class="animate-spin inline-block w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full mb-2"></div><br>Loading result...</div>';
    title.textContent = 'Loading...';
    subtitle.textContent = '';
    if (downloadBtn) downloadBtn.classList.add('hidden');
    if (waBtn) waBtn.classList.add('hidden');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    const url = '/api/results/' + encodeURIComponent(resultId);

    fetch(url)
        .then(r => {
            if (!r.ok) throw new Error('Server returned ' + r.status + ' ' + r.statusText);
            return r.json();
        })
        .then(data => {
            if (!data.success || !data.result) {
                body.innerHTML = '<div class="text-center py-8"><p class="text-sm font-bold text-rose-600">Result not found.</p><p class="text-xs text-slate-500 mt-1">The result may have been deleted or you may not have permission to view it. (ID: ' + resultId + ')</p></div>';
                title.textContent = 'Error';
                return;
            }
            const r = data.result;
            const exam = data.exam;
            const pct = r.percentage || 0;
            const isPassed = pct >= 50;
            const grade = pct >= 75 ? 'A' : pct >= 60 ? 'B' : pct >= 50 ? 'C' : pct >= 40 ? 'D' : 'F';

            title.textContent = r.studentName || 'Student Result';
            subtitle.textContent = (r.examTitle || '') + ' | ' + (r.subject || '') + ' | ' + grade;

            let html = '';

            html += `<div class="p-4 sm:p-6 rounded-2xl mb-4 text-white ${isPassed ? 'bg-gradient-to-br from-emerald-600 to-emerald-800' : 'bg-gradient-to-br from-rose-600 to-rose-800'}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider opacity-80 font-bold">${r.subject || ''}</p>
                        <h4 class="text-lg font-black">${r.examTitle || ''}</h4>
                        <p class="text-xs opacity-80">${r.studentName || ''} ${r.studentId ? '&middot; ID: ' + r.studentId : ''}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-black">${pct}%</p>
                        <p class="text-xs font-bold uppercase ${isPassed ? 'text-emerald-200' : 'text-rose-200'}">${isPassed ? 'Passed' : 'Failed'} &middot; Grade ${grade}</p>
                    </div>
                </div>
            </div>`;

            html += `<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                    <p class="text-[10px] text-slate-500 font-bold uppercase">Score</p>
                    <p class="text-lg font-black text-slate-900">${r.score || 0} / ${r.totalPossibleMarks || r.totalQuestions || 0}</p>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                    <p class="text-[10px] text-slate-500 font-bold uppercase">Correct</p>
                    <p class="text-lg font-black text-slate-900">${r.correctAnswers || 0} / ${r.totalQuestions || 0}</p>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                    <p class="text-[10px] text-slate-500 font-bold uppercase">Grade</p>
                    <p class="text-lg font-black text-slate-900">${grade}</p>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                    <p class="text-[10px] text-slate-500 font-bold uppercase">Date</p>
                    <p class="text-xs font-bold text-slate-900">${r.date ? new Date(r.date).toLocaleDateString() : 'N/A'}</p>
                </div>
            </div>`;

            const questions = exam ? (exam.questions || []) : [];
            const failed = r.failedQuestions || [];
            if (failed.length > 0 || questions.length > 0) {
                html += `<h4 class="text-sm font-bold text-slate-800 mb-3">Answer Review</h4>`;
                const reviewItems = failed.length > 0 ? failed : questions.map((q, i) => ({
                    question: q.question || '',
                    optionA: q.optionA || q.options?.A || '',
                    optionB: q.optionB || q.options?.B || '',
                    optionC: q.optionC || q.options?.C || '',
                    optionD: q.optionD || q.options?.D || '',
                    selectedAnswer: null,
                    correctAnswer: q.correctAnswer || q.answer || '',
                    isCorrect: false,
                    explanation: q.explanation || '',
                    marks: q.marks || 5,
                }));

                reviewItems.forEach((item, idx) => {
                    const isCorrect = item.selectedAnswer === item.correctAnswer;
                    const isNotAnswered = !item.selectedAnswer;
                    let badgeColor, badgeText;
                    if (isCorrect) { badgeColor = 'bg-emerald-50 text-emerald-700 border-emerald-200'; badgeText = 'Correct'; }
                    else if (isNotAnswered) { badgeColor = 'bg-amber-50 text-amber-700 border-amber-200'; badgeText = 'Not Answered'; }
                    else { badgeColor = 'bg-rose-50 text-rose-700 border-rose-200'; badgeText = 'Wrong'; }

                    html += `<div class="p-3 sm:p-4 border border-slate-200 rounded-xl mb-2">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-[10px] bg-slate-100 text-slate-600 py-0.5 px-2 rounded-full font-bold">Q${idx + 1}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border ${badgeColor}">${badgeText}</span>
                        </div>
                        <p class="text-sm font-bold text-slate-800 mb-2">${item.question || ''}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">`;
                    ['A','B','C','D'].forEach(k => {
                        const optLabel = item['option' + k] || item[k] || (item.options && item.options[k]) || '';
                        if (!optLabel) return;
                        const isCorrectOpt = k === item.correctAnswer;
                        const isSelectedOpt = k === (item.selectedAnswer || '');
                        let optClass = 'border-slate-200 bg-white';
                        let markerClass = 'bg-slate-200 text-slate-700';
                        let badge = '';
                        if (isCorrectOpt) { optClass = 'border-emerald-300 bg-emerald-50 ring-1 ring-emerald-200'; markerClass = 'bg-emerald-500 text-white'; badge = '<span class="text-[10px] font-bold text-emerald-600 shrink-0 ml-auto">&#10003; Correct</span>'; }
                        else if (isSelectedOpt && !isCorrectOpt) { optClass = 'border-rose-300 bg-rose-50 ring-1 ring-rose-200'; markerClass = 'bg-rose-500 text-white'; badge = '<span class="text-[10px] font-bold text-rose-600 shrink-0 ml-auto">&#10007; Your Answer</span>'; }
                        html += `<div class="p-2.5 border rounded-lg text-xs font-semibold flex items-center gap-2 ${optClass}"><span class="w-6 h-6 rounded-lg flex items-center justify-center font-bold shrink-0 text-[10px] ${markerClass}">${k}</span><span class="break-words min-w-0 flex-1">${optLabel}</span>${badge}</div>`;
                    });
                    html += `</div>`;
                    if (item.explanation) {
                        html += `<div class="mt-2 p-2.5 bg-indigo-50 border border-indigo-100 rounded-lg flex items-start gap-2"><svg class="w-3.5 h-3.5 text-indigo-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><p class="text-xs text-slate-700"><strong class="text-indigo-700">Explanation:</strong> ${item.explanation}</p></div>`;
                    }
                    html += `</div>`;
                });
            }

            body.innerHTML = html;

            if (downloadBtn) {
                downloadBtn.classList.remove('hidden');
                downloadBtn.onclick = function() { downloadGradedScript(r.examId, r.id); };
            }
            if (waBtn) {
                const waLink = whatsappResultLink(r);
                if (waLink) {
                    waBtn.classList.remove('hidden');
                    waBtn.onclick = function() { window.open(waLink, '_blank'); };
                }
            }
        })
        .catch(err => {
            body.innerHTML = '<div class="text-center py-8"><p class="text-sm font-bold text-rose-600">Failed to load result.</p><p class="text-xs text-slate-500 mt-1">' + err.message + '</p></div>';
            title.textContent = 'Error';
        });
}

function closeResultModal() {
    document.getElementById('result-modal').classList.add('hidden');
    document.getElementById('result-modal-body').innerHTML = '';
    document.body.style.overflow = '';
}

// ====== CSV IMPORT ======
let csvFile = null, csvPreviewData = null, csvPage = 1, csvPageSize = 25;

function openCsvImport() {
    document.getElementById('csv-import-modal').classList.remove('hidden');
    document.getElementById('csv-import-step-label').textContent = 'Step 1 of 3: Upload File';
    document.getElementById('csv-step-1').classList.remove('hidden');
    document.getElementById('csv-step-2').classList.add('hidden');
    document.getElementById('csv-step-3').classList.add('hidden');
    document.getElementById('csv-footer-step-1').classList.remove('hidden');
    document.getElementById('csv-footer-step-2').classList.add('hidden');
    document.getElementById('csv-footer-step-3').classList.add('hidden');
    csvFile = null;
    csvPreviewData = null;
    document.getElementById('csv-file-input').value = '';
    document.getElementById('csv-file-info').classList.add('hidden');
    document.getElementById('csv-preview-btn').disabled = true;
    document.getElementById('csv-preview-btn').className = 'w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg opacity-60 cursor-not-allowed transition shadow-sm';
    document.body.style.overflow = 'hidden';
    // Populate selects from existing curriculum data
    const subjects = document.querySelectorAll('#plan-subject option');
    const subjectSelect = document.getElementById('csv-subject');
    if (subjectSelect.options.length <= 1) {
        [...subjects].forEach(opt => {
            if (opt.value) subjectSelect.add(new Option(opt.value, opt.value));
        });
    }
    ['csv-class', 'csv-term'].forEach(id => {
        const src = document.getElementById(id === 'csv-class' ? 'plan-class' : 'plan-term');
        const tgt = document.getElementById(id);
        if (src && tgt && tgt.options.length <= 1) {
            [...src.options].forEach(opt => {
                if (opt.value) tgt.add(new Option(opt.value, opt.value));
            });
        }
    });
}

function closeCsvImport() {
    document.getElementById('csv-import-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

function handleCsvFile(input) {
    const file = input.files[0];
    if (!file) return;
    if (!file.name.toLowerCase().endsWith('.csv')) {
        alert('Please select a CSV file.');
        input.value = '';
        return;
    }
    csvFile = file;
    document.getElementById('csv-file-name').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
    document.getElementById('csv-file-info').classList.remove('hidden');
    document.getElementById('csv-preview-btn').disabled = false;
    document.getElementById('csv-preview-btn').className = 'w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg cursor-pointer transition shadow-sm';
}

async function previewCsvImport() {
    const subject = document.getElementById('csv-subject').value;
    const cls = document.getElementById('csv-class').value;
    const term = document.getElementById('csv-term').value;
    const session = document.getElementById('csv-session').value;
    const examType = document.getElementById('csv-exam-type').value;
    const topic = document.getElementById('csv-topic').value;

    if (!subject || !cls || !term || !session) {
        alert('Please fill in all required fields (Subject, Class, Term, Session).');
        return;
    }
    if (!csvFile) {
        alert('Please select a CSV file.');
        return;
    }

    const btn = document.getElementById('csv-preview-btn');
    btn.disabled = true; btn.textContent = 'Processing...';

    const formData = new FormData();
    formData.append('file', csvFile);
    formData.append('subject', subject);
    formData.append('class', cls);
    formData.append('term', term);
    formData.append('session', session);
    formData.append('exam_type', examType);
    formData.append('topic', topic);

    try {
        const res = await fetch('/api/csv-import/preview', { method: 'POST', body: formData });
        const data = await res.json();
        if (!data.success) {
            alert(data.error || 'Preview failed.');
            btn.disabled = false; btn.textContent = 'Preview Import';
            return;
        }
        csvPreviewData = data;
        showCsvPreview(data);
    } catch (e) {
        alert('Network error while processing file.');
        btn.disabled = false; btn.textContent = 'Preview Import';
    }
}

function showCsvPreview(data) {
    document.getElementById('csv-step-1').classList.add('hidden');
    document.getElementById('csv-step-2').classList.remove('hidden');
    document.getElementById('csv-footer-step-1').classList.add('hidden');
    document.getElementById('csv-footer-step-2').classList.remove('hidden');
    document.getElementById('csv-footer-step-3').classList.add('hidden');
    document.getElementById('csv-import-step-label').textContent = 'Step 2 of 3: Review & Confirm';

    document.getElementById('csv-preview-stats').innerHTML = `
        <div class="p-2 sm:p-3 bg-slate-50 border border-slate-200 rounded-lg text-center">
            <div class="text-lg sm:text-xl font-bold text-slate-900">${data.total_rows}</div>
            <div class="text-xs text-slate-500">Total Rows</div>
        </div>
        <div class="p-2 sm:p-3 bg-blue-50 border border-blue-200 rounded-lg text-center">
            <div class="text-lg sm:text-xl font-bold text-blue-600">${data.valid_rows}</div>
            <div class="text-xs text-blue-600">Valid</div>
        </div>
        <div class="p-2 sm:p-3 ${data.error_rows > 0 ? 'bg-red-800/10 border-red-700/20' : 'bg-slate-50 border-slate-200'} border rounded-lg text-center">
            <div class="text-lg sm:text-xl font-bold ${data.error_rows > 0 ? 'text-red-700' : 'text-slate-500'}">${data.error_rows}</div>
            <div class="text-xs ${data.error_rows > 0 ? 'text-red-700' : 'text-slate-500'}">Errors</div>
        </div>
        <div class="p-2 sm:p-3 bg-slate-800/10 border border-slate-800/20 rounded-lg text-center">
            <div class="text-lg sm:text-xl font-bold text-slate-800">${data.duplicate_count}</div>
            <div class="text-xs text-slate-800">Duplicates Found</div>
        </div>
    `;

    if (data.error_rows > 0) {
        document.getElementById('csv-preview-errors').classList.remove('hidden');
        document.getElementById('csv-error-title').textContent = data.error_rows + ' Row(s) with Errors';
        document.getElementById('csv-error-list').innerHTML = Object.entries(data.errors || {}).map(([row, errs]) =>
            `<div class="flex gap-2"><span class="font-medium whitespace-nowrap">Row ${row}:</span><span>${errs.join('; ')}</span></div>`
        ).join('');
    } else {
        document.getElementById('csv-preview-errors').classList.add('hidden');
    }

    // Pagination setup
    csvPage = 1;
    csvPreviewData = data;
    renderCsvPreviewPage();

    document.getElementById('csv-import-btn').disabled = data.valid_rows === 0;
    document.getElementById('csv-import-btn').className = data.valid_rows === 0
        ? 'px-6 py-2 bg-slate-400 text-slate-700 text-sm font-bold rounded-lg cursor-not-allowed'
        : 'px-6 py-2 bg-blue-600 hover:bg-blue-600 text-white text-sm font-bold rounded-lg cursor-pointer transition';
}

function renderCsvPreviewPage() {
    const data = csvPreviewData;
    if (!data) return;
    const rows = data.rows || [];
    const totalRows = rows.length;
    const totalPages = Math.max(1, Math.ceil(totalRows / csvPageSize));
    if (csvPage > totalPages) csvPage = totalPages;

    const start = (csvPage - 1) * csvPageSize;
    const end = Math.min(start + csvPageSize, totalRows);
    const pageRows = rows.slice(start, end);

    const tbody = document.getElementById('csv-preview-table');
    if (pageRows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="px-3 py-4 text-center text-slate-400">No rows to display.</td></tr>';
    } else {
        tbody.innerHTML = pageRows.map(r => {
            const question = r.question.length > 60 ? r.question.substring(0, 60) + '...' : r.question;
            return `<tr class="${r.valid ? '' : 'bg-red-800/10'} border-b border-slate-100">
                <td class="px-2 sm:px-3 py-2 text-slate-500">${r.row}</td>
                <td class="px-2 sm:px-3 py-2 font-medium text-slate-800 max-w-[180px] sm:max-w-[280px] truncate" title="${r.question.replace(/"/g, '&quot;')}">${question || '-'}</td>
                <td class="px-2 sm:px-3 py-2 text-slate-600">${r.optionA || '-'}</td>
                <td class="px-2 sm:px-3 py-2 text-slate-600">${r.optionB || '-'}</td>
                <td class="px-2 sm:px-3 py-2 text-slate-600">${r.optionC || '-'}</td>
                <td class="px-2 sm:px-3 py-2 text-slate-600">${r.optionD || '-'}</td>
                <td class="px-2 sm:px-3 py-2 text-center font-bold ${r.valid ? 'text-blue-600' : 'text-red-500'}">${r.correctAnswer || '-'}</td>
                <td class="px-2 sm:px-3 py-2 text-center">${r.valid
                    ? '<span class="text-blue-600 text-xs font-semibold">OK</span>'
                    : '<span class="text-red-700 text-xs font-semibold" title="' + (r.errors || []).join('; ') + '">Error</span>'
                }</td>
            </tr>`;
        }).join('');
    }

    // Update pagination controls
    const pagDiv = document.getElementById('csv-pagination');
    const moreDiv = document.getElementById('csv-preview-more');

    if (totalPages > 1 || data.has_more) {
        pagDiv.classList.remove('hidden');
        moreDiv.classList.add('hidden');
    } else {
        pagDiv.classList.add('hidden');
        if (data.has_more) {
            moreDiv.classList.remove('hidden');
            moreDiv.textContent = 'Showing first ' + totalRows + ' of ' + data.total_all_rows + ' rows.';
        } else {
            moreDiv.classList.add('hidden');
        }
    }

    document.getElementById('csv-page-info').textContent = `Page ${csvPage} of ${totalPages} (${totalRows} rows${data.has_more ? ' of ' + data.total_all_rows + ' total' : ''})`;
    document.getElementById('csv-page-num').textContent = csvPage;
    document.getElementById('csv-prev-page').disabled = csvPage <= 1;
    document.getElementById('csv-next-page').disabled = csvPage >= totalPages;
}

function goCsvPage(delta) {
    const data = csvPreviewData;
    if (!data) return;
    const totalPages = Math.max(1, Math.ceil((data.rows || []).length / csvPageSize));
    const newPage = Math.max(1, Math.min(totalPages, csvPage + delta));
    if (newPage === csvPage) return;
    csvPage = newPage;
    renderCsvPreviewPage();
}

function setCsvPageSize(size) {
    csvPageSize = size;
    csvPage = 1;
    renderCsvPreviewPage();
}

function csvGoBack(step) {
    document.getElementById('csv-import-step-label').textContent = step === 1 ? 'Step 1 of 3: Upload File' : 'Step 2 of 3: Review & Confirm';
    document.getElementById('csv-step-1').classList.toggle('hidden', step !== 1);
    document.getElementById('csv-step-2').classList.toggle('hidden', step !== 2);
    document.getElementById('csv-step-3').classList.toggle('hidden', step !== 3);
    document.getElementById('csv-footer-step-1').classList.toggle('hidden', step !== 1);
    document.getElementById('csv-footer-step-2').classList.toggle('hidden', step !== 2);
    document.getElementById('csv-footer-step-3').classList.toggle('hidden', step !== 3);
}

async function confirmCsvImport() {
    if (!csvFile || !csvPreviewData || csvPreviewData.valid_rows === 0) return;

    const duplicateHandling = document.querySelector('input[name="duplicate_handling"]:checked')?.value || 'import_all';

    const btn = document.getElementById('csv-import-btn');
    const btnTop = document.getElementById('csv-import-btn-top');
    const progress = document.getElementById('csv-import-progress');
    if (btn) { btn.disabled = true; btn.classList.add('hidden'); }
    if (btnTop) { btnTop.disabled = true; btnTop.classList.add('hidden'); }
    progress.classList.remove('hidden');

    const formData = new FormData();
    formData.append('file', csvFile);
    formData.append('subject', document.getElementById('csv-subject').value);
    formData.append('class', document.getElementById('csv-class').value);
    formData.append('term', document.getElementById('csv-term').value);
    formData.append('session', document.getElementById('csv-session').value);
    formData.append('exam_type', document.getElementById('csv-exam-type').value);
    formData.append('topic', document.getElementById('csv-topic').value);
    formData.append('subTopic', document.getElementById('csv-subtopic').value);
    formData.append('duration', document.getElementById('csv-duration').value);
    formData.append('defaultMarks', document.getElementById('csv-marks').value);
    formData.append('duplicate_handling', duplicateHandling);

    try {
        const res = await fetch('/api/csv-import/import', { method: 'POST', body: formData });
        let data;
        try {
            data = await res.json();
        } catch (parseError) {
            progress.classList.add('hidden');
            if (btn) { btn.classList.remove('hidden'); btn.disabled = false; }
            if (btnTop) { btnTop.classList.remove('hidden'); btnTop.disabled = false; }
            alert('Server returned an invalid response. Please check the server logs.');
            return;
        }

        progress.classList.add('hidden');
        if (btn) { btn.classList.remove('hidden'); btn.disabled = false; }
        if (btnTop) { btnTop.classList.remove('hidden'); btnTop.disabled = false; }

        if (!data.success) {
            alert(data.error || 'Import failed.');
            return;
        }

        showCsvResult(data);
    } catch (e) {
        progress.classList.add('hidden');
        if (btn) { btn.classList.remove('hidden'); btn.disabled = false; }
        if (btnTop) { btnTop.classList.remove('hidden'); btnTop.disabled = false; }
        alert('Network error during import. Please check your connection and try again.');
    }
}

function showCsvResult(data) {
    document.getElementById('csv-step-2').classList.add('hidden');
    document.getElementById('csv-step-3').classList.remove('hidden');
    document.getElementById('csv-footer-step-1').classList.add('hidden');
    document.getElementById('csv-footer-step-2').classList.add('hidden');
    document.getElementById('csv-footer-step-3').classList.remove('hidden');
    document.getElementById('csv-import-step-label').textContent = 'Step 3 of 3: Complete';

    const hasErrors = data.errors && Object.keys(data.errors).length > 0;
    const isFullySuccessful = data.imported > 0 && !hasErrors;

    document.getElementById('csv-result-title').textContent = isFullySuccessful ? 'Import Complete!' : 'Import Completed with Issues';
    document.getElementById('csv-result-message').textContent = data.message;

    document.getElementById('csv-result-details').innerHTML = `
        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-center">
            <div class="text-xl font-bold text-blue-600">${data.imported}</div>
            <div class="text-xs text-blue-600">Imported</div>
        </div>
        <div class="p-3 bg-slate-800/10 border border-slate-800/20 rounded-lg text-center">
            <div class="text-xl font-bold text-slate-800">${data.skipped}</div>
            <div class="text-xs text-slate-800">Skipped</div>
        </div>
        <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-center">
            <div class="text-xl font-bold text-blue-700">${data.replaced}</div>
            <div class="text-xs text-blue-600">Replaced</div>
        </div>
    `;

    const errDiv = document.getElementById('csv-result-errors');
    const errList = document.getElementById('csv-result-error-list');
    if (hasErrors) {
        errDiv.classList.remove('hidden');
        errList.innerHTML = Object.entries(data.errors).map(([row, errs]) =>
            `<div class="flex gap-2"><span class="font-medium whitespace-nowrap">Row ${row}:</span><span>${Array.isArray(errs) ? errs.join('; ') : errs}</span></div>`
        ).join('');
    } else {
        errDiv.classList.add('hidden');
    }

    // Refresh exam list and switch to CBT tab so user sees imported questions
    initTeacherDashboard().then(() => {
        if (data.imported > 0) {
            switchTab('cbt-engine');
        }
    });
}

function downloadCsvTemplate() {
    window.open('/api/csv-import/template', '_blank');
}

// Drop zone support
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('csv-drop-zone');
    if (dropZone) {
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('border-blue-600', 'bg-blue-50');
        });
        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('border-blue-600', 'bg-blue-50');
        });
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('border-blue-600', 'bg-blue-50');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('csv-file-input').files = files;
                handleCsvFile(document.getElementById('csv-file-input'));
            }
        });
    }
});

// ====== EXAM SETTINGS ======
let examSettingsId = null;

function openExamSettings(id) {
    const exam = teacherData.exams.find(e => e.id === id);
    if (!exam) return;
    examSettingsId = id;
    document.getElementById('exam-settings-title').value = exam.title || '';
    document.getElementById('exam-settings-duration').value = exam.duration || 30;
    document.getElementById('exam-settings-marks').value = exam.defaultMarks || 5;
    document.getElementById('exam-settings-instructions').value = exam.instructions || '';
    document.getElementById('exam-settings-status').classList.add('hidden');
    document.getElementById('exam-settings-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeExamSettings() {
    document.getElementById('exam-settings-modal').classList.add('hidden');
    document.body.style.overflow = '';
    examSettingsId = null;
}

async function saveExamSettings() {
    const title = document.getElementById('exam-settings-title').value.trim();
    const duration = document.getElementById('exam-settings-duration').value;
    const marks = document.getElementById('exam-settings-marks').value;
    const instructions = document.getElementById('exam-settings-instructions').value.trim();

    if (!duration || duration < 1) { alert('Duration must be at least 1 minute.'); return; }
    if (!marks || marks < 1 || marks > 100) { alert('Marks must be between 1 and 100.'); return; }

    const btn = document.getElementById('exam-settings-save-btn');
    btn.disabled = true; btn.textContent = 'Saving...';

    try {
        const res = await fetch('/api/exams/' + examSettingsId + '/settings', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ title, duration: parseInt(duration), defaultMarks: parseInt(marks), instructions })
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('exam-settings-status').className = 'text-xs font-semibold text-blue-600 bg-blue-50 p-3 rounded-lg';
            document.getElementById('exam-settings-status').textContent = 'Settings saved successfully!';
            document.getElementById('exam-settings-status').classList.remove('hidden');
            initTeacherDashboard();
        } else {
            alert('Failed to save settings.');
        }
    } catch(e) {
        alert('Network error.');
    }
    btn.disabled = false; btn.textContent = 'Save Settings';
}

// ====== SCHEME OF WORK ======
// ====== TAB SWITCHING ======
let currentTab = '';
function switchTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.tab-btn').forEach(btn => {
        const isActive = btn.id === 'tab-' + tab + '-btn';
        btn.className = 'tab-btn px-4 py-3 text-sm font-semibold border-b-2 transition cursor-pointer whitespace-nowrap ' + (isActive ? 'border-slate-800 text-slate-800 bg-white' : 'border-transparent text-slate-500 hover:text-slate-700');
    });
    document.querySelectorAll('.tab-panel').forEach(p => {
        p.classList.toggle('hidden', p.id !== 'tab-' + tab);
    });
    if (tab === 'results') {
        pollResults();
        startResultsPolling();
    } else {
        stopResultsPolling();
    }
}

// ====== EVENT DELEGATION (fallback if inline onclick fails) ======
document.addEventListener('click', function(e) {
    if (e.target.closest('[data-download-script]')) return;
    const viewEl = e.target.closest('[data-view-result]');
    if (viewEl) {
        const id = viewEl.getAttribute('data-view-result');
        if (id) viewResult(id);
    }
});

// ====== INITIALIZE VOICE INPUT BUTTONS ======
['plan-duration','plan-topic','plan-subtopic','plan-school','plan-teacher',
 'note-topic','note-subtopic',
 'q-topic','q-subtopic','q-count'].forEach(createVoiceInput);

// ====== INIT ======

initTeacherDashboard();
</script>
@endsection
