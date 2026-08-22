@extends('layouts.app')

@section('content')
<div class="bg-slate-50 text-slate-900 overflow-x-hidden">
    <!-- Header Navigation -->
    <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-gradient-to-br from-blue-950 via-red-600 to-emerald-600 text-white font-black text-lg shadow-lg shadow-blue-900/40">
                    S
                </span>
                <span class="text-xl font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-950 via-red-600 to-emerald-650">
                    ClassPortal
                </span>
            </div>
            <div class="hidden md:flex items-center gap-8 text-sm font-bold text-slate-600">
                <a href="#features" class="hover:text-red-600 transition">Features</a>
                <a href="#subjects" class="hover:text-emerald-700 transition">Subjects</a>
                <a href="#pricing" class="hover:text-blue-950 transition">Pricing</a>
                <a href="#contact" class="hover:text-red-700 transition">Contact</a>
            </div>
            <div class="flex items-center gap-2">
                @auth
                <span class="text-xs font-bold text-slate-600 hidden sm:block">{{ Session::get('user.name') }}</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 text-xs font-black text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer border-none">
                        Logout
                    </button>
                </form>
                @else
                <button
                    class="px-5 py-2.5 text-xs font-black text-white bg-gradient-to-r from-blue-950 via-red-600 to-emerald-600 hover:from-blue-900 hover:to-emerald-750 rounded-xl shadow-md shadow-blue-900/20 transition cursor-pointer border-none"
                >
                    Enter Portals
                </button>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-12 pb-20 sm:pt-16 sm:pb-28">
        <div class="absolute inset-0 bg-slate-100/40 -z-10"></div>
        <div class="absolute -top-40 right-10 w-96 h-96 bg-blue-900/10 rounded-full blur-3xl"></div>
        <div class="absolute top-20 -left-10 w-80 h-80 bg-red-600/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 right-2 w-80 h-80 bg-emerald-600/5 rounded-full blur-3xl"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-8">
            <div
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-gradient-to-r from-blue-50 to-red-50 border border-red-200 text-blue-950 rounded-full font-black text-xs shadow-sm"
            >
                <span class="w-3.5 h-3.5 text-red-600">📚</span>
                <span class="text-blue-950 font-extrabold">Complete CBT & Lesson Note Suite</span>
            </div>

            <h1 class="text-4xl sm:text-6xl font-black tracking-tight text-blue-950 max-w-4xl mx-auto leading-[1.1]">
                Elevate School Excellence With
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-950 via-red-650 to-emerald-600">
                    Dynamic Exam CBT & Lesson Organizers
                </span>
            </h1>

            <p class="text-base sm:text-xl text-slate-600 max-w-2xl mx-auto leading-relaxed font-semibold">
                The definitive portal for custom lesson plans, class notes compilation, drag-and-drop CSV question adapters, and high-performance CBT exam taking for students.
            </p>

            <div class="flex flex-wrap items-center justify-center gap-3">
                <button
                    class="px-8 py-3.5 bg-gradient-to-r from-blue-950 via-red-600 to-emerald-600 hover:from-blue-900 hover:via-red-700 hover:to-emerald-700 text-white font-extrabold text-sm rounded-xl transition shadow-lg shadow-blue-950/20 cursor-pointer flex items-center gap-2 group border-none"
                >
                    Get Started Freely
                    <span class="w-4 h-4 group-hover:translate-x-1 transition-transform">→</span>
                </button>
                <button class="px-6 py-3.5 bg-emerald-50 text-emerald-800 border border-emerald-150 hover:bg-emerald-100 font-bold text-sm rounded-xl transition cursor-pointer">
                    Take CBT Exam
                </button>
            </div>

            <!-- Direct CBT Exam Entry Hall -->
            <div class="max-w-3xl mx-auto mt-12 p-6 sm:p-8 bg-gradient-to-tr from-blue-50/70 via-red-50/20 to-emerald-50/50 rounded-3xl border border-blue-150 shadow-xl text-left space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="flex h-2.5 w-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[10px] uppercase font-black tracking-wider text-slate-500">Live CBT Examination Room</span>
                        </div>
                        <h3 class="text-lg font-black text-slate-900">Directly Join Exam Room</h3>
                        <p class="text-xs text-slate-500">Paste your exam link, your exam code, or select an active test below to take it immediately.</p>
                    </div>

                    <div class="shrink-0 flex gap-2">
                        <span class="px-3.5 py-1.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-[10px] font-bold">
                            ⚡ Code Entry Active
                        </span>
                    </div>
                </div>

                <!-- Exam Code Input Form -->
                <form id="exam-code-form" class="space-y-3">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="relative flex-1 flex items-center">
                            <span class="absolute left-3.5 text-slate-400 z-10">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            </span>
                            <input
                                type="text" id="exam-code-input" required
                                placeholder="Paste the exam link or enter exam code here (e.g. exam_xxxx)"
                                class="w-full bg-white border border-slate-200 rounded-xl pl-10 pr-10 py-3 text-xs font-semibold focus:outline-none focus:border-blue-900 focus:ring-1 focus:ring-blue-900"
                            />
                            <div class="absolute right-2 z-10 flex items-center" id="exam-code-voice-btn"></div>
                        </div>
                        <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs rounded-xl shadow-md transition cursor-pointer shrink-0 uppercase tracking-wider border-none">
                            Enter Exam Room Now
                        </button>
                    </div>
                    <div id="exam-code-error" class="hidden text-xs text-rose-600 font-bold"></div>
                    <div id="exam-code-success" class="hidden text-xs text-emerald-600 font-bold"></div>
                </form>

                <div class="border-t border-slate-200/60 pt-4 space-y-3">
                    <h4 class="text-xs font-extrabold text-slate-700 uppercase tracking-widest">Ongoing Published Exams Room (<span id="exams-count">0</span>)</h4>
                    <div id="exams-loading" class="text-xs text-slate-400 font-medium animate-pulse hidden">Checking published exam registries...</div>
                    <div id="exams-empty" class="p-4 bg-slate-100/55 rounded-xl text-center text-xs text-slate-400 font-medium">
                        No examinations have been published on this portal yet. Create and publish one as an educator to list it here!
                    </div>
                    <div id="exams-list" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-48 overflow-y-auto pr-1"></div>
                </div>
            </div>

            <!-- Graphical Dashboard Teaser Mock -->
            <div class="mt-16 relative mx-auto max-w-5xl rounded-3xl overflow-hidden shadow-2xl border border-slate-200">
                <div class="bg-slate-900 text-white p-3 flex items-center gap-2 px-6">
                    <span class="w-3 h-3 rounded-full bg-red-650 block"></span>
                    <span class="w-3 h-3 rounded-full bg-emerald-500 block"></span>
                    <span class="w-3 h-3 rounded-full bg-blue-900 block"></span>
                    <span class="text-xs font-mono text-slate-400 ml-4 font-bold">ClassPortal.Edu Dashboard Preview / CBT Arena</span>
                </div>
                <div class="p-8 sm:p-12 bg-white flex flex-col md:flex-row gap-8 items-center justify-between text-left">
                    <div class="space-y-4 max-w-md">
                        <span class="text-xs font-bold text-red-600 uppercase tracking-wider block">Interactive Suite</span>
                        <h3 class="text-2xl font-extrabold text-blue-950 leading-tight">Nigeria's No. 1 Educator Portal for Primary & High Schools</h3>
                        <p class="text-sm text-slate-600">
                            Fully operational dashboards configured with assessment engines, performance index lines, student scorecard tracking, and downloadable grade reports.
                        </p>
                        <div class="flex gap-4 pt-2">
                            <div class="text-center bg-slate-50 py-2 px-4 rounded-xl border border-slate-100">
                                <p class="text-xl font-bold text-slate-900">Free</p>
                                <p class="text-[10px] text-slate-400 font-bold">CBT Exam Publishes</p>
                            </div>
                            <div class="text-center bg-slate-50 py-2 px-4 rounded-xl border border-slate-100">
                                <p class="text-xl font-bold text-slate-900">100%</p>
                                <p class="text-[10px] text-slate-400 font-bold">Curriculum Aligned</p>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-1/2 p-6 bg-gradient-to-br from-blue-50 to-emerald-50/50 rounded-2xl border border-blue-100 space-y-4 font-sans">
                        <div class="flex items-center justify-between bg-white py-2.5 px-4 rounded-xl shadow-xs border border-blue-50">
                            <span class="text-xs font-bold text-slate-500">Active CBT Examinees</span>
                            <span class="text-xs font-bold text-emerald-600 font-extrabold">● 42 students online</span>
                        </div>
                        <div class="p-4 bg-white rounded-xl shadow-xs border border-blue-50 space-y-2 text-xs">
                            <div class="flex justify-between font-bold text-slate-700">
                                <span>Mathematics CBT</span>
                                <span class="text-emerald-700">78% Average Score</span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-emerald-600 h-full w-4/5"></div>
                            </div>
                        </div>
                        <div class="p-4 bg-white rounded-xl shadow-xs border border-blue-50 space-y-2 text-xs">
                            <div class="flex justify-between font-bold text-slate-700">
                                <span>Physics Thermodynamics</span>
                                <span class="text-red-700 font-extrabold">88% Average Score</span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-red-600 h-full w-[88%]"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Grid Section -->
    <section id="features" class="py-20 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center space-y-3">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Tools Tailored for Modern Classrooms</h2>
                <p class="text-slate-600 max-w-xl mx-auto text-sm leading-relaxed">
                    We leverage direct robust technologies to coordinate lesson notes, content delivery, and CBT test preparation.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="p-6 bg-white hover:bg-slate-50 rounded-2xl border border-slate-200 hover:border-blue-900 transition duration-300 flex flex-col space-y-4">
                    <span class="w-10 h-10 rounded-xl bg-slate-50 shadow-xs border border-slate-100 flex items-center justify-center text-violet-600">🧠</span>
                    <h3 class="text-base font-extrabold text-blue-950">Exam Question Generator</h3>
                    <p class="text-xs text-slate-600 leading-relaxed flex-grow">Generate comprehensive multiple-choice questions matching Nigeria and WAEC/NECO syllabus in seconds.</p>
                </div>
                <div class="p-6 bg-white hover:bg-slate-50 rounded-2xl border border-slate-200 hover:border-blue-900 transition duration-300 flex flex-col space-y-4">
                    <span class="w-10 h-10 rounded-xl bg-slate-50 shadow-xs border border-slate-100 flex items-center justify-center text-emerald-600">📖</span>
                    <h3 class="text-base font-extrabold text-blue-950">Lesson Note Generator</h3>
                    <p class="text-xs text-slate-600 leading-relaxed flex-grow">Input any subject or topic to instantly structure clear content notes, definitions, class exercises, and take-home tasks.</p>
                </div>
                <div class="p-6 bg-white hover:bg-slate-50 rounded-2xl border border-slate-200 hover:border-blue-900 transition duration-300 flex flex-col space-y-4">
                    <span class="w-10 h-10 rounded-xl bg-slate-50 shadow-xs border border-slate-100 flex items-center justify-center text-indigo-600">🎓</span>
                    <h3 class="text-base font-extrabold text-blue-950">CBT Exam Simulator</h3>
                    <p class="text-xs text-slate-600 leading-relaxed flex-grow">Host custom examinations with reliable countdown countdown limits, real-time logging, auto-submit bounds, and printable pass certificates.</p>
                </div>
                <div class="p-6 bg-white hover:bg-slate-50 rounded-2xl border border-slate-200 hover:border-blue-900 transition duration-300 flex flex-col space-y-4">
                    <span class="w-10 h-10 rounded-xl bg-slate-50 shadow-xs border border-slate-100 flex items-center justify-center text-red-600">🏫</span>
                    <h3 class="text-base font-extrabold text-blue-950">Bulk CSV Question Upload</h3>
                    <p class="text-xs text-slate-600 leading-relaxed flex-grow">Supports teachers to upload hundreds of exam questions from spreadsheets instantly into active CBT packages.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Available Subjects Shelf -->
    <section id="subjects" class="py-20 bg-slate-50 border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center space-y-3">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Supporting All Subjects</h2>
                <p class="text-slate-600 max-w-xl mx-auto text-sm leading-relaxed">
                    Designed to serve students from Primary, Junior Secondary to Senior Secondary School levels.
                </p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @php
                    $subjects = [
                        ['name' => 'Mathematics', 'icon' => '📐', 'level' => 'Primary & Secondary'],
                        ['name' => 'English Language', 'icon' => '✍', 'level' => 'All Levels'],
                        ['name' => 'Physics', 'icon' => '⚡', 'level' => 'Senior Secondary'],
                        ['name' => 'Chemistry', 'icon' => '🧪', 'level' => 'Senior Secondary'],
                        ['name' => 'Biology', 'icon' => '🧬', 'level' => 'Senior Secondary'],
                        ['name' => 'Economics', 'icon' => '📉', 'level' => 'All Levels'],
                        ['name' => 'Coding', 'icon' => '💻', 'level' => 'All Levels'],
                        ['name' => 'Artificial Intelligence', 'icon' => '🤖', 'level' => 'All Levels'],
                        ['name' => 'Cultural & Creative Arts', 'icon' => '🎨', 'level' => 'Primary & Secondary'],
                        ['name' => 'Social Studies', 'icon' => '🌍', 'level' => 'Primary & Secondary'],
                        ['name' => 'Physical & Health Education', 'icon' => '⚽', 'level' => 'All Levels'],
                        ['name' => 'Phonics', 'icon' => '🔤', 'level' => 'Primary'],
                    ];
                @endphp
                @foreach($subjects as $sub)
                    <div class="p-4 bg-white border border-slate-200/80 rounded-2xl text-center space-y-2 hover:shadow-md hover:border-emerald-500 transition duration-300">
                        <span class="text-3xl block filter drop-shadow-sm">{{ $sub['icon'] }}</span>
                        <p class="text-xs font-bold text-slate-900 leading-tight">{{ $sub['name'] }}</p>
                        <p class="text-[10px] text-slate-400 font-bold tracking-wide uppercase">{{ $sub['level'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-20 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center space-y-3">
                <h2 class="text-3xl font-black text-blue-950 tracking-tight">Loved by Nigerian Educators</h2>
                <p class="text-slate-600 max-w-xl mx-auto text-sm">
                    Read how schools are elevating digital assessment pipelines.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200 space-y-4">
                    <div class="flex gap-1">
                        <span class="w-4 h-4 text-amber-400 fill-amber-400">⭐</span>
                        <span class="w-4 h-4 text-amber-400 fill-amber-400">⭐</span>
                        <span class="w-4 h-4 text-amber-400 fill-amber-400">⭐</span>
                        <span class="w-4 h-4 text-amber-400 fill-amber-400">⭐</span>
                        <span class="w-4 h-4 text-amber-400 fill-amber-400">⭐</span>
                    </div>
                    <p class="text-xs text-slate-700 italic leading-relaxed">
                        "Generating lesson notes used to take hours of manual copy pasting. With ClassPortal, I generated a 3-week lesson note and evaluation questions in 2 minutes. The Nigerian syllabus alignment is spot-on!"
                    </p>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Mrs. Abigail Johnson</p>
                        <p class="text-[10px] text-slate-400 font-semibold">Vice Principal, ClassPortal Academy, Lagos</p>
                    </div>
                </div>

                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200 space-y-4">
                    <div class="flex gap-1">
                        <span class="w-4 h-4 text-amber-400 fill-amber-400">⭐</span>
                        <span class="w-4 h-4 text-amber-400 fill-amber-400">⭐</span>
                        <span class="w-4 h-4 text-amber-400 fill-amber-400">⭐</span>
                        <span class="w-4 h-4 text-amber-400 fill-amber-400">⭐</span>
                        <span class="w-4 h-4 text-amber-400 fill-amber-400">⭐</span>
                    </div>
                    <p class="text-xs text-slate-700 italic leading-relaxed">
                        "The CSV upload is incredibly fast. I uploaded 150 questions in Chemistry and immediately got an invitation link for students. Scoring is instant!"
                    </p>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Mr. Austin Nwaigbo</p>
                        <p class="text-[10px] text-slate-400 font-semibold">Chemistry Lecturer, JSS/SSS Tech</p>
                    </div>
                </div>

                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200 space-y-4">
                    <div class="flex gap-1">
                        <span class="w-4 h-4 text-amber-400 fill-amber-400">⭐</span>
                        <span class="w-4 h-4 text-amber-400 fill-amber-400">⭐</span>
                        <span class="w-4 h-4 text-amber-400 fill-amber-400">⭐</span>
                        <span class="w-4 h-4 text-amber-400 fill-amber-400">⭐</span>
                        <span class="w-4 h-4 text-amber-400 fill-amber-400">⭐</span>
                    </div>
                    <p class="text-xs text-slate-700 italic leading-relaxed">
                        "Students took their trial CBT on mobile phones. Progress auto-saves, and the review Mode makes taking tests an educational experience. The downloadable performance certificate is beautiful!"
                    </p>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Rev. Dr. Peter Emmanuel</p>
                        <p class="text-[10px] text-slate-400 font-semibold">Director, Landmark Schools</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing matrix block -->
    <section id="pricing" class="py-20 bg-slate-50 border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center space-y-3">
                <h2 class="text-3xl font-black text-blue-950 tracking-tight">100% Free & Complimentary Access</h2>
                <p class="text-slate-600 max-w-xl mx-auto text-sm leading-relaxed">
                    No subscription, no token fee. Our entire platform is now 100% free and open for teachers, students, and schools.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 max-w-5xl mx-auto gap-8">
                <!-- Free Tier -->
                <div class="p-8 bg-white rounded-3xl border border-slate-200 relative space-y-6 flex flex-col justify-between">
                    <div class="space-y-4">
                        <span class="text-xs font-bold uppercase text-slate-400 tracking-wider">Student Tier</span>
                        <h3 class="text-2xl font-black text-blue-955">Standard Practice CBT</h3>
                        <p class="text-xs text-slate-500">Perfect for students starting practice or self-evaluation.</p>
                        <div class="text-3xl font-black text-slate-900 font-sans">₦0 <span class="text-xs text-slate-400 font-medium">/ forever</span></div>
                        <ul class="space-y-3 text-xs text-slate-600 font-semibold">
                            <li class="flex items-center gap-2"><span class="w-4 h-4 text-emerald-500">✓</span> Free Practice Questions Arena</li>
                            <li class="flex items-center gap-2"><span class="w-4 h-4 text-emerald-500">✓</span> Grade Reports & Fail Reviews</li>
                            <li class="flex items-center gap-2"><span class="w-4 h-4 text-emerald-500">✓</span> Custom CBT Exam Taking</li>
                            <li class="flex items-center gap-2"><span class="w-4 h-4 text-emerald-500">✓</span> Automated Lesson Notes Access</li>
                        </ul>
                    </div>
                    <button class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl text-xs transition transition duration-200 cursor-pointer">
                        Sign up Freely
                    </button>
                </div>

                <!-- Popular Tier -->
                <div class="p-8 bg-gradient-to-br from-blue-950 via-slate-900 to-emerald-950 text-white rounded-3xl relative space-y-6 flex flex-col justify-between shadow-xl ring-4 ring-emerald-500/10">
                    <span class="absolute top-4 right-4 bg-gradient-to-r from-red-650 to-emerald-650 text-white font-extrabold text-[10px] uppercase py-1 px-3.5 rounded-full tracking-wider shadow-md font-sans">
                        ClassPortal Free
                    </span>
                    <div class="space-y-4">
                        <span class="text-xs font-bold uppercase text-emerald-300 tracking-wider">Educator Bundle</span>
                        <h3 class="text-2xl font-black text-white">Publish CBT Exams</h3>
                        <p class="text-xs text-slate-300">Publish exams for hundreds of examinees, host link sessions.</p>
                        <div class="text-4xl font-black text-white font-sans">₦1,000 <span class="text-xs text-emerald-300 font-medium">/ per CBT Published</span></div>
                        <p class="text-[11px] text-slate-400 font-sans block">All features unlocked instantly & completely free</p>
                        <ul class="space-y-3 text-xs text-slate-200 font-semibold">
                            <li class="flex items-center gap-2"><span class="w-4 h-4 text-emerald-400">✓</span> Bulk spreadsheet CSV Adapters</li>
                            <li class="flex items-center gap-2"><span class="w-4 h-4 text-emerald-400">✓</span> Secure live countdown timings</li>
                            <li class="flex items-center gap-2"><span class="w-4 h-4 text-emerald-400">✓</span> Auto-grade & Review modes</li>
                            <li class="flex items-center gap-2"><span class="w-4 h-4 text-emerald-400">✓</span> PDF certificates generation</li>
                            <li class="flex items-center gap-2"><span class="w-4 h-4 text-emerald-400">✓</span> Lesson plan tabular builder</li>
                        </ul>
                    </div>
                    <a href="{{ route('teacher.dashboard') }}" class="w-full py-3 px-4 bg-gradient-to-r from-red-650 to-emerald-600 hover:from-red-700 hover:to-emerald-700 text-white font-extrabold rounded-xl text-xs transition duration-205 cursor-pointer shadow-md shadow-red-900/10 border-none text-center">
                        Access Portals Directly
                    </a>
                </div>

                <!-- Enterprise Tier -->
                <div class="p-8 bg-white rounded-3xl border border-slate-200 relative space-y-6 flex flex-col justify-between">
                    <div class="space-y-4">
                        <span class="text-xs font-bold uppercase text-slate-400 tracking-wider">Schools Suite</span>
                        <h3 class="text-2xl font-black text-slate-900 font-sans">Full Enterprise</h3>
                        <p class="text-xs text-slate-500">For large academic institutes, full customization, and branding.</p>
                        <div class="text-3xl font-black text-slate-900 font-sans">Custom <span class="text-xs text-slate-400 font-medium font-sans">quote</span></div>
                        <ul class="space-y-3 text-xs text-slate-600 font-semibold font-sans">
                            <li class="flex items-center gap-2"><span class="w-4 h-4 text-emerald-500">✓</span> Co-branded test headers</li>
                            <li class="flex items-center gap-2"><span class="w-4 h-4 text-emerald-500">✓</span> Advanced Instructor control desk</li>
                            <li class="flex items-center gap-2"><span class="w-4 h-4 text-emerald-500">✓</span> Dedicated database storage</li>
                            <li class="flex items-center gap-2"><span class="w-4 h-4 text-emerald-500">✓</span> 24/7 Priority support lines</li>
                        </ul>
                    </div>
                    <a href="{{ route('admin.dashboard') }}" class="w-full py-2.5 px-4 bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 font-bold rounded-xl text-xs transition duration-200 cursor-pointer border-none text-center">
                        Access Portals Directly
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-white border-t border-slate-100">
        <div class="max-w-4xl mx-auto px-4 space-y-12">
            <div class="text-center space-y-3">
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Need Support or Have Questions?</h2>
                <p class="text-slate-600 text-sm">
                    We are glad to help you modernize your school immediately. Reach out directly.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                <div class="space-y-6 p-6 bg-slate-50 border border-slate-200 rounded-3xl">
                    <h3 class="text-lg font-bold text-blue-950">Direct Contact details</h3>
                    <div class="space-y-4 font-sans text-sm">
                        <a href="tel:08062078597" class="flex items-center gap-3 p-2 rounded-2xl hover:bg-blue-100/40 transition group cursor-pointer block">
                            <span class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center text-blue-950 group-hover:scale-105 transition-transform shrink-0">📞</span>
                            <div>
                                <p class="text-[11px] text-slate-400 font-bold uppercase">Phone line</p>
                                <p class="font-bold text-slate-800 group-hover:text-blue-950 transition">08062078597</p>
                            </div>
                        </a>
                        <a href="https://wa.me/2348062078597?text=Hello%20ClassPortal%20Educational%20Suite" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 p-2 rounded-2xl hover:bg-emerald-50 transition group cursor-pointer block">
                            <span class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 group-hover:scale-105 transition-transform shrink-0">💬</span>
                            <div>
                                <p class="text-[11px] text-slate-400 font-bold uppercase">WhatsApp link</p>
                                <p class="font-bold text-slate-800 group-hover:text-emerald-750 transition">08062078597</p>
                            </div>
                        </a>
                        <a href="mailto:nwaigboaugust@gmail.com" class="flex items-center gap-3 p-2 rounded-2xl hover:bg-red-50 transition group cursor-pointer block">
                            <span class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center text-red-600 group-hover:scale-105 transition-transform shrink-0">🛡️</span>
                            <div>
                                <p class="text-[11px] text-slate-400 font-bold uppercase">Official Support Email</p>
                                <p class="font-bold text-slate-800 group-hover:text-red-700 transition">nwaigboaugust@gmail.com</p>
                            </div>
                        </a>
                    </div>
                </div>

                <form id="contact-form" class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Your Name</label>
                        <div class="relative flex items-center">
                            <input required type="text" id="feedback-name" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-3 pr-10 text-sm focus:outline-none focus:border-blue-950 focus:bg-white transition" placeholder="e.g. Augusta Johnson">
                            <div class="absolute right-2 z-10 voice-btn-container" data-input="feedback-name"></div>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">School Email</label>
                        <div class="relative flex items-center">
                            <input required type="email" id="feedback-email" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-3 pr-10 text-sm focus:outline-none focus:border-blue-950 focus:bg-white transition" placeholder="scholar@mind.com">
                            <div class="absolute right-2 z-10 voice-btn-container" data-input="feedback-email"></div>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Inquiry Description</label>
                        <div class="relative flex items-start">
                            <textarea required rows="3" id="feedback-message" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-3 pr-10 text-sm focus:outline-none focus:border-blue-950 focus:bg-white transition resize-none" placeholder="How can we assist your educator team?"></textarea>
                            <div class="absolute right-2 top-2 z-10 voice-btn-container" data-input="feedback-message"></div>
                        </div>
                    </div>
                    <div id="feedback-success" class="hidden bg-emerald-50 text-emerald-800 p-3 rounded-xl text-xs font-bold border border-emerald-100 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Thank you! Your feedback support ticket has been securely logged on our system dashboard!
                    </div>
                    <div id="feedback-error" class="hidden bg-rose-50 text-rose-800 p-3 rounded-xl text-xs font-bold border border-rose-100">
                        Failed to transmit your log. Please chat with us using the bot at the bottom right corner instead!
                    </div>
                    <button type="submit" id="feedback-submit-btn" class="w-full py-3 bg-gradient-to-r from-blue-950 via-red-650 to-emerald-600 text-white font-extrabold text-xs rounded-xl shadow-md transition hover:opacity-95 cursor-pointer uppercase tracking-wider border-none">
                        Submit Help Inquiry
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Elegant Footer -->
    <footer class="bg-slate-900 text-white py-12 border-t border-slate-800 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-950 text-white font-bold text-base">
                        S
                    </span>
                    <span class="text-lg font-black tracking-tight">ClassPortal</span>
                </div>
                <p class="text-xs text-slate-400 max-w-sm leading-relaxed">
                    Nigeria's state-of-the-art educational interface transforming CBT examiners, bulk lesson planners, and student grading profiles.
                </p>
            </div>
            <div class="space-y-3">
                <h4 class="font-bold text-sm">Quick Links</h4>
                <div class="space-y-2 text-xs text-slate-400">
                    <a href="#features" class="block hover:text-white transition">Features</a>
                    <a href="#subjects" class="block hover:text-white transition">Subjects</a>
                    <a href="#pricing" class="block hover:text-white transition">Pricing</a>
                    <a href="#contact" class="block hover:text-white transition">Contact</a>
                </div>
            </div>
            <div class="space-y-3">
                <h4 class="font-bold text-sm">Legal</h4>
                <div class="space-y-2 text-xs text-slate-400">
                    <a href="#" class="block hover:text-white transition">Privacy Policy</a>
                    <a href="#" class="block hover:text-white transition">Terms of Service</a>
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 pt-8 border-t border-slate-800 text-center text-xs text-slate-500">
            © {{ date('Y') }} ClassPortal. All rights reserved.
        </div>
    </footer>

    <!-- SYSTEM AUTHENTICATION MODAL DIALOG (Sign Up, Sign In, Reset password) -->
    <div id="auth-modal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-sm font-sans hidden">
        <div class="flex min-h-full items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-slate-200 max-w-md w-full p-8 shadow-2xl relative space-y-6 my-auto">
            <button
                onclick="toggleAuthModal()"
                class="absolute top-4 right-4 p-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-full cursor-pointer transition border-none"
            >
                ✕
            </button>

            <!-- Modal Heading Header -->
            <div class="text-center space-y-2">
                <span class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-600 text-white font-black text-2xl shadow-md mb-2">
                    S
                </span>
                <h3 id="modal-heading" class="text-xl font-black text-slate-900 leading-tight">
                    Welcome Back to ClassPortal
                </h3>
                <p id="modal-subheading" class="text-xs text-slate-400 font-semibold">
                    Log in to sync syllabus, CBT tests registries, and load class notes.
                </p>
            </div>

            <!-- Error or Success banners -->
            <div id="auth-error" class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold rounded-2xl text-left hidden"></div>
            <div id="auth-success" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold rounded-2xl text-left hidden"></div>

            <!-- Tabs for switching Modal state (Not shown in forgot mode) -->
            <div id="auth-tabs" class="bg-slate-100 p-1 rounded-2xl flex gap-1 text-center font-bold text-xs">
                <button
                    type="button"
                    onclick="setAuthMode('login')"
                    id="tab-login"
                    class="flex-1 py-1.5 rounded-xl transition cursor-pointer border-none bg-white text-slate-900 shadow-sm"
                >
                    Sign In
                </button>
                <button
                    type="button"
                    onclick="setAuthMode('signup')"
                    id="tab-signup"
                    class="flex-1 py-1.5 rounded-xl transition cursor-pointer border-none bg-transparent text-slate-700 hover:text-slate-900"
                >
                    Sign Up
                </button>
            </div>

            <!-- Operational Authentication Form -->
            <form id="auth-form" action="javascript:void(0)" method="POST" class="space-y-4 text-left">
                
                <div id="field-name" class="space-y-1 hidden">
                    <label class="text-xs font-black text-slate-600 block">Your Full Name:</label>
                    <input
                        type="text"
                        id="auth-name"
                        placeholder="e.g., Austin Nwaigbo"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none text-xs"
                    />
                </div>
                <div id="field-username" class="space-y-1 hidden">
                    <label class="text-xs font-black text-slate-600 block">Choose a Username (optional):</label>
                    <input
                        type="text"
                        id="auth-username"
                        placeholder="e.g., austin_nwaigbo"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none text-xs"
                    />
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-black text-slate-600 block">Username or Email Address:</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3 w-4 h-4 text-slate-400">👤</span>
                        <input
                            type="text" id="auth-login"
                            placeholder="e.g., admin or educator@school.com"
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none text-xs"
                        />
                    </div>
                </div>

                <div id="field-password" class="space-y-1">
                    <label class="text-xs font-black text-slate-600 block">Account Password:</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3 w-4 h-4 text-slate-400">🔒</span>
                        <input
                            type="password" id="auth-password"
                            placeholder="At least 8 characters"
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none text-xs"
                        />
                    </div>
                </div>

                <div id="field-confirm-password" class="space-y-1 hidden">
                    <label class="text-xs font-black text-slate-600 block">Confirm Password:</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3 w-4 h-4 text-slate-400">🔒</span>
                        <input
                            type="password" id="auth-confirm-password"
                            placeholder="Re-enter password for clearance"
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none text-xs"
                        />
                    </div>
                </div>

                <div id="field-role" class="space-y-1 hidden">
                    <label class="text-xs font-black text-slate-600 block">Describe Student or Teacher Role: </label>
                    <div class="grid grid-cols-2 gap-2 text-center text-xs font-semibold">
                        <button
                            type="button"
                            id="role-student"
                            onclick="setSignupRole('student')"
                            class="py-2 px-3 border rounded-xl cursor-pointer transition bg-indigo-50 border-indigo-400 text-indigo-700 font-extrabold"
                        >
                            🎓 Student Candidate
                        </button>
                        <button
                            type="button"
                            id="role-teacher"
                            onclick="setSignupRole('teacher')"
                            class="py-2 px-3 border rounded-xl cursor-pointer transition bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100"
                        >
                            ✍ Professional Teacher
                        </button>
                    </div>
                </div>

                <!-- Forgot password switch trigger link -->
                <div id="forgot-link" class="text-right">
                    <button
                        type="button"
                        onclick="setAuthMode('forgot')"
                        class="text-xs font-black text-slate-700 hover:text-indigo-600 transition cursor-pointer bg-transparent border-none p-0"
                    >
                        Forgot registration password?
                    </button>
                </div>

                <div id="back-to-login" class="text-left hidden">
                    <button
                        type="button"
                        onclick="setAuthMode('login')"
                        class="text-xs font-black text-indigo-600 hover:text-indigo-700 transition cursor-pointer bg-transparent border-none p-0"
                    >
                        Back to Log In
                    </button>
                </div>

                <!-- Submit trigger button -->
                <button
                    type="submit" id="auth-submit-btn"
                    class="w-full py-3 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-lg transition cursor-pointer uppercase tracking-wider border-none text-center flex items-center justify-center gap-2"
                >
                    Validate Credentials
                </button>
                
                </form>
        </div>
        </div>
    </div>
</div>

<script>
    // ============================================================
    // VoiceInputButton reusable component
    // ============================================================
    function createVoiceInputButton(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const container = document.querySelector(`[data-input="${inputId}"]`);
        if (!container) return;

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>';
            btn.className = 'inline-flex items-center justify-center opacity-60 text-slate-500 cursor-help p-1 rounded-md bg-slate-200';
            btn.title = 'Speech recognition not supported';
            btn.onclick = function() {
                const tooltip = document.createElement('div');
                tooltip.className = 'absolute bottom-full mb-2 right-0 z-50 bg-slate-900 text-white text-[10px] font-bold px-2 py-1.5 rounded-lg whitespace-nowrap shadow-xl';
                tooltip.textContent = 'Speech-to-text is not supported on this browser context.';
                this.parentElement.appendChild(tooltip);
                setTimeout(function() { tooltip.remove(); }, 3000);
            };
            container.appendChild(btn);
            return;
        }

        let recognition = null;
        let isListening = false;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>';
        btn.className = 'inline-flex items-center justify-center transition-all cursor-pointer border-none shrink-0 bg-slate-200 text-slate-700 hover:bg-slate-300 hover:text-slate-900 p-1 rounded-md';
        btn.title = 'Use speech-to-text (microphone input)';

        btn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (isListening) {
                try { recognition.stop(); } catch(e) {}
                isListening = false;
                btn.className = 'inline-flex items-center justify-center transition-all cursor-pointer border-none shrink-0 bg-slate-200 text-slate-700 hover:bg-slate-300 hover:text-slate-900 p-1 rounded-md';
                btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>';
            } else {
                if (!recognition) {
                    recognition = new SpeechRecognition();
                    recognition.continuous = false;
                    recognition.interimResults = false;
                    recognition.lang = 'en-US';
                    recognition.onend = function() {
                        isListening = false;
                        btn.className = 'inline-flex items-center justify-center transition-all cursor-pointer border-none shrink-0 bg-slate-200 text-slate-700 hover:bg-slate-300 hover:text-slate-900 p-1 rounded-md';
                        btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>';
                    };
                    recognition.onresult = function(event) {
                        const transcript = event.results[0][0].transcript;
                        if (transcript) {
                            const current = input.value.trim();
                            input.value = current ? current + ' ' + transcript : transcript;
                            input.dispatchEvent(new Event('input'));
                        }
                    };
                    recognition.onerror = function(event) {
                        console.error('Speech error:', event.error);
                        isListening = false;
                        btn.className = 'inline-flex items-center justify-center transition-all cursor-pointer border-none shrink-0 bg-slate-200 text-slate-700 hover:bg-slate-300 hover:text-slate-900 p-1 rounded-md';
                        btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>';
                        const tooltip = document.createElement('div');
                        tooltip.className = 'absolute bottom-full mb-2 right-0 z-50 bg-slate-900 text-white text-[10px] font-bold px-2 py-1.5 rounded-lg whitespace-nowrap shadow-xl';
                        tooltip.textContent = event.error === 'not-allowed' ? 'Microphone permission blocked.' : 'Speech error: ' + event.error;
                        btn.parentElement.appendChild(tooltip);
                        setTimeout(function() { tooltip.remove(); }, 3000);
                    };
                }
                try {
                    recognition.start();
                    isListening = true;
                    btn.className = 'inline-flex items-center justify-center transition-all cursor-pointer border-none shrink-0 bg-rose-500 text-white animate-pulse p-1 rounded-md';
                    btn.innerHTML = '<span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span><svg class="w-3.5 h-3.5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg></span>';
                } catch(e) {
                    console.warn('Already started:', e);
                }
            }
        };

        container.appendChild(btn);
    }

    // ============================================================
    // Auth Modal state management
    // ============================================================
    let authMode = 'login';
    let signupRole = 'student';

    function toggleAuthModal() {
        const modal = document.getElementById('auth-modal');
        modal.classList.toggle('hidden');
    }

    function setAuthMode(mode) {
        authMode = mode;
        const fieldName = document.getElementById('field-name');
        const fieldPassword = document.getElementById('field-password');
        const fieldConfirmPassword = document.getElementById('field-confirm-password');
        const fieldRole = document.getElementById('field-role');
        const authTabs = document.getElementById('auth-tabs');
        const forgotLink = document.getElementById('forgot-link');
        const backToLogin = document.getElementById('back-to-login');
        const modalHeading = document.getElementById('modal-heading');
        const modalSubheading = document.getElementById('modal-subheading');
        const authSubmit = document.getElementById('auth-submit-btn');
        const tabLogin = document.getElementById('tab-login');
        const tabSignup = document.getElementById('tab-signup');

        document.getElementById('auth-error').classList.add('hidden');
        document.getElementById('auth-success').classList.add('hidden');

        if (mode === 'login') {
            modalHeading.textContent = 'Welcome Back to ClassPortal';
            modalSubheading.textContent = 'Log in to sync syllabus, CBT tests registries, and load class notes.';
            authSubmit.textContent = 'Validate Credentials';
            fieldName.classList.add('hidden');
            document.getElementById('field-username').classList.add('hidden');
            fieldPassword.classList.remove('hidden');
            fieldConfirmPassword.classList.add('hidden');
            fieldRole.classList.add('hidden');
            authTabs.classList.remove('hidden');
            forgotLink.classList.remove('hidden');
            backToLogin.classList.add('hidden');
            tabLogin.className = 'flex-1 py-1.5 rounded-xl transition cursor-pointer border-none bg-white text-slate-900 shadow-sm';
            tabSignup.className = 'flex-1 py-1.5 rounded-xl transition cursor-pointer border-none bg-transparent text-slate-700 hover:text-slate-900';
        } else         if (mode === 'signup') {
            modalHeading.textContent = 'Create Educator or Student Account';
            modalSubheading.textContent = 'Get your customized Nigeria and WAEC/NECO educational dashboard.';
            authSubmit.textContent = 'Create Academic Profile';
            fieldName.classList.remove('hidden');
            document.getElementById('field-username').classList.remove('hidden');
            fieldPassword.classList.remove('hidden');
            fieldConfirmPassword.classList.remove('hidden');
            fieldRole.classList.remove('hidden');
            authTabs.classList.remove('hidden');
            forgotLink.classList.add('hidden');
            backToLogin.classList.add('hidden');
            tabSignup.className = 'flex-1 py-1.5 rounded-xl transition cursor-pointer border-none bg-white text-slate-900 shadow-sm';
            tabLogin.className = 'flex-1 py-1.5 rounded-xl transition cursor-pointer border-none bg-transparent text-slate-700 hover:text-slate-900';
        } else if (mode === 'forgot') {
            modalHeading.textContent = 'Recover Access Credentials';
            modalSubheading.textContent = 'Enter registered email address to continue validation.';
            authSubmit.textContent = 'Recover My Account';
            fieldName.classList.add('hidden');
            document.getElementById('field-username').classList.add('hidden');
            fieldPassword.classList.add('hidden');
            fieldConfirmPassword.classList.add('hidden');
            fieldRole.classList.add('hidden');
            authTabs.classList.add('hidden');
            forgotLink.classList.add('hidden');
            backToLogin.classList.remove('hidden');
        }
    }

    function setSignupRole(role) {
        signupRole = role;
        const roleStudent = document.getElementById('role-student');
        const roleTeacher = document.getElementById('role-teacher');
        if (role === 'student') {
            roleStudent.className = 'py-2 px-3 border rounded-xl cursor-pointer transition bg-indigo-50 border-indigo-400 text-indigo-700 font-extrabold';
            roleTeacher.className = 'py-2 px-3 border rounded-xl cursor-pointer transition bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100';
        } else {
            roleTeacher.className = 'py-2 px-3 border rounded-xl cursor-pointer transition bg-indigo-50 border-indigo-400 text-indigo-700 font-extrabold';
            roleStudent.className = 'py-2 px-3 border rounded-xl cursor-pointer transition bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100';
        }
    }

    // ============================================================
    // Initialize all features on DOM ready
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize voice input buttons
        createVoiceInputButton('feedback-name');
        createVoiceInputButton('feedback-email');
        createVoiceInputButton('feedback-message');
        createVoiceInputButton('exam-code-input');
        createVoiceInputButton('auth-login');

        // Attach auth modal triggers
        document.querySelectorAll('button').forEach(function(btn) {
            var text = btn.textContent || '';
            if (text.includes('Enter Portals') || text.includes('Get Started Freely') || text.includes('Access Portals Directly') || text.includes('Take CBT Exam')) {
                btn.addEventListener('click', function(e) { toggleAuthModal(); });
            }
        });

        // Auth form submit (API-based AJAX)
        document.getElementById('auth-form').addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('auth-submit-btn');
            var errorEl = document.getElementById('auth-error');
            var login = document.getElementById('auth-login').value;
            var password = document.getElementById('auth-password').value;
            var name = document.getElementById('auth-name') ? document.getElementById('auth-name').value : '';

            errorEl.classList.add('hidden');

            if (authMode === 'forgot') {
                fetch('/api/auth/reset', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ email: login })
                })
                .then(function(r) { return r.text().then(function(body) { try { return JSON.parse(body); } catch(e) { return { success: false, error: 'HTTP ' + r.status + ': ' + body.substring(0, 200) }; } }); })
                .then(function(data) {
                    if (data.success) {
                        toggleAuthModal();
                        alert(data.message);
                    } else {
                        errorEl.textContent = data.error || 'Reset failed.';
                        errorEl.classList.remove('hidden');
                    }
                })
                .catch(function(err) {
                    errorEl.textContent = 'Error: ' + err.message;
                    errorEl.classList.remove('hidden');
                });
                return;
            }

            if (authMode === 'login') {
                btn.disabled = true;
                btn.textContent = 'Signing in...';
                fetch('/api/auth/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ login: login, password: password })
                })
                .then(function(r) { return r.text().then(function(body) { try { return JSON.parse(body); } catch(e) { return { success: false, error: 'HTTP ' + r.status + ': ' + body.substring(0, 200) }; } }); })
                .then(function(data) {
                    if (data.success) {
                        var role = data.user.role || 'student';
                        window.location.href = '/' + role + '/dashboard';
                    } else {
                        errorEl.textContent = data.error || 'Invalid credentials.';
                        errorEl.classList.remove('hidden');
                    }
                })
                .catch(function(err) {
                    errorEl.textContent = 'Error: ' + err.message;
                    errorEl.classList.remove('hidden');
                })
                .finally(function() {
                    btn.disabled = false;
                    btn.textContent = 'Validate Credentials';
                });
            } else if (authMode === 'signup') {
                btn.disabled = true;
                btn.textContent = 'Creating account...';
                var username = document.getElementById('auth-username') ? document.getElementById('auth-username').value.trim() : '';
                fetch('/api/auth/register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ name: name, email: login, username: username, password: password, role: signupRole })
                })
                .then(function(r) {
                    return r.text().then(function(body) {
                        try { return JSON.parse(body); }
                        catch(e) { return { success: false, error: 'HTTP ' + r.status + ': ' + body.substring(0, 200) }; }
                    });
                })
                .then(function(data) {
                    if (data.success) {
                        var role = data.user.role || 'student';
                        window.location.href = '/' + role + '/dashboard';
                    } else {
                        errorEl.textContent = data.error || 'Registration failed.';
                        errorEl.classList.remove('hidden');
                    }
                })
                .catch(function(err) {
                    errorEl.textContent = 'Error: ' + err.message;
                    console.error('Register error:', err);
                    errorEl.classList.remove('hidden');
                })
                .finally(function() {
                    btn.disabled = false;
                    btn.textContent = 'Create Academic Profile';
                });
            }
        });

        // ============================================================
        // Active exams from API
        // ============================================================
        var allExams = [];
        var examCodeInput = document.getElementById('exam-code-input');

        function loadActiveExams() {
            var loadingEl = document.getElementById('exams-loading');
            var emptyEl = document.getElementById('exams-empty');
            var listEl = document.getElementById('exams-list');
            var countEl = document.getElementById('exams-count');

            loadingEl.classList.remove('hidden');
            emptyEl.classList.add('hidden');
            listEl.classList.add('hidden');

            fetch('/api/exams')
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    loadingEl.classList.add('hidden');
                    allExams = data.exams || [];
                    var activeExams = allExams.filter(function(e) { return e.isPublished; });
                    countEl.textContent = activeExams.length;

                    if (activeExams.length === 0) {
                        emptyEl.classList.remove('hidden');
                        return;
                    }

                    listEl.classList.remove('hidden');
                    listEl.innerHTML = '';
                    activeExams.forEach(function(ex) {
                        var card = document.createElement('div');
                        card.className = 'p-3.5 bg-white hover:bg-blue-50/10 border border-slate-150 rounded-2xl flex flex-col justify-between gap-3 hover:border-blue-200 transition group';
                        card.innerHTML = '<div class="space-y-1">'
                            + '<div class="flex items-center justify-between">'
                            + '<span class="text-[9px] bg-indigo-50 text-indigo-700 py-0.5 px-2 rounded-full font-bold">' + escapeHtml(ex.subject) + '</span>'
                            + '<span class="text-[10px] text-slate-400 font-mono font-bold">⏱ ' + (ex.duration || 0) + ' Mins</span>'
                            + '</div>'
                            + '<h5 class="text-xs font-black text-slate-800 line-clamp-1 group-hover:text-blue-900 transition">' + escapeHtml(ex.title) + '</h5>'
                            + '<p class="text-[9px] text-slate-400 font-semibold uppercase">By ' + escapeHtml(ex.creatorName || 'Educator') + '</p>'
                            + '</div>'
                            + '<button onclick="joinExam(\'' + ex.id + '\')" class="w-full py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white rounded-lg text-xs font-extrabold transition text-center cursor-pointer border border-emerald-100">Start and Answer Questions ✨</button>';
                        listEl.appendChild(card);
                    });
                })
                .catch(function(err) {
                    console.error('Failed to load exams:', err);
                    loadingEl.classList.add('hidden');
                    emptyEl.classList.remove('hidden');
                });
        }

        function escapeHtml(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(text));
            return div.innerHTML;
        }

        window.joinExam = function(examId) {
            var match = allExams.find(function(e) { return e.id === examId; });
            if (match) {
                window.location.href = '/student/exam/' + examId;
            }
        };

        // Exam code form submit
        document.getElementById('exam-code-form').addEventListener('submit', function(e) {
            e.preventDefault();
            var errorEl = document.getElementById('exam-code-error');
            var successEl = document.getElementById('exam-code-success');
            errorEl.classList.add('hidden');
            successEl.classList.add('hidden');

            var input = examCodeInput.value.trim();
            if (!input) return;

            var examId = input;
            if (input.includes('examId=')) {
                var parts = input.split('examId=');
                if (parts.length > 1) examId = parts[1].split('&')[0];
            } else if (input.includes('#/exam/')) {
                var parts = input.split('#/exam/');
                if (parts.length > 1) examId = parts[1];
            }

            var match = allExams.find(function(e) {
                return e.id.toLowerCase() === examId.toLowerCase() || e.title.toLowerCase().includes(examId.toLowerCase());
            });

            if (match) {
                successEl.textContent = '✓ Exam "' + match.title + '" found! Opening exam room...';
                successEl.classList.remove('hidden');
                setTimeout(function() {
                    window.location.href = '/student/exam/' + match.id;
                }, 1000);
            } else {
                errorEl.textContent = '⚠️ Exam not found. Please double check the ID/URL code.';
                errorEl.classList.remove('hidden');
            }
        });

        // Feedback form submit
        document.getElementById('contact-form').addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('feedback-submit-btn');
            var successEl = document.getElementById('feedback-success');
            var errorEl = document.getElementById('feedback-error');
            successEl.classList.add('hidden');
            errorEl.classList.add('hidden');

            var name = document.getElementById('feedback-name').value.trim();
            var email = document.getElementById('feedback-email').value.trim();
            var message = document.getElementById('feedback-message').value.trim();
            if (!name || !email || !message) return;

            btn.disabled = true;
            btn.textContent = 'Transmitting Log...';

            fetch('/api/feedback', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: name, email: email, message: message })
            })
            .then(function(res) {
                if (res.ok) {
                    successEl.classList.remove('hidden');
                    document.getElementById('feedback-name').value = '';
                    document.getElementById('feedback-email').value = '';
                    document.getElementById('feedback-message').value = '';
                } else {
                    errorEl.classList.remove('hidden');
                }
            })
            .catch(function() {
                errorEl.classList.remove('hidden');
            })
            .finally(function() {
                btn.disabled = false;
                btn.textContent = 'Submit Help Inquiry';
            });
        });

        // Load exams
        loadActiveExams();
    });
</script>
@endsection
