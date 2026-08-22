<?php

namespace App\Helpers;

/**
 * @deprecated All content generation is now handled by GeminiService via AIController.
 * This class is retained only for reference and is no longer called by any code.
 */
class ContentGenerator
{
    private static array $subjectQuestionBanks = [];

    /**
     * Reset the static question bank cache so the next generation
     * starts from a completely fresh state with no cached questions.
     */
    public static function reset(): void
    {
        self::$subjectQuestionBanks = [];
    }

    public static function generateLessonPlan(string $subject, string $class, string $term, int $week, string $topic, string $schoolName, string $teacherName, string $duration, string $ageRange): array
    {
        $objectives = self::generateObjectives($subject, $class, $topic);
        $materials = self::generateMaterials($subject, $class, $topic);
        $previousKnowledge = self::generatePreviousKnowledge($subject, $class, $topic);
        $steps = self::generateLessonSteps($objectives, $subject, $class, $topic);
        $evaluation = self::generateEvaluation($objectives, $subject, $topic);
        $assignment = self::generateAssignment($subject, $class, $topic, 'lesson_plan');
        $summary = self::generateSummary($subject, $topic, $objectives);
        $conclusion = self::generateConclusion($subject, $topic);

        return compact('objectives', 'materials', 'previousKnowledge', 'steps', 'evaluation', 'assignment', 'summary', 'conclusion');
    }

    public static function generateLessonNote(string $subject, string $class, string $term, int $week, string $topic, string $periods, string $difficulty, string $ageRange): array
    {
        $scheme = CurriculumData::getSchemeOfWork($subject, $class, $term);
        $weekSubtopics = [];
        foreach ($scheme as $s) {
            if (($s['week'] ?? 0) == $week) {
                $weekSubtopics = $s['subtopics'] ?? [];
                break;
            }
        }

        $subtopics = !empty($weekSubtopics)
            ? array_map(fn($st) => $st . ' (' . $topic . ')', $weekSubtopics)
            : self::generateSubtopics($subject, $class, $topic);

        $learningObjectives = self::generateObjectives($subject, $class, $topic);
        $introduction = self::generateIntroduction($subject, $class, $topic);
        $content = self::generateContentHtml($subject, $class, $topic, $subtopics, $difficulty);
        $examples = self::generateExamples($subject, $class, $topic, $difficulty);
        $activities = self::generateActivities($subject, $class, $topic);
        $summary = self::generateSummary($subject, $topic, $learningObjectives);
        $conclusion = self::generateConclusion($subject, $topic);
        $evaluationQuestions = self::generateEvaluationQuestions($subject, $topic, $learningObjectives);
        $assignment = self::generateAssignment($subject, $class, $topic, 'lesson_note');
        $detailedNote = self::generateDetailedNote($subject, $class, $topic, $subtopics, $learningObjectives, $difficulty);

        return compact('topic', 'subtopics', 'learningObjectives', 'introduction', 'content', 'examples', 'activities', 'summary', 'conclusion', 'evaluationQuestions', 'assignment', 'detailedNote');
    }

    public static function generateQuestions(string $subject, string $topic, int $count, bool $includeTheory): array
    {
        $objectives = [];

        // Try to get topic-specific questions first, then fall back to subject-wide bank
        $bank = self::getQuestionBank($subject, $topic);
        if (!empty($bank)) {
            // Shuffle to vary order, then cycle through with offset to avoid same sequence
            $bankCopy = $bank;
            shuffle($bankCopy);
            $bankSize = count($bankCopy);
            $offset = crc32($topic) % $bankSize;
            for ($i = 0; $i < $count; $i++) {
                $idx = ($offset + $i) % $bankSize;
                $q = $bankCopy[$idx];
                $q['id'] = $i + 1;
                $objectives[] = $q;
            }
        } else {
            // No bank questions available — use archetype generator
            for ($i = 1; $i <= $count; $i++) {
                $objectives[] = self::generateSingleQuestion($subject, $topic, $i + ($i * 7));
            }
        }

        $result = ['objectives' => $objectives];
        if ($includeTheory) {
            $result['theoryQuestions'] = self::generateTheoryQuestions($subject, $topic);
            $result['essayQuestions'] = self::generateEssayQuestions($subject, $topic);
            $result['structuredQuestions'] = self::generateStructuredQuestions($subject, $topic);
        }
        return $result;
    }

    /**
     * Generate multiple-choice questions using the archetype engine only.
     * Unlike generateQuestions(), this skips the static question bank, so
     * EVERY generated stem explicitly embeds the topic keyword. This makes the
     * questions pass strict topic-stem validation for any subject/topic and is
     * used as an offline top-up / last-resort when the AI is unavailable.
     */
    public static function generateTopicAnchoredQuestions(string $subject, string $topic, int $count): array
    {
        $items = [];
        for ($i = 1; $i <= $count; $i++) {
            $items[] = self::generateSingleQuestion($subject, $topic, $i + ($i * 7));
        }
        return $items;
    }

    private static function getQuestionBank(string $subject, string $topic): array
    {
        $key = strtolower(str_replace(' ', '_', $subject));
        $questions = self::getSubjectQuestions($key);
        if (empty($questions)) {
            return [];
        }

        $filtered = array_filter($questions, fn($q) => empty($q['topic']) || stripos($q['topic'], $topic) !== false || stripos($topic, $q['topic'] ?? '') !== false);
        if (empty($filtered)) {
            return $questions;
        }
        return array_values($filtered);
    }

    private static function getSubjectQuestions(string $key): array
    {
        if (!empty(self::$subjectQuestionBanks)) {
            return self::$subjectQuestionBanks[$key] ?? [];
        }

        self::$subjectQuestionBanks = [

            'mathematics' => [
                ['question' => 'Evaluate: 25 × 17 ÷ 5', 'A' => '32', 'B' => '85', 'C' => '65', 'D' => '75', 'answer' => 'B', 'topic' => 'arithmetic'],
                ['question' => 'Solve for x: 3(x - 4) + 2 = 2x + 5', 'A' => '10', 'B' => '12', 'C' => '15', 'D' => '18', 'answer' => 'C', 'topic' => 'algebra'],
                ['question' => 'The square root of 144 is:', 'A' => '11', 'B' => '14', 'C' => '12', 'D' => '13', 'answer' => 'C', 'topic' => 'square roots'],
                ['question' => 'Convert 0.25 to a fraction in its simplest form:', 'A' => '<span style="display:inline-flex;flex-direction:column;vertical-align:middle;text-align:center;margin:0 2px"><span style="border-bottom:2px solid #333;padding:0 6px 2px">1</span><span style="padding:2px 6px 0">2</span></span>', 'B' => '<span style="display:inline-flex;flex-direction:column;vertical-align:middle;text-align:center;margin:0 2px"><span style="border-bottom:2px solid #333;padding:0 6px 2px">2</span><span style="padding:2px 6px 0">5</span></span>', 'C' => '<span style="display:inline-flex;flex-direction:column;vertical-align:middle;text-align:center;margin:0 2px"><span style="border-bottom:2px solid #333;padding:0 6px 2px">1</span><span style="padding:2px 6px 0">4</span></span>', 'D' => '<span style="display:inline-flex;flex-direction:column;vertical-align:middle;text-align:center;margin:0 2px"><span style="border-bottom:2px solid #333;padding:0 6px 2px">3</span><span style="padding:2px 6px 0">4</span></span>', 'answer' => 'C', 'topic' => 'fractions'],
                ['question' => 'Find the LCM of 12 and 18:', 'A' => '24', 'B' => '36', 'C' => '48', 'D' => '72', 'answer' => 'B', 'topic' => 'lcm'],
                ['question' => 'A triangle with all sides equal is called:', 'A' => 'Isosceles', 'B' => 'Scalene', 'C' => 'Right-angled', 'D' => 'Equilateral', 'answer' => 'D', 'topic' => 'triangles'],
                ['question' => 'Calculate 20% of 250:', 'A' => '25', 'B' => '50', 'C' => '75', 'D' => '100', 'answer' => 'B', 'topic' => 'percentages'],
                ['question' => 'The perimeter of a square with side 8 cm is:', 'A' => '16 cm', 'B' => '24 cm', 'C' => '32 cm', 'D' => '64 cm', 'answer' => 'C', 'topic' => 'perimeter'],
                ['question' => 'Solve for x: 2x + 5 = 15', 'A' => '2', 'B' => '5', 'C' => '10', 'D' => '3', 'answer' => 'B', 'topic' => 'algebra'],
                ['question' => 'What is the product of 15 and 8?', 'A' => '105', 'B' => '120', 'C' => '115', 'D' => '125', 'answer' => 'B', 'topic' => 'multiplication'],
                ['question' => 'If a shirt costs ₦3,500 and there is a 10% discount, what is the discount amount?', 'A' => '₦250', 'B' => '₦300', 'C' => '₦350', 'D' => '₦400', 'answer' => 'C', 'topic' => 'discount'],
                ['question' => 'What is the next number in the sequence: 2, 4, 6, 8, ?', 'A' => '9', 'B' => '10', 'C' => '11', 'D' => '12', 'answer' => 'B', 'topic' => 'sequences'],
                ['question' => 'How many degrees are in a right angle?', 'A' => '45°', 'B' => '60°', 'C' => '90°', 'D' => '180°', 'answer' => 'C', 'topic' => 'angles'],
                ['question' => 'Calculate the area of a rectangle with length 5 cm and width 3 cm:', 'A' => '8 cm²', 'B' => '15 cm²', 'C' => '16 cm²', 'D' => '10 cm²', 'answer' => 'B', 'topic' => 'area'],
                ['question' => 'Simplify: 3<sup>2</sup> + 4<sup>2</sup>', 'A' => '7', 'B' => '25', 'C' => '49', 'D' => '14', 'answer' => 'B', 'topic' => 'indices'],
                ['question' => 'What is the value of 3<sup>3</sup>?', 'A' => '6', 'B' => '9', 'C' => '27', 'D' => '81', 'answer' => 'C', 'topic' => 'indices'],
                ['question' => 'If a student scores 28 out of 40 in a test, what is the percentage score?', 'A' => '60%', 'B' => '65%', 'C' => '70%', 'D' => '75%', 'answer' => 'C', 'topic' => 'percentages'],
                ['question' => 'Find the mode of the numbers: 2, 3, 4, 4, 5, 6', 'A' => '2', 'B' => '3', 'C' => '4', 'D' => '5', 'answer' => 'C', 'topic' => 'statistics'],
                ['question' => 'How many sides does a hexagon have?', 'A' => '4', 'B' => '5', 'C' => '6', 'D' => '8', 'answer' => 'C', 'topic' => 'polygons'],
                ['question' => 'Simplify 2<sup>3</sup> × 2<sup>2</sup>:', 'A' => '8', 'B' => '16', 'C' => '32', 'D' => '64', 'answer' => 'C', 'topic' => 'indices'],
                ['question' => 'If the ratio of boys to girls in a class is 3:2 and there are 30 students, how many are boys?', 'A' => '12', 'B' => '15', 'C' => '18', 'D' => '20', 'answer' => 'C', 'topic' => 'ratios'],
                ['question' => 'The sum of the interior angles of a triangle is:', 'A' => '90°', 'B' => '180°', 'C' => '270°', 'D' => '360°', 'answer' => 'B', 'topic' => 'angles'],
                ['question' => 'Solve: 5(x - 2) = 20. What is x?', 'A' => '4', 'B' => '6', 'C' => '8', 'D' => '10', 'answer' => 'B', 'topic' => 'algebra'],
                ['question' => 'What is the place value of 7 in the number 3,742?', 'A' => '7', 'B' => '70', 'C' => '700', 'D' => '7,000', 'answer' => 'C', 'topic' => 'place value'],
                ['question' => 'If a car travels 120 km in 2 hours, what is its average speed?', 'A' => '40 km/h', 'B' => '50 km/h', 'C' => '60 km/h', 'D' => '80 km/h', 'answer' => 'C', 'topic' => 'speed'],
                ['question' => 'Simplify: 2x + 3x - x', 'A' => '3x', 'B' => '4x', 'C' => '5x', 'D' => '6x', 'answer' => 'B', 'topic' => 'algebra'],
                ['question' => 'Evaluate: √(49 + 32)', 'A' => '7', 'B' => '8', 'C' => '9', 'D' => '10', 'answer' => 'C', 'topic' => 'square roots'],
                ['question' => 'A rectangular field measures 25 m by 12 m. Find its area in m<sup>2</sup>.', 'A' => '200 m²', 'B' => '250 m²', 'C' => '300 m²', 'D' => '325 m²', 'answer' => 'C', 'topic' => 'mensuration'],
                ['question' => 'Calculate the simple interest on ₦5,000 at 10% per annum for 2 years.', 'A' => '₦500', 'B' => '₦1,000', 'C' => '₦1,500', 'D' => '₦2,000', 'answer' => 'B', 'topic' => 'simple interest'],
                ['question' => 'If 3x + 2y = 12 and x = 2, find y.', 'A' => '2', 'B' => '3', 'C' => '4', 'D' => '5', 'answer' => 'B', 'topic' => 'algebra'],
                ['question' => 'Find the median of the numbers: 5, 8, 3, 9, 6', 'A' => '3', 'B' => '5', 'C' => '6', 'D' => '8', 'answer' => 'C', 'topic' => 'statistics'],
            ],

            'english_language' => [
                ['question' => 'Which of the following is a noun?', 'A' => 'Run', 'B' => 'Happiness', 'C' => 'Quickly', 'D' => 'Under', 'answer' => 'B', 'topic' => 'parts of speech'],
                ['question' => 'The opposite of "generous" is:', 'A' => 'Kind', 'B' => 'Selfish', 'C' => 'Stingy', 'D' => 'Mean', 'answer' => 'C', 'topic' => 'antonyms'],
                ['question' => 'Choose the correct spelling:', 'A' => 'Beautifull', 'B' => 'Beauteful', 'C' => 'Beautiful', 'D' => 'Bautiful', 'answer' => 'C', 'topic' => 'spelling'],
                ['question' => '"The boy ran quickly." The word "quickly" is a/an:', 'A' => 'Noun', 'B' => 'Verb', 'C' => 'Adjective', 'D' => 'Adverb', 'answer' => 'D', 'topic' => 'adverbs'],
                ['question' => 'Which sentence is in the past tense?', 'A' => 'He walks home.', 'B' => 'He walked home.', 'C' => 'He will walk home.', 'D' => 'He is walking home.', 'answer' => 'B', 'topic' => 'tenses'],
                ['question' => 'A synonym for "happy" is:', 'A' => 'Sad', 'B' => 'Angry', 'C' => 'Joyful', 'D' => 'Tired', 'answer' => 'C', 'topic' => 'synonyms'],
                ['question' => 'Which of the following is a complete sentence?', 'A' => 'Running fast.', 'B' => 'The boy.', 'C' => 'The sun is bright.', 'D' => 'Under the table.', 'answer' => 'C', 'topic' => 'sentences'],
                ['question' => 'The plural of "child" is:', 'A' => 'Childs', 'B' => 'Childes', 'C' => 'Children', 'D' => 'Childrens', 'answer' => 'C', 'topic' => 'plurals'],
                ['question' => 'Which word is an adjective?', 'A' => 'Quickly', 'B' => 'Beautiful', 'C' => 'Run', 'D' => 'They', 'answer' => 'B', 'topic' => 'adjectives'],
                ['question' => 'Identify the pronoun: "She went to school."', 'A' => 'Went', 'B' => 'To', 'C' => 'She', 'D' => 'School', 'answer' => 'C', 'topic' => 'pronouns'],
                ['question' => 'What is the past tense of "go"?', 'A' => 'Goed', 'B' => 'Went', 'C' => 'Gone', 'D' => 'Going', 'answer' => 'B', 'topic' => 'tenses'],
                ['question' => 'Which of the following is a preposition?', 'A' => 'Under', 'B' => 'Run', 'C' => 'Beautiful', 'D' => 'They', 'answer' => 'A', 'topic' => 'prepositions'],
                ['question' => 'Choose the correct article: "___ elephant is a large animal."', 'A' => 'A', 'B' => 'An', 'C' => 'The', 'D' => 'None', 'answer' => 'B', 'topic' => 'articles'],
                ['question' => 'What is the comparative form of "tall"?', 'A' => 'Taller', 'B' => 'Tallest', 'C' => 'More tall', 'D' => 'Most tall', 'answer' => 'A', 'topic' => 'comparison'],
                ['question' => 'Which sentence uses correct punctuation?', 'A' => 'Where are you going', 'B' => 'Where are you going?', 'C' => 'Where are you going.', 'D' => 'Where are you going!', 'answer' => 'B', 'topic' => 'punctuation'],
                ['question' => 'The word "careless" means:', 'A' => 'Full of care', 'B' => 'Without care', 'C' => 'Care again', 'D' => 'Care before', 'answer' => 'B', 'topic' => 'prefixes and suffixes'],
                ['question' => 'Which is a compound word?', 'A' => 'Happiness', 'B' => 'Sunlight', 'C' => 'Running', 'D' => 'Quickly', 'answer' => 'B', 'topic' => 'compound words'],
                ['question' => 'What type of sentence gives a command?', 'A' => 'Declarative', 'B' => 'Interrogative', 'C' => 'Imperative', 'D' => 'Exclamatory', 'answer' => 'C', 'topic' => 'sentences'],
                ['question' => 'Identify the conjunction: "I like tea AND coffee."', 'A' => 'I', 'B' => 'Like', 'C' => 'And', 'D' => 'Coffee', 'answer' => 'C', 'topic' => 'conjunctions'],
                ['question' => 'The opposite of "light" (weight) is:', 'A' => 'Bright', 'B' => 'Dark', 'C' => 'Heavy', 'D' => 'Small', 'answer' => 'C', 'topic' => 'antonyms'],
            ],

            'physics' => [
                ['question' => 'What is the SI unit of force?', 'A' => 'Joule', 'B' => 'Newton', 'C' => 'Watt', 'D' => 'Pascal', 'answer' => 'B', 'topic' => 'force'],
                ['question' => 'The speed of light in a vacuum is approximately:', 'A' => '3.0 × 10<sup>6</sup> ms<sup>-1</sup>', 'B' => '3.0 × 10<sup>7</sup> ms<sup>-1</sup>', 'C' => '3.0 × 10<sup>8</sup> ms<sup>-1</sup>', 'D' => '3.0 × 10<sup>9</sup> ms<sup>-1</sup>', 'answer' => 'C', 'topic' => 'light'],
                ['question' => 'Which of these is a vector quantity?', 'A' => 'Speed', 'B' => 'Mass', 'C' => 'Temperature', 'D' => 'Velocity', 'answer' => 'D', 'topic' => 'vectors'],
                ['question' => 'What is the unit of electric current?', 'A' => 'Volt', 'B' => 'Ampere', 'C' => 'Ohm', 'D' => 'Watt', 'answer' => 'B', 'topic' => 'electricity'],
                ['question' => 'Which law states that energy cannot be created or destroyed?', 'A' => 'Newton\'s First Law', 'B' => 'Law of Conservation of Energy', 'C' => 'Ohm\'s Law', 'D' => 'Boyle\'s Law', 'answer' => 'B', 'topic' => 'energy'],
                ['question' => 'The process by which heat travels through a solid is called:', 'A' => 'Convection', 'B' => 'Radiation', 'C' => 'Conduction', 'D' => 'Evaporation', 'answer' => 'C', 'topic' => 'heat transfer'],
                ['question' => 'What is the acceleration due to gravity on Earth approximately?', 'A' => '7.8 ms<sup>-2</sup>', 'B' => '9.8 ms<sup>-2</sup>', 'C' => '11.8 ms<sup>-2</sup>', 'D' => '5.8 ms<sup>-2</sup>', 'answer' => 'B', 'topic' => 'gravity'],
                ['question' => 'The unit of electrical resistance is the:', 'A' => 'Volt', 'B' => 'Ampere', 'C' => 'Ohm', 'D' => 'Watt', 'answer' => 'C', 'topic' => 'electricity'],
                ['question' => 'What type of lens converges light rays?', 'A' => 'Concave', 'B' => 'Convex', 'C' => 'Diverging', 'D' => 'Plane', 'answer' => 'B', 'topic' => 'optics'],
                ['question' => 'Which of these is NOT a renewable energy source?', 'A' => 'Solar', 'B' => 'Wind', 'C' => 'Natural gas', 'D' => 'Hydroelectric', 'answer' => 'C', 'topic' => 'energy'],
                ['question' => 'Sound waves cannot travel through:', 'A' => 'Air', 'B' => 'Water', 'C' => 'Steel', 'D' => 'Vacuum', 'answer' => 'D', 'topic' => 'sound'],
                ['question' => 'What is the unit of work?', 'A' => 'Newton', 'B' => 'Watt', 'C' => 'Joule', 'D' => 'Pascal', 'answer' => 'C', 'topic' => 'work'],
                ['question' => 'The SI unit of pressure is:', 'A' => 'Newton', 'B' => 'Joule', 'C' => 'Watt', 'D' => 'Pascal', 'answer' => 'D', 'topic' => 'pressure'],
                ['question' => 'Which of the following is a scalar quantity?', 'A' => 'Force', 'B' => 'Velocity', 'C' => 'Speed', 'D' => 'Acceleration', 'answer' => 'C', 'topic' => 'scalars'],
                ['question' => 'The formula for calculating speed is:', 'A' => 'Distance × Time', 'B' => 'Distance ÷ Time', 'C' => 'Time ÷ Distance', 'D' => 'Mass × Acceleration', 'answer' => 'B', 'topic' => 'speed'],
                ['question' => 'What is the SI unit of mass?', 'A' => 'Gram', 'B' => 'Kilogram', 'C' => 'Newton', 'D' => 'Pound', 'answer' => 'B', 'topic' => 'mass'],
                ['question' => 'A body remains at rest or in uniform motion unless acted upon by an external force. This is:', 'A' => 'Newton\'s Second Law', 'B' => 'Newton\'s First Law', 'C' => 'Newton\'s Third Law', 'D' => 'Law of Gravitation', 'answer' => 'B', 'topic' => 'newton laws'],
                ['question' => 'The frequency of a wave is measured in:', 'A' => 'Hertz', 'B' => 'Decibels', 'C' => 'Meters', 'D' => 'Watts', 'answer' => 'A', 'topic' => 'waves'],
                ['question' => 'Calculate the force on a 5 kg mass accelerating at 4 ms<sup>-2</sup>.', 'A' => '9 N', 'B' => '20 N', 'C' => '10 N', 'D' => '15 N', 'answer' => 'B', 'topic' => 'force'],
                ['question' => 'A car travels 150 km in 3 hours. Calculate its average speed.', 'A' => '30 kmh<sup>-1</sup>', 'B' => '40 kmh<sup>-1</sup>', 'C' => '50 kmh<sup>-1</sup>', 'D' => '60 kmh<sup>-1</sup>', 'answer' => 'C', 'topic' => 'speed'],
                ['question' => 'A body of mass 2 kg is moving with velocity 3 ms<sup>-1</sup>. Calculate its kinetic energy.', 'A' => '6 J', 'B' => '9 J', 'C' => '12 J', 'D' => '18 J', 'answer' => 'B', 'topic' => 'energy'],
                ['question' => 'The device used to step up or step down voltage is a:', 'A' => 'Generator', 'B' => 'Transformer', 'C' => 'Rectifier', 'D' => 'Amplifier', 'answer' => 'B', 'topic' => 'electricity'],
                ['question' => 'Which of these is a good conductor of electricity?', 'A' => 'Rubber', 'B' => 'Plastic', 'C' => 'Copper', 'D' => 'Wood', 'answer' => 'C', 'topic' => 'electricity'],
                ['question' => 'The energy possessed by a body due to its motion is called:', 'A' => 'Potential energy', 'B' => 'Kinetic energy', 'C' => 'Chemical energy', 'D' => 'Nuclear energy', 'answer' => 'B', 'topic' => 'energy'],
                ['question' => 'A stone of mass 10 kg is dropped from a height of 20 m. Calculate its potential energy. (g = 10 ms<sup>-2</sup>)', 'A' => '100 J', 'B' => '500 J', 'C' => '2,000 J', 'D' => '500 J', 'answer' => 'C', 'topic' => 'energy'],
                ['question' => 'Calculate the wavelength of a wave of frequency 50 Hz travelling at 340 ms<sup>-1</sup>.', 'A' => '5.8 m', 'B' => '6.8 m', 'C' => '7.8 m', 'D' => '8.8 m', 'answer' => 'B', 'topic' => 'waves'],
                ['question' => 'A current of 3 A flows through a resistor of 6 Ω. Calculate the voltage across the resistor.', 'A' => '12 V', 'B' => '18 V', 'C' => '24 V', 'D' => '36 V', 'answer' => 'B', 'topic' => 'electricity'],
                ['question' => 'A force of 20 N moves an object through 5 m. Calculate the work done.', 'A' => '50 J', 'B' => '100 J', 'C' => '150 J', 'D' => '200 J', 'answer' => 'B', 'topic' => 'work'],
            ],

            'chemistry' => [
                ['question' => 'The basic unit of an element is the:', 'A' => 'Molecule', 'B' => 'Atom', 'C' => 'Electron', 'D' => 'Proton', 'answer' => 'B', 'topic' => 'atoms'],
                ['question' => 'What is the chemical formula for water?', 'A' => 'H<sub>2</sub>O', 'B' => 'CO<sub>2</sub>', 'C' => 'NaCl', 'D' => 'H<sub>2</sub>SO<sub>4</sub>', 'answer' => 'A', 'topic' => 'chemical formulae'],
                ['question' => 'The process by which a solid turns directly into a gas is called:', 'A' => 'Evaporation', 'B' => 'Melting', 'C' => 'Sublimation', 'D' => 'Condensation', 'answer' => 'C', 'topic' => 'sublimation'],
                ['question' => 'What is the pH of a neutral solution?', 'A' => '0', 'B' => '7', 'C' => '14', 'D' => '1', 'answer' => 'B', 'topic' => 'acids and bases'],
                ['question' => 'Which gas is produced when zinc reacts with hydrochloric acid?', 'A' => 'Oxygen', 'B' => 'Chlorine', 'C' => 'Hydrogen', 'D' => 'Carbon dioxide', 'answer' => 'C', 'topic' => 'acids and bases'],
                ['question' => 'What is the atomic number of carbon?', 'A' => '4', 'B' => '6', 'C' => '8', 'D' => '12', 'answer' => 'B', 'topic' => 'atoms'],
                ['question' => 'The formula for common salt is:', 'A' => 'KCl', 'B' => 'NaCl', 'C' => 'CaCl<sub>2</sub>', 'D' => 'MgCl<sub>2</sub>', 'answer' => 'B', 'topic' => 'chemical formulae'],
                ['question' => 'Which of the following is an example of a physical change?', 'A' => 'Burning wood', 'B' => 'Rusting iron', 'C' => 'Melting ice', 'D' => 'Digesting food', 'answer' => 'C', 'topic' => 'physical changes'],
                ['question' => 'What is the symbol for potassium?', 'A' => 'Po', 'B' => 'Pt', 'C' => 'K', 'D' => 'P', 'answer' => 'C', 'topic' => 'elements'],
                ['question' => 'What is the chemical name for CaCO<sub>3</sub>?', 'A' => 'Calcium oxide', 'B' => 'Calcium carbonate', 'C' => 'Calcium chloride', 'D' => 'Calcium hydroxide', 'answer' => 'B', 'topic' => 'chemical formulae'],
                ['question' => 'The formula for sulfuric acid is:', 'A' => 'HCl', 'B' => 'HNO<sub>3</sub>', 'C' => 'H<sub>2</sub>SO<sub>4</sub>', 'D' => 'CH<sub>3</sub>COOH', 'answer' => 'C', 'topic' => 'acids and bases'],
                ['question' => 'Which of the following is a mixture?', 'A' => 'Water', 'B' => 'Common salt', 'C' => 'Air', 'D' => 'Sugar', 'answer' => 'C', 'topic' => 'mixtures'],
                ['question' => 'How many electrons are in a neutral oxygen atom (atomic number 8)?', 'A' => '4', 'B' => '6', 'C' => '8', 'D' => '16', 'answer' => 'C', 'topic' => 'atoms'],
                ['question' => 'What is the chemical symbol for gold?', 'A' => 'Go', 'B' => 'Gd', 'C' => 'Au', 'D' => 'Ag', 'answer' => 'C', 'topic' => 'elements'],
                ['question' => 'Balance: H<sub>2</sub> + O<sub>2</sub> → H<sub>2</sub>O. The coefficient of H<sub>2</sub>O is:', 'A' => '1', 'B' => '2', 'C' => '3', 'D' => '4', 'answer' => 'B', 'topic' => 'chemical equations'],
                ['question' => 'What is the formula of methane?', 'A' => 'CH<sub>4</sub>', 'B' => 'C<sub>2</sub>H<sub>6</sub>', 'C' => 'C<sub>3</sub>H<sub>8</sub>', 'D' => 'C<sub>4</sub>H<sub>10</sub>', 'answer' => 'A', 'topic' => 'organic chemistry'],
                ['question' => 'What is the formula of ethanol?', 'A' => 'CH<sub>4</sub>', 'B' => 'C<sub>2</sub>H<sub>5</sub>OH', 'C' => 'CH<sub>3</sub>OH', 'D' => 'C<sub>3</sub>H<sub>7</sub>OH', 'answer' => 'B', 'topic' => 'organic chemistry'],
                ['question' => 'The ion Cl<sup>-</sup> has how many more electrons than protons?', 'A' => '0', 'B' => '1', 'C' => '2', 'D' => '3', 'answer' => 'B', 'topic' => 'ions'],
                ['question' => 'Calculate the relative molecular mass of H<sub>2</sub>O. (H = 1, O = 16)', 'A' => '17', 'B' => '18', 'C' => '19', 'D' => '20', 'answer' => 'B', 'topic' => 'mole concept'],
                ['question' => 'Calculate the number of moles in 36 g of water (H<sub>2</sub>O, M = 18 gmol<sup>-1</sup>)', 'A' => '1 mol', 'B' => '2 mol', 'C' => '3 mol', 'D' => '4 mol', 'answer' => 'B', 'topic' => 'mole concept'],
                ['question' => 'The charge on a calcium ion Ca<sup>2+</sup> is:', 'A' => '+1', 'B' => '+2', 'C' => '-1', 'D' => '-2', 'answer' => 'B', 'topic' => 'ions'],
                ['question' => 'In the reaction: Zn + 2HCl → ZnCl<sub>2</sub> + H<sub>2</sub>, the gas produced is:', 'A' => 'Chlorine', 'B' => 'Oxygen', 'C' => 'Hydrogen', 'D' => 'Zinc chloride', 'answer' => 'C', 'topic' => 'chemical reactions'],
                ['question' => 'What is the oxidation number of sulfur in H<sub>2</sub>SO<sub>4</sub>?', 'A' => '+4', 'B' => '+5', 'C' => '+6', 'D' => '+7', 'answer' => 'C', 'topic' => 'oxidation'],
                ['question' => 'How many atoms are in one molecule of NH<sub>3</sub>?', 'A' => '2', 'B' => '3', 'C' => '4', 'D' => '5', 'answer' => 'C', 'topic' => 'atoms'],
                ['question' => 'What is the formula of the ammonium ion?', 'A' => 'NH<sub>4</sub><sup>+</sup>', 'B' => 'NH<sub>3</sub><sup>+</sup>', 'C' => 'NH<sub>2</sub><sup>-</sup>', 'D' => 'NO<sub>3</sub><sup>-</sup>', 'answer' => 'A', 'topic' => 'ions'],
            ],

            'biology' => [
                ['question' => 'The basic unit of life is the:', 'A' => 'Tissue', 'B' => 'Cell', 'C' => 'Organ', 'D' => 'System', 'answer' => 'B', 'topic' => 'cells'],
                ['question' => 'Which organ pumps blood around the body?', 'A' => 'Lungs', 'B' => 'Liver', 'C' => 'Heart', 'D' => 'Kidney', 'answer' => 'C', 'topic' => 'circulatory system'],
                ['question' => 'The process by which plants make food is called:', 'A' => 'Respiration', 'B' => 'Transpiration', 'C' => 'Photosynthesis', 'D' => 'Digestion', 'answer' => 'C', 'topic' => 'photosynthesis'],
                ['question' => 'What gas do plants absorb from the atmosphere?', 'A' => 'Oxygen', 'B' => 'Nitrogen', 'C' => 'Carbon dioxide', 'D' => 'Hydrogen', 'answer' => 'C', 'topic' => 'photosynthesis'],
                ['question' => 'Which of these is NOT a sense organ?', 'A' => 'Eye', 'B' => 'Ear', 'C' => 'Heart', 'D' => 'Skin', 'answer' => 'C', 'topic' => 'sense organs'],
                ['question' => 'What is the largest organ in the human body?', 'A' => 'Liver', 'B' => 'Brain', 'C' => 'Skin', 'D' => 'Heart', 'answer' => 'C', 'topic' => 'skin'],
                ['question' => 'Which of the following is a primary consumer?', 'A' => 'Lion', 'B' => 'Grass', 'C' => 'Cow', 'D' => 'Snake', 'answer' => 'C', 'topic' => 'ecology'],
                ['question' => 'The function of the red blood cells is to:', 'A' => 'Fight infection', 'B' => 'Carry oxygen', 'C' => 'Clot blood', 'D' => 'Produce hormones', 'answer' => 'B', 'topic' => 'blood'],
                ['question' => 'What is the name of the process by which organisms produce offspring?', 'A' => 'Respiration', 'B' => 'Reproduction', 'C' => 'Digestion', 'D' => 'Excretion', 'answer' => 'B', 'topic' => 'reproduction'],
                ['question' => 'Which of these animals undergoes metamorphosis?', 'A' => 'Dog', 'B' => 'Cat', 'C' => 'Frog', 'D' => 'Goat', 'answer' => 'C', 'topic' => 'metamorphosis'],
                ['question' => 'The green pigment in plants that captures sunlight is called:', 'A' => 'Melanin', 'B' => 'Chlorophyll', 'C' => 'Carotene', 'D' => 'Xanthophyll', 'answer' => 'B', 'topic' => 'photosynthesis'],
                ['question' => 'Where does digestion of protein begin in the human body?', 'A' => 'Mouth', 'B' => 'Stomach', 'C' => 'Small intestine', 'D' => 'Large intestine', 'answer' => 'B', 'topic' => 'digestive system'],
                ['question' => 'Which of the following is a disease caused by a virus?', 'A' => 'Malaria', 'B' => 'Cholera', 'C' => 'HIV/AIDS', 'D' => 'Tuberculosis', 'answer' => 'C', 'topic' => 'diseases'],
                ['question' => 'The organ responsible for filtering blood in the body is the:', 'A' => 'Liver', 'B' => 'Heart', 'C' => 'Kidney', 'D' => 'Lungs', 'answer' => 'C', 'topic' => 'excretory system'],
                ['question' => 'What is the primary function of the roots of a plant?', 'A' => 'Photosynthesis', 'B' => 'Support and absorption', 'C' => 'Reproduction', 'D' => 'Transpiration', 'answer' => 'B', 'topic' => 'plants'],
                ['question' => 'Which of the following is a cold-blooded animal?', 'A' => 'Bird', 'B' => 'Lizard', 'C' => 'Dog', 'D' => 'Cow', 'answer' => 'B', 'topic' => 'classification'],
                ['question' => 'The main function of the nervous system is to:', 'A' => 'Pump blood', 'B' => 'Digest food', 'C' => 'Coordinate body activities', 'D' => 'Filter waste', 'answer' => 'C', 'topic' => 'nervous system'],
                ['question' => 'What is the name of the bone that protects the brain?', 'A' => 'Femur', 'B' => 'Rib cage', 'C' => 'Skull', 'D' => 'Spine', 'answer' => 'C', 'topic' => 'skeletal system'],
                ['question' => 'The process by which plants lose water vapor through their leaves is called:', 'A' => 'Photosynthesis', 'B' => 'Transpiration', 'C' => 'Respiration', 'D' => 'Excretion', 'answer' => 'B', 'topic' => 'transpiration'],
                ['question' => 'Which vitamin is produced when the skin is exposed to sunlight?', 'A' => 'Vitamin A', 'B' => 'Vitamin B', 'C' => 'Vitamin C', 'D' => 'Vitamin D', 'answer' => 'D', 'topic' => 'skin'],
                ['question' => 'The movement of particles from a region of higher concentration to lower concentration is called:', 'A' => 'Osmosis', 'B' => 'Diffusion', 'C' => 'Active transport', 'D' => 'Transpiration', 'answer' => 'B', 'topic' => 'cell transport'],
                ['question' => 'Which of the following is a vector of malaria?', 'A' => 'Housefly', 'B' => 'Mosquito', 'C' => 'Tsetse fly', 'D' => 'Cockroach', 'answer' => 'B', 'topic' => 'diseases'],
                ['question' => 'The male reproductive cell is called:', 'A' => 'Ovum', 'B' => 'Sperm', 'C' => 'Egg', 'D' => 'Zygote', 'answer' => 'B', 'topic' => 'reproduction'],
                ['question' => 'Which part of the human digestive system absorbs water?', 'A' => 'Stomach', 'B' => 'Small intestine', 'C' => 'Large intestine', 'D' => 'Mouth', 'answer' => 'C', 'topic' => 'digestive system'],
                ['question' => 'An ecosystem consists of:', 'A' => 'Only plants', 'B' => 'Only animals', 'C' => 'Living and non-living components', 'D' => 'Only non-living components', 'answer' => 'C', 'topic' => 'ecology'],
                ['question' => 'Which of these organisms is a decomposer?', 'A' => 'Lion', 'B' => 'Mushroom', 'C' => 'Grass', 'D' => 'Fish', 'answer' => 'B', 'topic' => 'ecology'],
                ['question' => 'The blood vessels that carry blood away from the heart are called:', 'A' => 'Veins', 'B' => 'Arteries', 'C' => 'Capillaries', 'D' => 'Ventricles', 'answer' => 'B', 'topic' => 'circulatory system'],
                ['question' => 'Which part of the cell contains the genetic material?', 'A' => 'Cytoplasm', 'B' => 'Cell membrane', 'C' => 'Nucleus', 'D' => 'Mitochondria', 'answer' => 'C', 'topic' => 'cells'],
            ],

            'economics' => [
                ['question' => 'What is the basic economic problem?', 'A' => 'Unemployment', 'B' => 'Scarcity', 'C' => 'Inflation', 'D' => 'Poverty', 'answer' => 'B', 'topic' => 'basic concepts'],
                ['question' => 'The demand for a good increases when its price:', 'A' => 'Rises', 'B' => 'Falls', 'C' => 'Stays constant', 'D' => 'Fluctuates', 'answer' => 'B', 'topic' => 'demand'],
                ['question' => 'The supply curve slopes:', 'A' => 'Downward', 'B' => 'Upward', 'C' => 'Horizontal', 'D' => 'Vertical', 'answer' => 'B', 'topic' => 'supply'],
                ['question' => 'When demand exceeds supply, the price tends to:', 'A' => 'Fall', 'B' => 'Rise', 'C' => 'Stay the same', 'D' => 'Become zero', 'answer' => 'B', 'topic' => 'demand and supply'],
                ['question' => 'Which of these is a direct tax?', 'A' => 'VAT', 'B' => 'Excise duty', 'C' => 'Income tax', 'D' => 'Import duty', 'answer' => 'C', 'topic' => 'taxation'],
                ['question' => 'Land as a factor of production includes:', 'A' => 'Only soil', 'B' => 'All natural resources', 'C' => 'Buildings only', 'D' => 'Money', 'answer' => 'B', 'topic' => 'factors of production'],
                ['question' => 'The Central Bank of Nigeria is responsible for:', 'A' => 'Printing money', 'B' => 'Accepting deposits', 'C' => 'Granting loans to individuals', 'D' => 'Issuing currency', 'answer' => 'D', 'topic' => 'money and banking'],
                ['question' => 'Which of the following is a free good?', 'A' => 'Bread', 'B' => 'Sunlight', 'C' => 'Car', 'D' => 'Clothes', 'answer' => 'B', 'topic' => 'basic concepts'],
                ['question' => 'The reward for capital as a factor of production is:', 'A' => 'Wages', 'B' => 'Rent', 'C' => 'Interest', 'D' => 'Profit', 'answer' => 'C', 'topic' => 'factors of production'],
                ['question' => 'Which of these is an indirect tax?', 'A' => 'Personal income tax', 'B' => 'Company income tax', 'C' => 'Value Added Tax (VAT)', 'D' => 'Capital gains tax', 'answer' => 'C', 'topic' => 'taxation'],
                ['question' => 'Opportunity cost is defined as:', 'A' => 'The total cost of production', 'B' => 'The next best alternative forgone', 'C' => 'The money cost of a good', 'D' => 'The cost of raw materials', 'answer' => 'B', 'topic' => 'basic concepts'],
                ['question' => 'When the price of a good falls, the quantity demanded typically:', 'A' => 'Falls', 'B' => 'Rises', 'C' => 'Remains unchanged', 'D' => 'Doubles', 'answer' => 'B', 'topic' => 'demand'],
                ['question' => 'A market where there is only one seller is called:', 'A' => 'Oligopoly', 'B' => 'Monopoly', 'C' => 'Perfect competition', 'D' => 'Monopolistic competition', 'answer' => 'B', 'topic' => 'market structure'],
                ['question' => 'Which of the following is a function of commercial banks?', 'A' => 'Issuing currency', 'B' => 'Accepting deposits', 'C' => 'Controlling money supply', 'D' => 'Setting interest rates for the economy', 'answer' => 'B', 'topic' => 'money and banking'],
                ['question' => 'The law of diminishing marginal utility states that as consumption increases, the additional satisfaction:', 'A' => 'Increases', 'B' => 'Decreases', 'C' => 'Stays constant', 'D' => 'Becomes negative', 'answer' => 'B', 'topic' => 'utility'],
            ],

            'government' => [
                ['question' => 'Nigeria became a republic in the year:', 'A' => '1960', 'B' => '1963', 'C' => '1966', 'D' => '1979', 'answer' => 'B', 'topic' => 'constitution'],
                ['question' => 'The head of the executive arm of government in Nigeria is the:', 'A' => 'Chief Justice', 'B' => 'Senate President', 'C' => 'Speaker', 'D' => 'President', 'answer' => 'D', 'topic' => 'executive'],
                ['question' => 'Which of the following is a function of the legislature?', 'A' => 'Interpreting laws', 'B' => 'Making laws', 'C' => 'Executing laws', 'D' => 'Enforcing laws', 'answer' => 'B', 'topic' => 'legislature'],
                ['question' => 'The highest court in Nigeria is the:', 'A' => 'High Court', 'B' => 'Appeal Court', 'C' => 'Supreme Court', 'D' => 'Magistrate Court', 'answer' => 'C', 'topic' => 'judiciary'],
                ['question' => 'A constitution that is difficult to amend is said to be:', 'A' => 'Flexible', 'B' => 'Written', 'C' => 'Rigid', 'D' => 'Unwritten', 'answer' => 'C', 'topic' => 'constitution'],
                ['question' => 'Nigeria gained independence from Britain in:', 'A' => '1957', 'B' => '1960', 'C' => '1963', 'D' => '1966', 'answer' => 'B', 'topic' => 'constitution'],
                ['question' => 'The three arms of government are the executive, legislature and:', 'A' => 'Police', 'B' => 'Judiciary', 'C' => 'Military', 'D' => 'Civil service', 'answer' => 'B', 'topic' => 'separation of powers'],
                ['question' => 'Which of the following is a function of the executive?', 'A' => 'Making laws', 'B' => 'Interpreting laws', 'C' => 'Implementing laws', 'D' => 'Amending laws', 'answer' => 'C', 'topic' => 'executive'],
                ['question' => 'The principle of separation of powers was propounded by:', 'A' => 'John Locke', 'B' => 'Montesquieu', 'C' => 'Rousseau', 'D' => 'Thomas Hobbes', 'answer' => 'B', 'topic' => 'separation of powers'],
                ['question' => 'A political system where power is shared between central and regional governments is called:', 'A' => 'Unitary', 'B' => 'Federal', 'C' => 'Confederal', 'D' => 'Parliamentary', 'answer' => 'B', 'topic' => 'federalism'],
                ['question' => 'The head of the Nigerian Senate is called the:', 'A' => 'Speaker', 'B' => 'Chief Justice', 'C' => 'Senate President', 'D' => 'Prime Minister', 'answer' => 'C', 'topic' => 'legislature'],
                ['question' => 'Which of the following countries operates a unicameral legislature?', 'A' => 'Nigeria', 'B' => 'USA', 'C' => 'Ghana', 'D' => 'UK', 'answer' => 'C', 'topic' => 'legislature'],
                ['question' => 'The process of voting for political representatives is called:', 'A' => 'Nomination', 'B' => 'Election', 'C' => 'Appointment', 'D' => 'Selection', 'answer' => 'B', 'topic' => 'elections'],
                ['question' => 'Nigeria\'s current constitution was enacted in:', 'A' => '1979', 'B' => '1989', 'C' => '1999', 'D' => '2005', 'answer' => 'C', 'topic' => 'constitution'],
                ['question' => 'Which of the following is a fundamental human right in Nigeria?', 'A' => 'Right to drive', 'B' => 'Right to education', 'C' => 'Right to own a gun', 'D' => 'Right to travel abroad free', 'answer' => 'B', 'topic' => 'human rights'],
            ],
        ];

        return self::$subjectQuestionBanks[$key] ?? [];
    }

    private static function generateObjectives(string $subject, string $class, string $topic): array
    {
        $level = CurriculumData::getClassCategory($class);
        $verbs = $level === 'primary'
            ? ['define', 'identify', 'mention', 'list', 'give examples of']
            : ($level === 'junior'
                ? ['define', 'explain', 'describe', 'differentiate', 'apply']
                : ['define', 'explain', 'analyse', 'evaluate', 'synthesise']);

        $contexts = self::getObjectiveContexts($subject, $class, $topic, $verbs);
        return array_slice($contexts, 0, rand(3, 7));
    }

    private static function getObjectiveContexts(string $subject, string $class, string $topic, array $verbs): array
    {
        $subj = strtolower($subject);
        $isMath = str_contains($subj, 'mathemat');
        $isScience = str_contains($subj, 'physic') || str_contains($subj, 'chemist') || str_contains($subj, 'biology');
        $isEnglish = str_contains($subj, 'english') || str_contains($subj, 'literature');

        $patterns = [
            "Learners {$verbs[0]} " . lcfirst($topic) . ".",
            "Learners {$verbs[1]} the key features and characteristics of " . lcfirst($topic) . ".",
            "Learners {$verbs[2]} the relationship between " . lcfirst($topic) . " and related concepts in {$subject}.",
            "Learners {$verbs[3]} between the different aspects of " . lcfirst($topic) . ".",
            "Learners {$verbs[4]} the principles of " . lcfirst($topic) . " to solve practical problems.",
        ];

        if ($isMath) {
            $patterns = [
                "Learners solve problems involving " . lcfirst($topic) . ".",
                "Learners correctly apply the formula for " . lcfirst($topic) . " to given problems.",
                "Learners interpret and solve word problems related to " . lcfirst($topic) . ".",
                "Learners demonstrate the step-by-step working for " . lcfirst($topic) . " problems.",
                "Learners verify their solutions to " . lcfirst($topic) . " problems using alternative methods.",
            ];
        } elseif ($isScience) {
            $patterns = [
                "Learners {$verbs[0]} " . lcfirst($topic) . " with accurate scientific terminology.",
                "Learners {$verbs[1]} the principles and processes involved in " . lcfirst($topic) . ".",
                "Learners {$verbs[2]} the practical applications of " . lcfirst($topic) . " in everyday life.",
                "Learners {$verbs[3]} between related concepts within " . lcfirst($topic) . ".",
                "Learners {$verbs[4]} the scientific method to investigate " . lcfirst($topic) . ".",
            ];
        } elseif ($isEnglish) {
            $patterns = [
                "Learners {$verbs[0]} " . lcfirst($topic) . " in their own words.",
                "Learners {$verbs[1]} examples of " . lcfirst($topic) . " in given passages.",
                "Learners {$verbs[2]} the usage of " . lcfirst($topic) . " in different contexts.",
                "Learners {$verbs[3]} between correct and incorrect usage of " . lcfirst($topic) . ".",
                "Learners {$verbs[4]} their knowledge of " . lcfirst($topic) . " in their own writing.",
            ];
        }

        return $patterns;
    }

    private static function generateMaterials(string $subject, string $class, string $topic): array
    {
        $materials = [
            'Whiteboard and markers', 'Recommended textbook', 'Student notebooks',
            'Charts and diagrams', 'Handouts and worksheets',
        ];

        $specific = self::getSubjectSpecificMaterials($subject, $topic);
        if (!empty($specific)) {
            $materials = array_merge($materials, $specific);
        }

        return $materials;
    }

    private static function generatePreviousKnowledge(string $subject, string $class, string $topic): string
    {
        $level = CurriculumData::getClassCategory($class);
        $subj = strtolower($subject);

        $statements = [
            "Students have prior knowledge of basic concepts in {$subject} and can identify simple examples from their environment. They have previously been introduced to related topics in previous lessons.",
            "Students are familiar with fundamental principles in {$subject} and can recall previous lessons that connect to the new topic of {$topic}.",
            "Students possess foundational literacy and numeracy skills relevant to understanding {$topic}. They have experience with {$subject} concepts from previous terms.",
        ];

        if ($level === 'primary') {
            return "Students can count, read, and write basic numbers and words. They have been introduced to simple {$subject} concepts in previous lessons and can identify examples from their immediate environment. This lesson builds on their existing knowledge by introducing {$topic} in a fun and engaging way.";
        }
        if ($level === 'junior') {
            return "Students have foundational knowledge of {$subject} from their primary education. They can identify basic terms and apply simple principles. They have previously studied related topics that serve as a foundation for understanding {$topic}.";
        }
        return "Students have studied introductory concepts in {$subject} at the JSS level and can analyse basic principles. They are familiar with standard terminology and can engage with abstract concepts. Their prior knowledge provides a solid foundation for the advanced study of {$topic}.";
    }

    private static function generateLessonSteps(array $objectives, string $subject, string $class, string $topic): array
    {
        $steps = [];
        $stepCount = count($objectives);

        for ($i = 0; $i < $stepCount; $i++) {
            $stepNum = $i + 1;
            $isFirst = $i === 0;
            $isLast = $i === $stepCount - 1;
            $isMiddle = !$isFirst && !$isLast;

            $teacherAct = $isFirst
                ? "The teacher introduces the topic '{$topic}' by asking guiding questions related to students' prior knowledge. The teacher writes the topic and learning objectives on the board and explains what students will achieve by the end of the lesson. A brief discussion on the relevance of {$topic} in {$subject} is facilitated."
                : ($isLast
                    ? "The teacher guides students in applying the knowledge of {$topic} to solve practical problems and real-life situations in {$subject}. Students work in groups on assigned tasks while the teacher moves around to provide individual support and feedback. The teacher then leads a class discussion on the solutions."
                    : ($i === 1
                        ? "The teacher explains the key concepts and features of {$topic} using clear examples and illustrations on the board. Students are encouraged to ask questions and share their understanding. The teacher uses a step-by-step approach to ensure comprehension."
                        : ($i === 2
                            ? "The teacher discusses the different types and classifications of {$topic}, using visual aids and real-world examples. Students participate by identifying examples from their own experience. The teacher clarifies misconceptions and reinforces correct understanding."
                            : "The teacher elaborates on the applications and importance of {$topic} in {$subject} and in everyday life. Practical demonstrations and case studies are used to illustrate key points. Students engage in critical thinking exercises."
                        )
                    )
                );

            $learnerAct = $isFirst
                ? "Students respond to questions based on their prior knowledge, share what they already know about {$topic}, and write down the learning objectives in their notebooks. They ask questions about what they hope to learn."
                : ($isLast
                    ? "Students work in small groups to solve problems and complete tasks related to {$topic}. Each group presents their findings and answers to the class. Students peer-review each other's work and provide constructive feedback."
                    : ($isMiddle
                        ? "Students listen attentively, take notes, and ask questions for clarification. They participate in class discussions, answer questions posed by the teacher, and complete short exercises to reinforce their understanding."
                        : "Students engage in guided practice activities, working through examples with the teacher's support. They ask and answer questions to deepen their understanding."
                    )
                );

            $learningPoint = $isFirst
                ? "Students understand the scope and importance of {$topic} in {$subject} and are prepared for the lesson."
                : ($isLast
                    ? "Students can apply the knowledge of {$topic} to solve problems in {$subject} and relate it to real-world situations."
                    : ($i === 1
                        ? "Students gain a clear understanding of the key concepts and features of {$topic}."
                        : ($i === 2
                            ? "Students can identify and differentiate between the types and classifications of {$topic}."
                            : "Students understand the practical applications of {$topic} and can connect them to everyday life."
                        )
                    )
                );

            $steps[] = [
                'step' => $stepNum,
                'teacherActivities' => $teacherAct,
                'learnerActivities' => $learnerAct,
                'learningPoints' => $learningPoint,
            ];
        }

        return $steps;
    }

    private static function generateEvaluation(array $objectives, string $subject, string $topic): string
    {
        $questions = [];
        foreach ($objectives as $i => $obj) {
            $num = $i + 1;
            $verbs = ['Define', 'List three features of', 'Explain the importance of', 'Differentiate between', 'Give two examples of', 'Describe the process of', 'State the function of'];
            $v = $verbs[$i % count($verbs)];
            $questions[] = "{$num}. {$v} {$topic} in the context of {$subject}.";
        }
        $questions[] = (count($questions) + 1) . ". Solve a practical problem related to {$topic}.";
        return implode("\n", $questions);
    }

    private static function generateAssignment(string $subject, string $class, string $topic, string $type): string
    {
        $level = CurriculumData::getClassCategory($class);
        $tasks = [
            "1. Write a comprehensive note on {$topic} in your notebook.",
            "2. Answer questions 1–5 on {$topic} from your textbook.",
            "3. Research and write two additional examples of {$topic} from your local environment.",
        ];
        if ($level !== 'primary') {
            $tasks[] = "4. Prepare a 2-minute oral presentation on {$topic} for the next class.";
            $tasks[] = "5. Solve the attached practice problems on {$topic}.";
        } else {
            $tasks[] = "4. Draw and label a diagram related to {$topic}.";
            $tasks[] = "5. Ask a parent or guardian to help you find one more example of {$topic} at home.";
        }
        if ($type === 'lesson_plan') {
            $tasks[] = "6. Write five objective questions based on {$topic}.";
        }
        return implode("\n", $tasks);
    }

    private static function generateSummary(string $subject, string $topic, array $objectives): string
    {
        return "In this lesson, students learned about {$topic} in {$subject}. The key points covered include: " .
            implode('; ', array_map(function($o) {
                $clean = strip_tags($o);
                return substr($clean, 0, 80) . '...';
            }, array_slice($objectives, 0, 3))) .
            ". Students are encouraged to review these concepts and practice with the provided exercises to reinforce their understanding. The next lesson will build on these foundations to explore more advanced aspects of {$subject}.";
    }

    private static function generateConclusion(string $subject, string $topic): string
    {
        return "The teacher concludes by summarizing the key points about {$topic} and emphasizing its relevance in {$subject} and everyday life. Students are encouraged to continue practicing at home and to observe examples of {$topic} in their environment. The next lesson will build on these concepts to explore more advanced topics in {$subject}.";
    }

    private static function generateSubtopics(string $subject, string $class, string $topic): array
    {
        $subj = strtolower($subject);
        $level = CurriculumData::getClassCategory($class);
        $count = $level === 'primary' ? 3 : ($level === 'junior' ? 4 : 5);

        $generic = [
            "Definition and Meaning of {$topic}",
            "Key Characteristics and Features of {$topic}",
            "Types and Classifications of {$topic}",
            "Importance and Applications of {$topic}",
            "Practical Examples and Case Studies of {$topic}",
        ];

        if (str_contains($subj, 'chemist')) {
            $generic = [
                "Chemical Composition and Structure of {$topic}",
                "Physical and Chemical Properties of {$topic}",
                "Reactions and Equations Involving {$topic}",
                "Importance and Applications of {$topic} in Industry and Daily Life",
                "Quantitative Analysis and Calculations Related to {$topic}",
            ];
        } elseif (str_contains($subj, 'physic')) {
            $generic = [
                "Definition and Fundamental Principles of {$topic}",
                "Mathematical Formulations and Calculations in {$topic}",
                "Practical Applications and Examples of {$topic}",
                "Experimental Investigations of {$topic}",
                "{$topic} in Technology and Everyday Life",
            ];
        } elseif (str_contains($subj, 'biology') || str_contains($subj, 'science')) {
            $generic = [
                "Structure and Organization of {$topic}",
                "Functions and Processes of {$topic}",
                "Types and Classification of {$topic}",
                "Importance of {$topic} to Living Organisms and the Environment",
                "Practical Observations and Laboratory Studies of {$topic}",
            ];
        } elseif (str_contains($subj, 'mathemat')) {
            $generic = [
                "Basic Concepts and Definitions of {$topic}",
                "Formulae and Methods for Solving {$topic} Problems",
                "Worked Examples and Step-by-Step Solutions for {$topic}",
                "Real-Life Applications of {$topic}",
                "Advanced Problems and Challenges in {$topic}",
            ];
        } elseif (str_contains($subj, 'english') || str_contains($subj, 'literature')) {
            $generic = [
                "Definition and Explanation of {$topic}",
                "Rules and Conventions Governing {$topic}",
                "Examples and Illustrations of {$topic}",
                "Common Errors and How to Avoid Them in {$topic}",
                "Practical Exercises and Applications of {$topic}",
            ];
        }

        $result = [];
        foreach (array_slice($generic, 0, $count) as $item) {
            $result[] = str_replace(['{$topic}', '{$subject}', '{$class}'], [$topic, $subject, $class], $item);
        }
        return $result;
    }

    private static function generateIntroduction(string $subject, string $class, string $topic): string
    {
        $level = CurriculumData::getClassCategory($class);

        if ($level === 'primary') {
            return "The teacher begins the lesson by asking students to mention what they already know about {$topic}. A fun story, song, or real-life example related to {$topic} is shared to capture the learners' attention. The teacher then announces: 'Today, we are going to learn about {$topic}.' The learning objectives are written on the board in simple, child-friendly language.";
        }
        if ($level === 'junior') {
            return "The teacher reviews the previous lesson and asks questions to assess students' prior knowledge of {$subject}. A thought-provoking question related to {$topic} is posed to stimulate curiosity and engage critical thinking. The teacher then introduces the new topic '{$topic}' and explains the learning objectives for the lesson, relating the topic to real-life situations in Nigeria.";
        }
        return "The teacher begins by connecting the lesson to students' existing knowledge of {$subject}. A brief discussion on the relevance of {$topic} in the Nigerian context and in the broader field of {$subject} is facilitated. Students are informed of what they will achieve by the end of the lesson, and the teacher sets clear expectations for the learning outcomes.";
    }

    private static function generateContentHtml(string $subject, string $class, string $topic, array $subtopics, string $difficulty = 'Medium'): string
    {
        $level = CurriculumData::getClassCategory($class);
        $html = '';

        foreach ($subtopics as $st) {
            $html .= "<h4>" . htmlspecialchars($st) . "</h4>";
            $html .= "<p>" . self::generateContentParagraph($subject, $class, $topic, $st, $difficulty) . "</p>";
        }

        $html .= "<h4>Key Points to Remember</h4><ul>";
        $bulletCount = $level === 'primary' ? 3 : ($level === 'junior' ? 4 : 5);
        $bullets = self::generateBulletPoints($subject, $topic, $bulletCount);
        foreach ($bullets as $b) {
            $html .= "<li>" . htmlspecialchars($b) . "</li>";
        }
        $html .= "</ul>";

        return $html;
    }

    private static function generateContentParagraph(string $subject, string $class, string $topic, string $subtopic, string $difficulty): string
    {
        $subj = strtolower($subject);
        $level = CurriculumData::getClassCategory($class);

        $paragraphs = self::getContentParagraphs($subj, $subject, $class, $topic, $subtopic, $difficulty, $level);

        if (empty($paragraphs)) {
            $paragraphs = [
                "When studying " . htmlspecialchars($subtopic) . ", it is important to focus on the specific facts, definitions, and principles that define this area of {$subject}. Students should take notes, ask questions, and practice applying what they have learned to ensure a thorough understanding of the material.",
                "The concept of " . htmlspecialchars($subtopic) . " in {$subject} requires careful attention to detail. Learners should work through examples step by step, identifying patterns and relationships that help connect new knowledge to what they already know.",
                htmlspecialchars($subtopic) . " is a key area within the broader topic of {$topic}. By mastering the content in this section, students develop critical thinking skills and gain confidence in their ability to tackle more advanced concepts in {$subject}.",
            ];
        }

        $key = abs(crc32($subtopic)) % count($paragraphs);
        return $paragraphs[$key];
    }

    private static function getContentParagraphs(string $subj, string $subject, string $class, string $topic, string $subtopic, string $difficulty, string $level): array
    {
        if (str_contains($subj, 'chemist')) {
            return [
                "The study of " . htmlspecialchars($subtopic) . " in Chemistry focuses on the composition, structure, and behaviour of substances. Students analyse how atoms and molecules interact through chemical bonds, reactions, and energy changes, forming the basis for understanding matter at the molecular level.",
                "In Chemistry, " . htmlspecialchars($subtopic) . " involves understanding the specific properties and transformations of chemical substances. This includes observing reactions, balancing equations, calculating quantities using mole concepts, and predicting outcomes based on chemical principles.",
                htmlspecialchars($subtopic) . " is fundamental to the Nigerian Chemistry curriculum at the {$class} level. Learners are expected to master the key concepts, perform relevant calculations, and connect theoretical knowledge to practical laboratory observations and real-world applications.",
                "When studying " . htmlspecialchars($subtopic) . ", Chemistry students must pay careful attention to symbols, formulae, and equations. Understanding the quantitative relationships between reactants and products through stoichiometry is essential for solving numerical problems and interpreting experimental data.",
            ];
        }

        if (str_contains($subj, 'physic')) {
            return [
                "In Physics, " . htmlspecialchars($subtopic) . " deals with the fundamental laws and principles that govern physical phenomena. Students learn to describe, measure, and predict the behaviour of physical systems using mathematical models and experimental methods.",
                "The study of " . htmlspecialchars($subtopic) . " requires Physics students to apply mathematical reasoning to physical situations. This involves identifying relevant variables, selecting appropriate formulae, performing calculations with correct units, and interpreting results in a physical context.",
                htmlspecialchars($subtopic) . " is a core component of the Nigerian Physics syllabus. Students explore the relationships between forces, energy, motion, and other physical quantities through theoretical study and practical laboratory investigations.",
                "Understanding " . htmlspecialchars($subtopic) . " in Physics helps students explain natural phenomena and technological applications. From the motion of vehicles to the operation of electrical devices, these principles are observable in everyday life and in advanced scientific contexts.",
            ];
        }

        if (str_contains($subj, 'biology') || str_contains($subj, 'science')) {
            return [
                "In Biology, " . htmlspecialchars($subtopic) . " examines the structures and processes that sustain life. Students explore the organization of living organisms from cells to systems, understanding how each level contributes to the functioning of the whole organism.",
                "The study of " . htmlspecialchars($subtopic) . " in Biology involves investigating the diversity of life, ecological relationships, and the physiological mechanisms that allow organisms to grow, reproduce, and respond to their environment.",
                htmlspecialchars($subtopic) . " is an important part of the Nigerian Biology curriculum for {$class}. Students learn to identify, describe, and explain the biological principles that underpin health, agriculture, and environmental conservation.",
                "When studying " . htmlspecialchars($subtopic) . " in Biology, learners develop skills in observation, classification, and analysis. Laboratory work involving specimens, models, and experiments helps reinforce theoretical knowledge and develops practical scientific skills.",
            ];
        }

        if (str_contains($subj, 'mathemat')) {
            return [
                "In Mathematics, " . htmlspecialchars($subtopic) . " requires students to understand and apply specific mathematical procedures and relationships. Mastery comes from practising problems systematically, checking work for accuracy, and building speed and confidence over time.",
                "The study of " . htmlspecialchars($subtopic) . " in Mathematics involves learning the relevant formulae, methods, and problem-solving strategies. Students should work through examples step by step, paying attention to each stage of the calculation or proof process.",
                htmlspecialchars($subtopic) . " is a key topic in the Nigerian Mathematics curriculum for {$class}. Regular practice with varied problems helps students develop fluency and the ability to apply mathematical thinking to both theoretical questions and real-life situations.",
                "When studying " . htmlspecialchars($subtopic) . " in Mathematics, learners should focus on understanding the underlying principles rather than memorizing steps. Connecting mathematical concepts to practical applications makes learning more meaningful and improves long-term retention.",
            ];
        }

        if (str_contains($subj, 'english') || str_contains($subj, 'literature')) {
            return [
                "In English Language, " . htmlspecialchars($subtopic) . " focuses on developing competence in understanding and using the English language effectively. This includes mastering the rules, structures, and conventions that govern communication in both spoken and written forms.",
                "The study of " . htmlspecialchars($subtopic) . " helps students improve their reading comprehension, writing skills, and oral communication. Learners are encouraged to practise regularly through reading, writing exercises, and class discussions.",
                htmlspecialchars($subtopic) . " is an essential component of the Nigerian English Language curriculum. Mastery of this area enables students to express themselves clearly, understand complex texts, and perform well in examinations.",
                "When studying " . htmlspecialchars($subtopic) . " in English Language, students should pay attention to examples and practise applying the rules in their own writing and speech. Regular practice and exposure to varied texts reinforces learning and builds confidence.",
            ];
        }

        if (str_contains($subj, 'econom')) {
            return [
                "In Economics, " . htmlspecialchars($subtopic) . " deals with the principles that govern the production, distribution, and consumption of goods and services. Students analyse how individuals, businesses, and governments make decisions about resource allocation in the face of scarcity.",
                "The study of " . htmlspecialchars($subtopic) . " in Economics requires students to understand key concepts, interpret data, and apply economic models to real-world situations. Learners should be able to explain economic phenomena using appropriate terminology and analytical frameworks.",
                htmlspecialchars($subtopic) . " is a significant area in the Nigerian Economics curriculum. Students explore how economic principles apply to the Nigerian context, including issues related to development, trade, monetary policy, and financial markets.",
            ];
        }

        if (str_contains($subj, 'govern') || str_contains($subj, 'civic')) {
            return [
                "In Government, " . htmlspecialchars($subtopic) . " examines the structures, processes, and institutions through which societies are governed. Students learn about political systems, constitutions, the rule of law, and the rights and responsibilities of citizens.",
                "The study of " . htmlspecialchars($subtopic) . " in Government provides learners with an understanding of how political power is organized and exercised. This includes analysing different forms of government, the electoral process, and the role of citizens in a democracy.",
                htmlspecialchars($subtopic) . " is an integral part of the Nigerian Government curriculum. Students explore the historical development of Nigeria's political system, the structure of government at federal and state levels, and contemporary political issues.",
            ];
        }

        if (str_contains($subj, 'geograph')) {
            return [
                "In Geography, " . htmlspecialchars($subtopic) . " involves the study of the Earth's physical features, atmosphere, and human activities across different regions. Students learn to interpret maps, analyse spatial patterns, and understand the relationships between people and their environment.",
                "The study of " . htmlspecialchars($subtopic) . " in Geography requires learners to develop skills in observation, data collection, and map reading. Fieldwork and the use of geographical tools help students connect theoretical knowledge to real-world observations.",
                htmlspecialchars($subtopic) . " is a key component of the Geography curriculum in Nigerian schools. Students explore both the physical geography of Nigeria and the human geographical factors that shape settlement patterns, economic activities, and environmental management.",
            ];
        }

        if (str_contains($subj, 'history')) {
            return [
                "In History, " . htmlspecialchars($subtopic) . " explores past events, societies, and developments that have shaped the present. Students learn to analyse historical sources, understand cause and effect, and develop perspective on contemporary issues through the study of the past.",
                "The study of " . htmlspecialchars($subtopic) . " in History provides insight into the political, social, economic, and cultural developments that have influenced Nigeria and the wider world. Learners develop critical thinking skills through the evaluation of evidence and interpretation of historical narratives.",
                htmlspecialchars($subtopic) . " is a significant area of the Nigerian History curriculum. Students examine key events, personalities, and movements in Nigerian history, from pre-colonial times through independence to the present day.",
            ];
        }

        return [];
    }

    private static function generateBulletPoints(string $subject, string $topic, int $count): array
    {
        $subj = strtolower($subject);

        if (str_contains($subj, 'chemist')) {
            $pool = [
                "Chemistry explains the composition, structure, and properties of all forms of matter around us.",
                "Understanding chemical reactions helps us explain everyday phenomena from cooking to rusting.",
                "The mole concept and stoichiometry are essential tools for quantitative chemical analysis.",
                "Chemical equations must be balanced to satisfy the law of conservation of mass.",
                "The periodic table organizes elements by their atomic number and chemical properties.",
            ];
        } elseif (str_contains($subj, 'physic')) {
            $pool = [
                "Physics explains the fundamental laws that govern motion, energy, forces, and matter.",
                "Understanding physics principles is essential for technological innovation and engineering.",
                "Measurements and SI units are the foundation of all physical calculations.",
                "Energy exists in various forms and can be converted from one form to another.",
                "Forces cause changes in the motion of objects according to Newton's laws.",
            ];
        } elseif (str_contains($subj, 'biology') || str_contains($subj, 'science')) {
            $pool = [
                "Biology is the study of living organisms and their interactions with the environment.",
                "The cell is the basic structural and functional unit of all living organisms.",
                "Living organisms are classified into kingdoms based on shared characteristics.",
                "Ecosystems consist of living and non-living components that interact in complex ways.",
                "Understanding biological processes helps us maintain health and manage natural resources.",
            ];
        } elseif (str_contains($subj, 'mathemat')) {
            $pool = [
                "Mathematics uses logical reasoning and precise methods to solve problems.",
                "Understanding mathematical concepts builds a foundation for science and technology.",
                "Regular practice with varied problems is essential for developing mathematical fluency.",
                "Mathematics helps develop critical thinking and analytical problem-solving skills.",
                "Mathematical principles are applied in everyday life from budgeting to measurements.",
            ];
        } elseif (str_contains($subj, 'english') || str_contains($subj, 'literature')) {
            $pool = [
                "English Language skills are essential for effective communication in all subjects.",
                "Understanding grammar rules helps in constructing clear and correct sentences.",
                "Reading widely improves vocabulary, comprehension, and writing ability.",
                "Literature exposes students to diverse cultures, ideas, and forms of creative expression.",
                "Effective writing requires planning, drafting, revising, and editing.",
            ];
        } elseif (str_contains($subj, 'econom')) {
            $pool = [
                "Economics studies how societies allocate scarce resources to meet unlimited wants.",
                "Supply and demand determine the prices of goods and services in a market economy.",
                "Understanding economic principles helps individuals make informed financial decisions.",
                "Government policies influence economic growth, employment, and price stability.",
                "Nigeria's economy is shaped by both domestic policies and global economic trends.",
            ];
        } elseif (str_contains($subj, 'govern') || str_contains($subj, 'civic')) {
            $pool = [
                "Government is the system through which a society is organized and governed.",
                "The Nigerian Constitution outlines the structure and powers of the three arms of government.",
                "Citizenship comes with both rights and responsibilities in a democratic society.",
                "The rule of law ensures that all persons and institutions are accountable to the law.",
                "Active civic participation strengthens democracy and promotes good governance.",
            ];
        } else {
            $pool = [
                "{$topic} is a key area of study within {$subject} that helps students understand important concepts and principles.",
                "Active engagement with the learning materials and regular revision are essential for mastering this subject.",
                "Connecting theoretical knowledge to practical examples helps deepen understanding and improve retention.",
                "Students are encouraged to ask questions, participate in discussions, and seek clarification when needed.",
                "Consistent practice and review of past topics builds confidence and prepares students for assessments.",
            ];
        }

        return array_slice($pool, 0, $count);
    }

    private static function generateExamples(string $subject, string $class, string $topic, string $difficulty = 'Medium'): array
    {
        $level = CurriculumData::getClassCategory($class);
        $isMath = str_contains(strtolower($subject), 'mathemat');
        $isPhysics = str_contains(strtolower($subject), 'physic');
        $isChem = str_contains(strtolower($subject), 'chemist');
        $isScience = $isPhysics || $isChem || str_contains(strtolower($subject), 'biology');

        $count = $level === 'primary' ? 2 : ($isMath || $isScience ? 10 : 3);

        $examples = [];
        for ($i = 1; $i <= $count; $i++) {
            $examples[] = [
                'title' => "Example {$i}: " . ($i === 1 ? "Basic Concept" : ($i === 2 ? "Practical Application" : ($i >= 10 ? "Advanced Worked Example {$i}" : ($i >= 4 ? "Worked Example {$i}" : "Application {$i}")))),
                'description' => ($isMath && $i >= 3)
                    ? self::generateMathExample($topic, $i, $level)
                    : ($isPhysics && $i >= 3
                        ? self::generatePhysicsExample($topic, $i, $level)
                        : ($isChem && $i >= 3
                            ? self::generateChemistryExample($topic, $i, $level)
                            : "Consider the following example related to {$topic} in {$subject}: " . self::getTopicExample($subject, $topic, $i, $class)
                        )
                    ),
            ];
        }
        return $examples;
    }

    private static function generateMathExample(string $topic, int $num, string $level): string
    {
        $examples = [
            3 => "Step 1: Identify the given information. Step 2: Write the relevant formula. Step 3: Substitute the values carefully. Step 4: Solve step by step. Step 5: Check your answer by working backwards. Practice with different numbers to build confidence.",
            4 => "A student scored 25 out of 40 in a test. What is the percentage score? Solution: (25 ÷ 40) × 100 = 62.5%. Answer: 62.5%. This shows moderate performance requiring improvement.",
            5 => "If a car travels 240 kilometres in 3 hours, what is its average speed? Solution: Speed = Distance ÷ Time = 240 km ÷ 3 h = 80 km/h. Therefore, the car travels at an average speed of 80 kilometres per hour.",
            6 => "Solve for x: 3x + 7 = 22. Solution: 3x + 7 = 22 → 3x = 22 - 7 → 3x = 15 → x = 15 ÷ 3 → x = 5. Answer: x = 5. Check: 3(5) + 7 = 15 + 7 = 22. Correct.",
            7 => "Find the area of a rectangle with length 12 cm and width 8 cm. Solution: Area = length × width = 12 cm × 8 cm = 96 cm². The area of the rectangle is 96 square centimetres.",
            8 => "Simplify: 2/3 + 3/4. Solution: LCM of 3 and 4 is 12. 2/3 = 8/12, 3/4 = 9/12. 8/12 + 9/12 = 17/12 = 1 5/12. Answer: 1 5/12.",
            9 => "A man bought a television for ₦85,000 and sold it for ₦93,500. What is his percentage profit? Profit = ₦93,500 - ₦85,000 = ₦8,500. Percentage profit = (₦8,500 ÷ ₦85,000) × 100 = 10%. Answer: 10% profit.",
            10 => "Calculate the volume of a cylinder with radius 7 cm and height 10 cm. (Take π = 22/7) Solution: Volume = πr²h = (22/7) × 7² × 10 = (22/7) × 49 × 10 = 22 × 7 × 10 = 1,540 cm³. The volume is 1,540 cubic centimetres.",
        ];
        return $examples[$num] ?? "Worked example {$num}: Apply the principles of {$topic} step by step. First, identify the given values. Second, recall the appropriate formula. Third, substitute and compute. Fourth, verify your answer. Practice makes perfect!";
    }

    private static function generatePhysicsExample(string $topic, int $num, string $level): string
    {
        $examples = [
            3 => "Step 1: Read the problem carefully and note all given quantities. Step 2: Identify the relevant physical principle or formula. Step 3: Convert all quantities to SI units if necessary. Step 4: Substitute values into the formula. Step 5: Calculate and include correct units in your answer.",
            4 => "A car accelerates uniformly from rest to 20 m/s in 10 seconds. Calculate its acceleration. Solution: a = (v - u)/t = (20 - 0)/10 = 2 m/s². The car's acceleration is 2 metres per second squared.",
            5 => "A stone of mass 2 kg is dropped from a height of 45 m. Calculate its potential energy at the top. (g = 10 m/s²) Solution: PE = mgh = 2 × 10 × 45 = 900 J. The potential energy is 900 joules.",
            6 => "A current of 3 A flows through a resistor of 5 Ω. Calculate the voltage across the resistor. Solution: V = IR = 3 × 5 = 15 V. The voltage across the resistor is 15 volts.",
            7 => "Calculate the force required to accelerate a 50 kg object at 4 m/s². Solution: F = ma = 50 × 4 = 200 N. A force of 200 newtons is required.",
            8 => "A wave has frequency 50 Hz and wavelength 6 m. Calculate its speed. Solution: v = fλ = 50 × 6 = 300 m/s. The wave speed is 300 metres per second.",
            9 => "An electric bulb rated 60 W is used for 5 hours daily. Calculate the energy consumed in kWh. Solution: Energy = Power × Time = 60 W × 5 h = 300 Wh = 0.3 kWh per day.",
            10 => "A lens has a focal length of 10 cm. Calculate its power. Solution: P = 1/f = 1/0.10 = 10 D. The power of the lens is 10 dioptres.",
        ];
        return $examples[$num] ?? "Worked example {$num}: Apply the relevant physics formula. Write down all known quantities. Select the correct equation. Substitute and solve. Always include units in your final answer.";
    }

    private static function generateChemistryExample(string $topic, int $num, string $level): string
    {
        $examples = [
            3 => "Step 1: Write the balanced chemical equation. Step 2: Identify the known and unknown quantities. Step 3: Use mole ratios from the balanced equation. Step 4: Convert between mass, moles, and volume as needed. Step 5: Calculate the answer with correct significant figures.",
            4 => "Calculate the relative molecular mass of H₂SO₄. (H=1, S=32, O=16) Solution: 2(1) + 32 + 4(16) = 2 + 32 + 64 = 98 g/mol. The relative molecular mass of sulphuric acid is 98 grams per mole.",
            5 => "What is the percentage by mass of oxygen in H₂O? (H=1, O=16) Solution: Molar mass of H₂O = 2(1) + 16 = 18 g/mol. Mass of oxygen = 16 g. % O = (16/18) × 100 = 88.89%. Water is 88.89% oxygen by mass.",
            6 => "How many moles are in 20 g of calcium carbonate (CaCO₃)? (Ca=40, C=12, O=16) Solution: Molar mass = 40 + 12 + 48 = 100 g/mol. Moles = 20/100 = 0.20 mol. There are 0.20 moles in 20 g of CaCO₃.",
            7 => "Balance the equation: Fe + O₂ → Fe₂O₃. Solution: 4Fe + 3O₂ → 2Fe₂O₃. The balanced equation shows that 4 iron atoms react with 3 oxygen molecules to produce 2 formula units of iron(III) oxide.",
            8 => "Calculate the concentration of a solution containing 5 g of NaOH dissolved in 500 mL of water. (NaOH = 40 g/mol) Solution: Moles = 5/40 = 0.125 mol. Concentration = 0.125/0.500 = 0.25 mol/dm³.",
            9 => "What volume of 0.1 M HCl is needed to neutralise 25 cm³ of 0.2 M NaOH? Solution: HCl + NaOH → NaCl + H₂O. Moles of NaOH = 0.2 × 0.025 = 0.005 mol. Volume of HCl = 0.005/0.1 = 0.05 L = 50 cm³.",
            10 => "Calculate the pH of a 0.01 M HCl solution. Solution: [H⁺] = 0.01 M = 10⁻² M. pH = -log[H⁺] = -log(10⁻²) = 2. The pH of 0.01 M HCl is 2, indicating a strongly acidic solution.",
        ];
        return $examples[$num] ?? "Worked example {$num}: Apply stoichiometric principles. Write the balanced equation. Convert given quantities to moles. Use mole ratios. Calculate the required quantity. Always include appropriate units.";
    }

    private static function getTopicExample(string $subject, string $topic, int $num, string $class): string
    {
        $examples = [
            "A student in {$class} wants to understand {$topic}. Here is a simple way to think about it using everyday items found in a Nigerian home. Relating new concepts to familiar objects helps make learning more meaningful and memorable.",
            "In a {$class} classroom, students can demonstrate their understanding of {$topic} by working through exercises from their textbook. This practical approach helps reinforce the concept through active learning.",
            "Let us apply {$topic} to a real situation: Consider how this concept appears in the daily life of a Nigerian family, business, or community. Understanding the practical relevance of topics makes {$subject} more engaging and valuable.",
        ];
        return $examples[($num - 1) % count($examples)];
    }

    private static function generateActivities(string $subject, string $class, string $topic): array
    {
        $level = CurriculumData::getClassCategory($class);
        $count = $level === 'primary' ? 2 : 3;

        $activities = [
            [
                'title' => 'Group Discussion',
                'description' => "Divide the class into small groups. Each group discusses the concept of {$topic} in {$subject} and prepares a summary of their understanding. Groups take turns presenting their findings to the class. The teacher facilitates and provides clarification where needed.",
            ],
            [
                'title' => 'Individual Exercise',
                'description' => "Students work independently to complete a worksheet on {$topic}. The teacher moves around the classroom to provide individual support and guidance. This allows each student to practice at their own pace and receive personalized feedback.",
            ],
            [
                'title' => 'Class Presentation',
                'description' => "Selected students present their understanding of {$topic} to the class. This builds confidence, develops communication skills, and reinforces learning through peer teaching. The audience is encouraged to ask questions.",
            ],
        ];

        return array_slice($activities, 0, $count);
    }

    private static function generateEvaluationQuestions(string $subject, string $topic, array $objectives): array
    {
        $questions = [];
        foreach ($objectives as $i => $obj) {
            $num = $i + 1;
            preg_match('/:\s*\d+\.\s*(.+?)(?:\.|$)/', $obj, $m);
            $task = $m[1] ?? "explain {$topic}";
            $questions[] = "{$num}. " . ucfirst(trim($task)) . " as it relates to {$topic} in {$subject}.";
        }
        $questions[] = (count($questions) + 1) . ". Give a practical example of {$topic} from your community or environment.";
        return $questions;
    }

    private static function generateDetailedNote(string $subject, string $class, string $topic, array $subtopics, array $objectives, string $difficulty): string
    {
        $year = date('Y');
        $subj = strtolower($subject);

        $note = "LESSON NOTE: {$subject} - {$topic}\n";
        $note .= "Class: {$class}\n";
        $note .= "Academic Year: {$year}\n";
        $note .= "Difficulty Level: {$difficulty}\n";
        $note .= str_repeat("=", 50) . "\n\n";

        $note .= "LEARNING OBJECTIVES:\n";
        foreach ($objectives as $o) {
            $note .= "  \u{2022} {$o}\n";
        }
        $note .= "\n";

        $note .= "INTRODUCTION:\n";
        $note .= "This lesson focuses on {$topic} in {$subject} for {$class}. Students will explore the key concepts, principles, and applications of this topic through structured learning activities, examples, and practice exercises.\n\n";

        $note .= "CONTENT DEVELOPMENT:\n\n";
        foreach ($subtopics as $i => $st) {
            $note .= ($i + 1) . ". " . strip_tags($st) . ":\n";
            $note .= "   " . self::generateDetailedSection($subject, $class, $topic, $st, $subj, $difficulty) . "\n\n";
        }

        $note .= "KEY POINTS TO NOTE:\n";
        if (str_contains($subj, 'chemist')) {
            $note .= "  \u{2022} Chemical substances have specific compositions and properties that determine their behaviour.\n";
            $note .= "  \u{2022} Balanced chemical equations are essential for quantitative analysis of reactions.\n";
            $note .= "  \u{2022} Understanding chemical principles helps explain natural phenomena and industrial processes.\n";
        } elseif (str_contains($subj, 'physic')) {
            $note .= "  \u{2022} Physical quantities must be measured using appropriate instruments and SI units.\n";
            $note .= "  \u{2022} Scientific laws and principles form the foundation for understanding physical phenomena.\n";
            $note .= "  \u{2022} Practical experimentation and data analysis are essential skills in Physics.\n";
        } elseif (str_contains($subj, 'biology') || str_contains($subj, 'science')) {
            $note .= "  \u{2022} Life processes are governed by complex biological systems and mechanisms.\n";
            $note .= "  \u{2022} Classification helps organize the diversity of living organisms into manageable groups.\n";
            $note .= "  \u{2022} Ecological relationships between organisms and their environment maintain balance in nature.\n";
        } elseif (str_contains($subj, 'mathemat')) {
            $note .= "  \u{2022} Mathematical problems require systematic approaches and careful calculations.\n";
            $note .= "  \u{2022} Understanding formulae and knowing when to apply them is key to success.\n";
            $note .= "  \u{2022} Regular practice with varied problems builds speed, accuracy, and confidence.\n";
        } else {
            $note .= "  \u{2022} Key concepts and definitions should be clearly understood before moving forward.\n";
            $note .= "  \u{2022} Regular revision and practice help reinforce learning and improve retention.\n";
            $note .= "  \u{2022} Applying knowledge to practical situations deepens understanding.\n";
        }
        $note .= "\n";

        $note .= "SUMMARY:\n";
        $note .= "This lesson covered the topic of {$topic} in {$subject}. Students explored the main concepts, worked through relevant examples, and engaged in learning activities designed to reinforce their understanding. Continued practice and revision will help consolidate these concepts and prepare students for the next stage of learning.\n\n";

        $note .= "EVALUATION:\n";
        $note .= "Students will be assessed through:\n";
        $note .= "  \u{2022} Oral questions and class participation during the lesson\n";
        $note .= "  \u{2022} Written exercises and problem-solving tasks\n";
        $note .= "  \u{2022} End-of-lesson review questions\n";
        $note .= "  \u{2022} Homework assignments for independent practice\n\n";

        $note .= "ASSIGNMENT:\n";
        if (str_contains($subj, 'mathemat') || str_contains($subj, 'physic') || str_contains($subj, 'chemist')) {
            $note .= "  1. Complete all practice problems related to this topic in your textbook\n";
            $note .= "  2. Create a summary note of the key formulae and concepts covered\n";
            $note .= "  3. Attempt the end-of-chapter review questions\n";
            $note .= "  4. Prepare three of your own questions to ask in the next class\n";
        } else {
            $note .= "  1. Write a summary of the key points covered in today's lesson\n";
            $note .= "  2. Answer the review questions at the end of the chapter\n";
            $note .= "  3. Research and note down two real-life applications of this topic\n";
            $note .= "  4. Prepare for a brief class discussion on this topic in the next lesson\n";
        }

        return $note;
    }

    private static function generateDetailedSection(string $subject, string $class, string $topic, string $subtopic, string $subj, string $difficulty): string
    {
        if (str_contains($subj, 'chemist')) {
            $sections = [
                "This section examines the chemical nature of {$topic}, focusing on its composition, structure, and the principles that govern its behaviour in chemical reactions.",
                "Here we explore the properties and characteristics of {$topic}, including how it interacts with other substances and the conditions that affect these interactions.",
                "This part covers the quantitative aspects of {$topic}, including relevant calculations, measurements, and the application of chemical formulae and equations.",
                "The practical importance of {$topic} is examined through its applications in industry, medicine, agriculture, and everyday life in Nigeria.",
                "This section deals with laboratory procedures, observations, and experimental techniques related to the study of {$topic}.",
            ];
        } elseif (str_contains($subj, 'physic')) {
            $sections = [
                "This section introduces the fundamental principles of {$topic}, including the key definitions and physical laws that describe its behaviour.",
                "Here we examine the mathematical relationships and formulae used to calculate and predict physical quantities related to {$topic}.",
                "This part explores real-world applications and examples of {$topic}, demonstrating how physics principles operate in everyday situations.",
                "Experimental methods for investigating {$topic} are covered, including measurement techniques, data collection, and analysis procedures.",
                "The technological and industrial applications of {$topic} are discussed, highlighting the connection between physics and innovation.",
            ];
        } elseif (str_contains($subj, 'biology') || str_contains($subj, 'science')) {
            $sections = [
                "This section describes the basic structure and organization of {$topic}, including the key components and how they are arranged.",
                "Here we examine the functions and processes associated with {$topic}, understanding how living systems operate and maintain life.",
                "This part covers the different types and categories of {$topic}, providing a framework for understanding its diversity and complexity.",
                "The ecological and environmental significance of {$topic} is explored, including its role in maintaining balance in nature.",
                "Laboratory and field studies related to {$topic} are discussed, including observation techniques and practical investigations.",
            ];
        } elseif (str_contains($subj, 'mathemat')) {
            $sections = [
                "This section introduces the basic concepts and definitions of {$topic}, establishing the foundation for understanding more complex applications.",
                "Here we learn the formulae and methods used to solve problems involving {$topic}, working through examples step by step.",
                "This part provides worked examples with complete solutions, demonstrating the correct approach to solving problems on {$topic}.",
                "Real-life applications of {$topic} are explored, showing how mathematical concepts are used in practical situations.",
                "Advanced problems and challenges related to {$topic} are presented to develop higher-order thinking and problem-solving skills.",
            ];
        } elseif (str_contains($subj, 'english') || str_contains($subj, 'literature')) {
            $sections = [
                "This section defines and explains {$topic}, providing a clear understanding of its meaning and usage in English Language.",
                "Here we examine the rules and conventions that govern {$topic}, including guidelines for correct usage in writing and speech.",
                "Examples and illustrations of {$topic} are provided to demonstrate how it is correctly used in different contexts.",
                "Common errors related to {$topic} are identified and explained, helping students avoid mistakes in their own work.",
                "Practical exercises and activities help students practise and apply their knowledge of {$topic} in meaningful ways.",
            ];
        } else {
            $sections = [
                "This section provides an overview of the key concepts and ideas related to this topic in {$subject}.",
                "Here we examine the main features and characteristics that define this area of study.",
                "The practical applications and real-world relevance of this topic are explored with relevant examples.",
                "This section covers important facts, data, and information that students need to master.",
            ];
        }

        $key = abs(crc32($subtopic)) % count($sections);
        return $sections[$key];
    }

    private static function generateSingleQuestion(string $subject, string $topic, int $num): array
    {
        $seed = crc32($topic . $subject . $num);
        srand($seed);

        // Each archetype is a COMPLETE question style with its own answer and distractor patterns.
        // Archetypes alternate so adjacent questions look completely different.
        $archetypes = [

            // --- DEFINITION style ---
            ['question' => 'Which of the following is the correct definition of {$topic} in {$subject}?',
             'correct' => '{$topic} is a core concept in {$subject} that involves specific principles, rules, and applications unique to this topic.',
             'wrong' => [
                '{$topic} is a general term used across all subjects with no specific meaning in {$subject}',
                '{$topic} refers to a completely different topic studied outside of {$subject}',
                '{$topic} is the same as another unrelated concept in {$subject}',
             ]],

            // --- COMPONENT style ---
            ['question' => 'Which of the following is a major component of {$topic} in {$subject}?',
             'correct' => 'One of the key components of {$topic} is understanding its foundational principles and how they apply to {$subject} problems.',
             'wrong' => [
                'Memorizing facts without understanding the underlying principles of {$topic}',
                'Ignoring the relationship between {$topic} and other {$subject} topics',
                'Focusing only on unrelated topics outside of {$subject}',
             ]],

            // --- EXAMPLE style ---
            ['question' => 'Which of the following is a good example of {$topic} as taught in {$subject}?',
             'correct' => 'A practical example of {$topic} can be seen when applying {$subject} knowledge to solve problems related to this topic.',
             'wrong' => [
                'An example taken from a completely different subject area',
                'A situation where {$topic} knowledge is deliberately ignored',
                'A common everyday activity that has no connection to {$subject}',
             ]],

            // --- IMPORTANCE style ---
            ['question' => 'Why is {$topic} considered an important topic in {$subject}?',
             'correct' => '{$topic} is important because it provides the foundation for understanding more advanced concepts and solving real-world problems in {$subject}.',
             'wrong' => [
                '{$topic} is not actually important and is rarely used in {$subject}',
                '{$topic} is only important for passing exams and has no practical value',
                '{$topic} is important only for students who want to specialize in other subjects',
             ]],

            // --- APPLICATION style ---
            ['question' => 'How is {$topic} applied in real-world {$subject} situations?',
             'correct' => '{$topic} is applied by using its principles to analyze, interpret, and solve practical problems encountered in {$subject}-related fields.',
             'wrong' => [
                '{$topic} has no real-world applications and is only studied theoretically',
                '{$topic} can only be applied in laboratory settings, not in everyday life',
                'The application of {$topic} is limited to written examinations only',
             ]],

            // --- CLASSIFICATION style ---
            ['question' => 'Which of the following best categorizes the scope of {$topic} in {$subject}?',
             'correct' => '{$topic} covers a range of subtopics and principles that collectively form an important area of study within the {$subject} curriculum.',
             'wrong' => [
                '{$topic} is a single narrow concept with no subtopics or related ideas',
                '{$topic} belongs to a different subject entirely and is not part of {$subject}',
                '{$topic} covers everything in {$subject} and has no specific boundaries',
             ]],

            // --- CHARACTERISTIC style ---
            ['question' => 'Which of the following is a key characteristic of {$topic}?',
             'correct' => 'A defining characteristic of {$topic} is that it requires both theoretical understanding and practical application within the context of {$subject}.',
             'wrong' => [
                '{$topic} is purely theoretical with no practical component in {$subject}',
                '{$topic} is easy and requires no effort to understand or apply',
                '{$topic} is identical to every other topic in {$subject}',
             ]],

            // --- DISTINCTION style ---
            ['question' => 'How does {$topic} differ from other topics in {$subject}?',
             'correct' => '{$topic} differs from other topics because it focuses on specific concepts and methods that are unique to this area of {$subject}.',
             'wrong' => [
                '{$topic} is exactly the same as every other topic in {$subject}',
                '{$topic} is not actually taught as part of {$subject}',
                'There is no difference between {$topic} and unrelated everyday knowledge',
             ]],

            // --- PREREQUISITE style ---
            ['question' => 'What knowledge is needed before studying {$topic} in {$subject}?',
             'correct' => 'Before studying {$topic}, students should understand the foundational concepts in {$subject} that serve as building blocks for this topic.',
             'wrong' => [
                'No prior knowledge is needed; {$topic} can be studied by anyone at any level',
                'Students must first study unrelated topics that have no connection to {$topic}',
                'Only advanced mathematics knowledge is needed, regardless of the subject',
             ]],

            // --- OUTCOME style ---
            ['question' => 'What should a student be able to do after learning {$topic} in {$subject}?',
             'correct' => 'After learning {$topic}, a student should be able to identify, explain, and apply its key principles to solve {$subject} problems independently.',
             'wrong' => [
                'The student should be able to teach {$subject} to others without any preparation',
                'The student should forget everything learned after the examination',
                'The student should be able to answer questions about unrelated topics',
             ]],

            // --- RELATIONSHIP style ---
            ['question' => 'How does {$topic} relate to the broader {$subject} curriculum?',
             'correct' => '{$topic} connects to other topics in {$subject} by building on prior knowledge and providing a foundation for more advanced study.',
             'wrong' => [
                '{$topic} is isolated and has no relationship to any other part of {$subject}',
                '{$topic} contradicts everything else taught in {$subject}',
                '{$topic} replaces all other topics in the {$subject} curriculum',
             ]],

            // --- COMMON ERROR style ---
            ['question' => 'Which of the following is a common mistake students make when studying {$topic}?',
             'correct' => 'A common mistake is confusing the specific principles of {$topic} with similar but different concepts from other areas of {$subject}.',
             'wrong' => [
                'Spending too much time studying {$topic} and not enough on other subjects',
                'Reading the textbook carefully and taking detailed notes',
                'Asking the teacher questions when something about {$topic} is unclear',
             ]],

            // --- EVALUATION style ---
            ['question' => 'Which of the following questions can be answered using knowledge of {$topic}?',
             'correct' => 'A question that requires applying the principles of {$topic} to analyze a situation or solve a problem in {$subject}.',
             'wrong' => [
                'A question about a historical event that has no connection to {$subject}',
                'A question that can be answered using common sense without studying {$subject}',
                'A question about a completely different subject that is not related to {$topic}',
             ]],

            // --- SCOPE style ---
            ['question' => 'What is included in the study of {$topic} in {$subject}?',
             'correct' => 'The study of {$topic} includes its key principles, methods, applications, and how it connects to other areas within {$subject}.',
             'wrong' => [
                'The study of {$topic} includes unrelated topics from other school subjects',
                'The study of {$topic} is limited to a single definition with no further content',
                'The study of {$topic} covers only practical work with no theoretical background',
             ]],

            // --- TRUE STATEMENT style ---
            ['question' => 'Which statement about {$topic} is true?',
             'correct' => '{$topic} is an important area of {$subject} that helps students develop critical thinking and problem-solving skills.',
             'wrong' => [
                '{$topic} is only for advanced students and should not be taught at this level',
                '{$topic} has been removed from the {$subject} curriculum',
                '{$topic} is the same thing as another topic with a different name',
             ]],

            // --- FALSE STATEMENT style ---
            ['question' => 'Which of the following is NOT true about {$topic}?',
             'correct' => 'It is not true that {$topic} can be fully understood without studying the related concepts in {$subject}.',
             'wrong' => [
                '{$topic} requires practice and application to master',
                '{$topic} is part of the {$subject} curriculum for this class level',
                'Understanding {$topic} helps in solving {$subject} problems',
             ]],

            // --- PURPOSE style ---
            ['question' => 'What is the main purpose of teaching {$topic} in {$subject}?',
             'correct' => 'The purpose is to equip students with the knowledge and skills needed to understand and apply {$topic} principles in {$subject} and beyond.',
             'wrong' => [
                'The purpose is to make the {$subject} curriculum more difficult for students',
                'The purpose is to fill time in the academic term with no specific learning goals',
                'The purpose is to test students memory rather than their understanding',
             ]],

            // --- METHOD style ---
            ['question' => 'Which approach is best for understanding {$topic} in {$subject}?',
             'correct' => 'The best approach is to study the principles of {$topic} step by step and practice applying them to different {$subject} problems.',
             'wrong' => [
                'The best approach is to memorize all the answers without understanding',
                'The best approach is to skip {$topic} and focus on other subjects entirely',
                'The best approach is to guess randomly and hope for the best',
             ]],

            // --- COMPARISON style ---
            ['question' => 'How should {$topic} be studied compared to other topics in {$subject}?',
             'correct' => 'Like other topics in {$subject}, {$topic} should be studied with attention to its unique principles and how they connect to the broader subject.',
             'wrong' => [
                '{$topic} should be studied in isolation without connecting to other topics',
                '{$topic} is less important and deserves less study time than other topics',
                '{$topic} should only be studied if there is extra time at the end of the term',
             ]],

            // --- EXAM STYLE ---
            ['question' => 'In a {$subject} examination, a question on {$topic} might ask a student to:',
             'correct' => 'Apply their knowledge of {$topic} to analyze a given scenario and select the correct response based on {$subject} principles.',
             'wrong' => [
                'Write an essay about a topic that has nothing to do with {$subject}',
                'Recite a poem or story unrelated to {$topic} or {$subject}',
                'Draw a picture without any connection to the {$subject} curriculum',
             ]],

            // --- STEP style ---
            ['question' => 'What is the first step in learning {$topic} in {$subject}?',
             'correct' => 'The first step is to understand the basic definitions and core concepts that form the foundation of {$topic} in {$subject}.',
             'wrong' => [
                'The first step is to take an examination before studying the topic',
                'The first step is to ignore the teacher and study on your own without guidance',
                'The first step is to move on to advanced topics without understanding the basics',
             ]],

            // --- BENEFIT style ---
            ['question' => 'How does studying {$topic} benefit a {$subject} student?',
             'correct' => 'Studying {$topic} helps students develop a deeper understanding of {$subject} and prepares them for more advanced study and real-world applications.',
             'wrong' => [
                'Studying {$topic} has no benefit and is a waste of time for students',
                'The only benefit of studying {$topic} is getting good grades on the report card',
                'Studying {$topic} only benefits students who want to change subjects later',
             ]],

            // --- CHALLENGE style ---
            ['question' => 'What makes {$topic} challenging for some {$subject} students?',
             'correct' => '{$topic} can be challenging because it requires abstract thinking and the ability to connect multiple concepts within {$subject}.',
             'wrong' => [
                '{$topic} is challenging only because the textbook is difficult to read',
                '{$topic} is challenging because teachers deliberately make it hard for students',
                '{$topic} is not actually challenging; students who struggle are not trying hard enough',
             ]],

            // --- FOUNDATION style ---
            ['question' => 'Why is {$topic} considered a foundational topic in {$subject}?',
             'correct' => '{$topic} is foundational because it introduces key ideas and skills that are built upon in more advanced {$subject} topics throughout the curriculum.',
             'wrong' => [
                '{$topic} is not foundational; it is an optional topic that can be skipped',
                '{$topic} is taught at the end of the term as a review of unrelated concepts',
                '{$topic} is only a review of what students learned in previous classes',
             ]],

            // --- CONNECTION style ---
            ['question' => 'What other subjects connect to {$topic} in {$subject}?',
             'correct' => '{$topic} in {$subject} connects to other subjects by sharing principles and applications that are relevant across the broader school curriculum.',
             'wrong' => [
                '{$topic} does not connect to any other subject in any way',
                '{$topic} only connects to subjects that are not taught in Nigerian schools',
                '{$topic} replaces the need to study any other subject',
             ]],

            // --- ASSESSMENT style ---
            ['question' => 'How is a students understanding of {$topic} typically assessed in {$subject}?',
             'correct' => 'Understanding of {$topic} is assessed through questions that require students to recall, explain, and apply its principles to {$subject} problems.',
             'wrong' => [
                'Students are assessed only on their ability to memorize facts about {$topic}',
                'Students are not assessed on {$topic} at all; it is not part of the examination',
                'Assessment of {$topic} is based on group work rather than individual understanding',
             ]],

            // --- TEACHING style ---
            ['question' => 'How should a teacher introduce {$topic} to a {$subject} class?',
             'correct' => 'A teacher should start by connecting {$topic} to what students already know in {$subject}, then build understanding step by step with examples.',
             'wrong' => [
                'The teacher should give a test on {$topic} before teaching anything about it',
                'The teacher should skip the introduction and move directly to advanced content',
                'The teacher should tell students that {$topic} is too hard for them to understand',
             ]],

            // --- REVIEW style ---
            ['question' => 'What should a student review before being tested on {$topic} in {$subject}?',
             'correct' => 'A student should review the key definitions, principles, and examples of {$topic} and practice applying them to {$subject} problems.',
             'wrong' => [
                'The student should review topics from other subjects that are unrelated to {$subject}',
                'The student does not need to review because the test on {$topic} will be very easy',
                'The student should only review the answers without understanding the questions',
             ]],

            // --- MISTAKE style ---
            ['question' => 'Which error might a student make when answering a question about {$topic}?',
             'correct' => 'A student might confuse {$topic} with a related concept in {$subject} and apply the wrong principle to solve the problem.',
             'wrong' => [
                'A student might answer too quickly and finish the exam before the time is up',
                'A student might help other students instead of focusing on their own work',
                'A student might write the answer in the wrong place on the answer sheet',
             ]],
        ];

        $index = abs($num + $seed) % count($archetypes);
        $archetype = $archetypes[$index];

        // Build question with placeholders replaced
        $q = str_replace(['{$topic}', '{$subject}'], [$topic, $subject], $archetype['question']);
        $correctAnswer = str_replace(['{$topic}', '{$subject}'], [$topic, $subject], $archetype['correct']);

        // Build wrong options with placeholders replaced and ensure they differ
        $wrongRaw = array_map(fn($w) => str_replace(['{$topic}', '{$subject}'], [$topic, $subject], $w), $archetype['wrong']);

        // Shuffle and pick up to 3 unique wrong options
        shuffle($wrongRaw);
        $wrongSelected = array_slice($wrongRaw, 0, 3);

        // Combine and ensure 4 unique options
        $allOptions = [$correctAnswer, ...$wrongSelected];
        $allOptions = array_unique(array_values($allOptions));
        while (count($allOptions) < 4) {
            $allOptions[] = "Another concept related to {$topic} in the study of {$subject}";
            $allOptions = array_unique(array_values($allOptions));
        }
        $allOptions = array_values(array_slice($allOptions, 0, 4));

        // Shuffle to randomize answer position
        $letters = ['A', 'B', 'C', 'D'];
        shuffle($allOptions);
        $options = [];
        $correctLetter = 'A';
        foreach ($letters as $i => $letter) {
            $options[$letter] = $allOptions[$i];
            if ($allOptions[$i] === $correctAnswer) {
                $correctLetter = $letter;
            }
        }

        srand();

        return [
            'id' => $num,
            'question' => $q,
            'A' => $options['A'],
            'B' => $options['B'],
            'C' => $options['C'],
            'D' => $options['D'],
            'answer' => $correctLetter,
        ];
    }

    private static function generateTheoryQuestions(string $subject, string $topic): array
    {
        return [
            [
                'question' => "Explain in detail the concept of {$topic} as it applies to {$subject}. Include its definition, key characteristics, and importance.",
                'answer' => "{$topic} is a fundamental concept in {$subject} that encompasses various aspects including principles, applications, and real-world relevance. In the Nigerian curriculum context, {$topic} is taught across multiple class levels with increasing depth and complexity, preparing students for both examinations and practical application."
            ],
            [
                'question' => "Discuss the practical applications of {$topic} in everyday life, with specific reference to Nigeria.",
                'answer' => "{$topic} has numerous practical applications in everyday life. In Nigeria, {$topic} can be observed in areas such as education, business, technology, and community development. Understanding {$topic} helps students appreciate how {$subject} concepts apply to real-world situations and prepares them for higher education and careers in various fields."
            ],
        ];
    }

    private static function generateEssayQuestions(string $subject, string $topic): array
    {
        return [
            [
                'question' => "Write a comprehensive essay on the importance of {$topic} in {$subject}.",
                'guidance' => "Include definition, key concepts, types/classifications, importance, real-world examples, and relevance to the Nigerian context. Support your points with specific examples."
            ],
        ];
    }

    private static function generateStructuredQuestions(string $subject, string $topic): array
    {
        return [
            [
                'question' => "Describe {$topic} with reference to {$subject}.",
                'parts' => [
                    'a' => "Define {$topic}",
                    'b' => "List five key features of {$topic}",
                    'c' => "Explain three importance of {$topic}",
                    'd' => "Give two practical examples of {$topic} from the Nigerian context",
                ],
            ],
        ];
    }

    private static function getSubjectSpecificMaterials(string $subject, string $topic): array
    {
        $subj = strtolower($subject);
        if (str_contains($subj, 'mathemat')) return ['Counters and abacus', 'Geometric shapes', 'Number line chart', 'Measuring tools', 'Graph paper'];
        if (str_contains($subj, 'physic')) return ['Laboratory apparatus', 'Meters and measuring devices', 'Experimental setup materials', 'Circuit components'];
        if (str_contains($subj, 'chemist')) return ['Laboratory chemicals and apparatus', 'Safety goggles', 'Periodic table chart', 'Test tubes and beakers'];
        if (str_contains($subj, 'biology') || str_contains($subj, 'science')) return ['Specimens and models', 'Magnifying glass/microscope', 'Charts of biological systems', 'Dissection tools'];
        if (str_contains($subj, 'english') || str_contains($subj, 'literature')) return ['Reading passages', 'Dictionary', 'Grammar charts', 'Storybooks', 'Thesaurus'];
        if (str_contains($subj, 'geograph')) return ['Maps and globes', 'Atlas', 'Compass', 'Weather charts', 'Satellite images'];
        if (str_contains($subj, 'history')) return ['Timeline charts', 'Historical pictures', 'Textbook with Nigerian history', 'Primary source documents'];
        if (str_contains($subj, 'govern') || str_contains($subj, 'civic')) return ['Nigerian Constitution booklet', 'Posters of government structures', 'Newspaper cuttings', 'Voter education materials'];
        if (str_contains($subj, 'econom')) return ['Graph charts', 'Price lists', 'Newspaper business sections', 'Economic indicators data'];
        if (str_contains($subj, 'account') || str_contains($subj, 'commerce')) return ['Sample ledgers and journals', 'Invoice templates', 'Business transaction examples', 'Financial statements'];
        if (str_contains($subj, 'agric')) return ['Farm tools and equipment', 'Seed samples', 'Posters of crop/livestock', 'Soil samples'];
        return [];
    }
}
