<?php

namespace App\Helpers;

class CurriculumData
{
    public static function getSubjects(): array
    {
        return [
            'Mathematics', 'English Language', 'Physics', 'Chemistry', 'Biology',
            'Agricultural Science', 'Economics', 'Commerce', 'Accounting',
            'Government', 'History', 'Geography', 'Literature', 'ICT',
            'Civic Education', 'Basic Science', 'Basic Technology',
            'Social Studies', 'Home Economics', 'Physical Education',
            'Christian Religious Studies', 'Islamic Studies',
            'French', 'Yoruba', 'Igbo', 'Hausa', 'Music', 'Art & Design',
            'Intermediate Science', 'Cultural and Creative Arts (CCA)',
            'Nigerian History', 'Artificial Intelligence', 'Phonics',
            'Vocational Studies', 'Physical and Health Education (PHE)',
            'Coding', 'Verbal Reasoning', 'Craft and Art',
        ];
    }

    public static function getClasses(): array
    {
        return [
            // Nursery domain - ordered lowest to highest: Reception -> Foundation -> Nursery 1 -> Nursery 2
            'Reception', 'Foundation', 'Nursery 1', 'Nursery 2',
            'Primary 1', 'Primary 2', 'Primary 3', 'Primary 4', 'Primary 5', 'Primary 6',
            'JSS1', 'JSS2', 'JSS3',
            'SS1', 'SS2', 'SS3',
        ];
    }

    public static function getTerms(): array
    {
        return ['First Term', 'Second Term', 'Third Term'];
    }

    public static function getWeeks(): array
    {
        return range(1, 13);
    }

    public static function getClassCategory(string $class): string
    {
        if (in_array($class, ['Reception', 'Foundation', 'Nursery 1', 'Nursery 2'], true)) return 'nursery';
        if (str_starts_with($class, 'Primary')) return 'primary';
        if (str_starts_with($class, 'JSS')) return 'junior';
        if (str_starts_with($class, 'SS')) return 'senior';
        return 'primary';
    }

    public static function getAgeRange(string $class): string
    {
        return match (true) {
            $class === 'Reception' => '2 – 3 years',
            $class === 'Foundation' => '3 – 4 years',
            $class === 'Nursery 1' => '4 – 5 years',
            $class === 'Nursery 2' => '5 – 6 years',
            $class === 'Primary 1' => '6 – 7 years',
            $class === 'Primary 2' => '7 – 8 years',
            $class === 'Primary 3' => '8 – 9 years',
            $class === 'Primary 4' => '9 – 10 years',
            $class === 'Primary 5' => '10 – 11 years',
            $class === 'Primary 6' => '11 – 12 years',
            str_starts_with($class, 'JSS') => '11 – 14 years',
            str_starts_with($class, 'SS') => '14 – 17 years',
            default => 'varies',
        };
    }

    public static function getSchemeOfWork(string $subject, string $class, string $term): array
    {
        $key = strtolower(str_replace(' ', '_', $subject));
        $method = 'scheme' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
        
        if (method_exists(self::class, $method)) {
            $all = self::$method();
            $classLevel = self::getClassCategory($class);
            $termIndex = array_search($term, self::getTerms());
            $classBase = match(true) {
                in_array($class, ['Reception', 'Foundation', 'Nursery 1', 'Nursery 2'], true) => 'nursery',
                str_starts_with($class, 'Primary') => 'primary',
                str_starts_with($class, 'JSS') => 'jss',
                str_starts_with($class, 'SS') => 'ss',
                default => 'primary'
            };
            $classNum = preg_replace('/[^0-9]/', '', $class);
            $key = $classBase . $classNum;
            if (isset($all[$key][$termIndex])) {
                return $all[$key][$termIndex];
            }
        }

        return self::fallbackScheme($subject, $class, $term);
    }

    private static function fallbackScheme(string $subject, string $class, string $term): array
    {
        $weeks = [];
        $topics = [
            'Introduction to ' . $subject,
            'Basic Concepts in ' . $subject,
            'Fundamentals of ' . $subject,
            'Key Principles of ' . $subject,
            'Applications of ' . $subject,
            'Advanced Topics in ' . $subject,
            'Revision and Review',
            'Assessment and Evaluation',
            'Project Work',
            'Practical Applications',
            'Case Studies',
            'Comprehensive Revision',
            'End of Term Examination',
        ];
        for ($w = 1; $w <= 13; $w++) {
            $weeks[] = [
                'week' => $w,
                'topic' => $topics[$w - 1] ?? 'General Revision',
                'subtopics' => ['Core concepts', 'Key definitions', 'Practical applications', 'Real-world examples', 'Common misconceptions'],
            ];
        }
        return $weeks;
    }

    private static function schemeMathematics(): array
    {
        return [
            'primary1' => [
                [
                    ['week' => 1, 'topic' => 'Counting and Writing Numbers 1-100', 'subtopics' => ['Identification of numbers 1-100', 'Writing numbers in words', 'Place value of digits']],
                    ['week' => 2, 'topic' => 'Addition of Whole Numbers', 'subtopics' => ['Addition of 2-digit numbers without regrouping', 'Addition with regrouping', 'Word problems on addition']],
                    ['week' => 3, 'topic' => 'Subtraction of Whole Numbers', 'subtopics' => ['Subtraction of 2-digit numbers', 'Subtraction with borrowing', 'Word problems on subtraction']],
                    ['week' => 4, 'topic' => 'Multiplication of Whole Numbers', 'subtopics' => ['Multiplication as repeated addition', 'Multiplication tables 2-5', 'Simple multiplication sums']],
                    ['week' => 5, 'topic' => 'Division of Whole Numbers', 'subtopics' => ['Sharing equally', 'Division as grouping', 'Simple division sums']],
                    ['week' => 6, 'topic' => 'Fractions', 'subtopics' => ['Identifying halves and quarters', 'Understanding thirds', 'Shading fractions of shapes']],
                    ['week' => 7, 'topic' => 'Shapes and Patterns', 'subtopics' => ['Identifying 2D shapes', 'Creating patterns with shapes', 'Properties of shapes']],
                    ['week' => 8, 'topic' => 'Measurement of Length', 'subtopics' => ['Comparing lengths', 'Using rulers', 'Measuring in metres and centimetres']],
                    ['week' => 9, 'topic' => 'Measurement of Mass', 'subtopics' => ['Comparing weights', 'Using weighing scales', 'Kilograms and grams']],
                    ['week' => 10, 'topic' => 'Time and Calendar', 'subtopics' => ['Telling time (hours and half-hours)', 'Days of the week', 'Months of the year']],
                    ['week' => 11, 'topic' => 'Money', 'subtopics' => ['Identifying coins and notes', 'Adding money', 'Simple money transactions']],
                    ['week' => 12, 'topic' => 'Revision', 'subtopics' => ['Review of all topics', 'Practice exercises', 'Problem-solving']],
                    ['week' => 13, 'topic' => 'Examination', 'subtopics' => ['End of term assessment']],
                ],
                [
                    ['week' => 1, 'topic' => 'Counting Numbers 101-500', 'subtopics' => ['Number identification', 'Writing in words and figures', 'Ordering numbers']],
                    ['week' => 2, 'topic' => 'Addition and Subtraction Review', 'subtopics' => ['Adding 3-digit numbers', 'Subtracting 3-digit numbers', 'Checking answers']],
                    ['week' => 3, 'topic' => 'Multiplication Tables 6-10', 'subtopics' => ['Learning tables 6-10', 'Multiplication of 2-digit by 1-digit', 'Word problems']],
                    ['week' => 4, 'topic' => 'Division with Remainders', 'subtopics' => ['Dividing 2-digit numbers', 'Interpreting remainders', 'Checking division']],
                    ['week' => 5, 'topic' => 'Fractions and Decimals', 'subtopics' => ['Equivalent fractions', 'Introduction to decimals', 'Converting fractions to decimals']],
                    ['week' => 6, 'topic' => '3D Shapes', 'subtopics' => ['Identifying 3D shapes', 'Properties of 3D shapes', 'Nets of shapes']],
                    ['week' => 7, 'topic' => 'Capacity and Volume', 'subtopics' => ['Comparing capacities', 'Litres and millilitres', 'Measuring volume']],
                    ['week' => 8, 'topic' => 'Symmetry', 'subtopics' => ['Lines of symmetry', 'Creating symmetrical shapes', 'Completing symmetrical patterns']],
                    ['week' => 9, 'topic' => 'Data Collection and Pictograms', 'subtopics' => ['Collecting data', 'Drawing pictograms', 'Interpreting pictograms']],
                    ['week' => 10, 'topic' => 'Temperature', 'subtopics' => ['Reading thermometers', 'Degrees Celsius', 'Comparing temperatures']],
                    ['week' => 11, 'topic' => 'Money Transactions', 'subtopics' => ['Calculating change', 'Budgeting', 'Saving money']],
                    ['week' => 12, 'topic' => 'Revision', 'subtopics' => ['Review of term topics', 'Comprehensive exercises', 'Problem-solving']],
                    ['week' => 13, 'topic' => 'Examination', 'subtopics' => ['End of term assessment']],
                ],
                [
                    ['week' => 1, 'topic' => 'Numbers 501-1000', 'subtopics' => ['Reading and writing large numbers', 'Place value up to thousands', 'Comparing and ordering']],
                    ['week' => 2, 'topic' => 'Addition and Subtraction of Large Numbers', 'subtopics' => ['Adding 3-digit with regrouping', 'Subtracting with borrowing', 'Checking with inverse']],
                    ['week' => 3, 'topic' => 'Multiplication of 2-digit by 2-digit', 'subtopics' => ['Multiplication using grid method', 'Standard multiplication', 'Word problems']],
                    ['week' => 4, 'topic' => 'Division of 3-digit by 1-digit', 'subtopics' => ['Long division method', 'Division with remainders', 'Checking with multiplication']],
                    ['week' => 5, 'topic' => 'Proper and Improper Fractions', 'subtopics' => ['Types of fractions', 'Converting improper to mixed', 'Ordering fractions']],
                    ['week' => 6, 'topic' => 'Angles', 'subtopics' => ['Types of angles', 'Measuring with protractors', 'Drawing angles']],
                    ['week' => 7, 'topic' => 'Perimeter and Area', 'subtopics' => ['Perimeter of polygons', 'Area of rectangles', 'Estimating area']],
                    ['week' => 8, 'topic' => 'Time and Duration', 'subtopics' => ['Telling time to minutes', 'Calculating duration', 'Using timetables']],
                    ['week' => 9, 'topic' => 'Probability', 'subtopics' => ['Likely and unlikely events', 'Recording outcomes', 'Simple probability']],
                    ['week' => 10, 'topic' => 'Graphs and Charts', 'subtopics' => ['Bar charts', 'Line graphs', 'Interpreting data']],
                    ['week' => 11, 'topic' => 'Money and Financial Literacy', 'subtopics' => ['Saving and spending', 'Simple interest concept', 'Financial planning']],
                    ['week' => 12, 'topic' => 'Revision', 'subtopics' => ['Review of all term topics', 'Practice examinations', 'Problem-solving']],
                    ['week' => 13, 'topic' => 'Examination', 'subtopics' => ['End of term assessment']],
                ],
            ],
            'primary2' => [
                [
                    ['week' => 1, 'topic' => 'Whole Numbers 1-1000', 'subtopics' => ['Counting up to 1000', 'Place value (units, tens, hundreds)', 'Writing numbers in words']],
                    ['week' => 2, 'topic' => 'Addition of Whole Numbers', 'subtopics' => ['Addition with sums up to 1000', 'Addition with 3 addends', 'Word problems involving addition']],
                    ['week' => 3, 'topic' => 'Subtraction of Whole Numbers', 'subtopics' => ['Subtraction from 3-digit numbers', 'Subtraction with borrowing across columns', 'Checking subtraction with addition']],
                    ['week' => 4, 'topic' => 'Multiplication', 'subtopics' => ['Multiplication tables 1-12', 'Multiplying 2-digit by 1-digit', 'Multiplication word problems']],
                    ['week' => 5, 'topic' => 'Division', 'subtopics' => ['Division as sharing equally', 'Division with remainders', 'Division word problems']],
                    ['week' => 6, 'topic' => 'Fractions', 'subtopics' => ['Identifying halves, thirds, quarters', 'Comparing fractions', 'Adding and subtracting like fractions']],
                    ['week' => 7, 'topic' => 'Decimals', 'subtopics' => ['Tenths as decimals', 'Hundredths as decimals', 'Adding decimals']],
                    ['week' => 8, 'topic' => '2D Shapes and Properties', 'subtopics' => ['Quadrilaterals', 'Triangles by sides/angles', 'Properties and classification']],
                    ['week' => 9, 'topic' => 'Measurement: Length, Mass, Capacity', 'subtopics' => ['Converting units (m/cm, kg/g, L/mL)', 'Adding and subtracting measurements', 'Estimating measurements']],
                    ['week' => 10, 'topic' => 'Area and Perimeter', 'subtopics' => ['Finding perimeter of rectangles', 'Finding area by counting squares', 'Area of rectangles']],
                    ['week' => 11, 'topic' => 'Time and Money', 'subtopics' => ['Time to 5-minute intervals', 'Adding and subtracting money', 'Calculating change']],
                    ['week' => 12, 'topic' => 'Statistics', 'subtopics' => ['Collecting data', 'Drawing bar charts', 'Interpreting information']],
                    ['week' => 13, 'topic' => 'Examination', 'subtopics' => ['End of term revision and test']],
                ],
                [[], [], []],
                [[], [], []],
            ],
        ];
    }

    public static function getCurriculumPrompt(string $subject, string $class, string $term, string $topic): string
    {
        $category = self::getClassCategory($class);
        $age = self::getAgeRange($class);

        return <<<PROMPT
You are a curriculum expert generating educational content.
Curriculum: Standard international curriculum
Subject: {$subject}
Class: {$class} (Age range: {$age})
Term: {$term}
Topic: {$topic}

Educational Level Context:
- NURSERY (Reception, Foundation, Nursery 1-2): Play-based, sensory, early literacy/numeracy, ages 2-6, very simple language and concrete play examples (Reception lowest)
- PRIMARY (Primary 1-6): Foundational concepts, simple language, concrete examples, play-based learning, basic literacy and numeracy
- JUNIOR SECONDARY (JSS1-3): Intermediate concepts, introduction to abstract thinking, subject specialization begins
- SENIOR SECONDARY (SS1-3): Advanced concepts, critical thinking, examination preparation

Content Requirements:
1. Align with standard curriculum standards for the specified class level
2. Use globally relatable, real-world examples without favoring any specific country or region — do not mention Nigeria, Nigerian cities/states like Lagos or Kano, or phrases like "in the Nigerian context"
3. Include practical, classroom-ready content
4. Appropriate vocabulary and complexity for the specified class level
5. Follow approved Scheme of Work for the subject/class/term
PROMPT;
    }
}
