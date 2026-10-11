@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h1 class="text-2xl font-bold text-slate-800 mb-6">Create New Exam</h1>
            
            <form action="{{ route('exam.store') }}" method="POST" id="examForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-slate-700 mb-2">Exam Title</label>
                    <input type="text" name="title" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="grid gap-4 mb-4 md:grid-cols-2">
                    <div>
                        <label class="block text-slate-700 mb-2">Subject</label>
                        <select name="subject" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option>Mathematics</option>
                            <option>English Language</option>
                            <option>Physics</option>
                            <option>Chemistry</option>
                            <option>Biology</option>
                            <option>Social and Citizenship Education</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-2">Duration (minutes)</label>
                        <input type="number" name="duration" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid gap-4 mb-4 md:grid-cols-2">
                    <div>
                        <label class="block text-slate-700 mb-2">Topic</label>
                        <input type="text" name="topic" placeholder="e.g., Algebra" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-slate-700 mb-2">Sub-topic (Optional)</label>
                        <input type="text" name="subTopic" placeholder="e.g., Quadratic Equations" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-slate-700 mb-2">Default Marks Per Question</label>
                    <input type="number" name="defaultMarks" value="5" min="1" max="100" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-slate-400 mt-1">Each question will carry this number of marks unless overridden individually.</p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-slate-700 mb-2">Instructions</label>
                    <textarea name="instructions" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <!-- Questions Section -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold text-slate-800">Questions</h2>
                        <button type="button" id="addQuestion" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition">
                            Add Question
                        </button>
                    </div>
                    <div id="questionsContainer"></div>
                </div>

                <div class="flex gap-4">
                    <a href="{{ route('teacher.dashboard') }}" class="flex-1 py-3 border border-slate-300 text-slate-700 text-center rounded-lg font-bold hover:bg-slate-50 transition">
                        Cancel
                    </a>
                    <button type="submit" class="flex-1 py-3 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition">
                        Create Exam
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let questionCount = 0;

document.getElementById('addQuestion').addEventListener('click', () => {
    questionCount++;
    const container = document.getElementById('questionsContainer');
    const div = document.createElement('div');
    div.className = 'mb-6 p-4 border border-slate-200 rounded-xl';
    div.innerHTML = `
        <div class="flex justify-between items-start mb-3">
            <h3 class="font-semibold text-slate-800">Question ${questionCount}</h3>
            <button type="button" onclick="this.closest('div').remove()" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
        </div>
        <div class="mb-3">
            <label class="block text-slate-700 mb-1 text-sm">Question Text</label>
            <input type="text" name="questions[${questionCount}][question]" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
        </div>
        <div class="grid gap-2 mb-3 md:grid-cols-2">
            <div>
                <label class="block text-slate-700 mb-1 text-xs">Option A</label>
                <input type="text" name="questions[${questionCount}][optionA]" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-slate-700 mb-1 text-xs">Option B</label>
                <input type="text" name="questions[${questionCount}][optionB]" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-slate-700 mb-1 text-xs">Option C</label>
                <input type="text" name="questions[${questionCount}][optionC]" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-slate-700 mb-1 text-xs">Option D</label>
                <input type="text" name="questions[${questionCount}][optionD]" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
        </div>
        <div class="mb-3">
            <label class="block text-slate-700 mb-1 text-sm">Correct Answer</label>
            <select name="questions[${questionCount}][correctAnswer]" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>
        </div>
    `;
    container.appendChild(div);
});

// Add first question by default
document.getElementById('addQuestion').click();
</script>
@endsection
