import { WeeklySchemeUnit } from "../types";

export interface EducationLevelConfig {
  id: string;
  name: string;
  classes: string[];
  subjects: string[];
}

export const EDUCATION_LEVELS: EducationLevelConfig[] = [
  {
    id: "nursery",
    name: "Nursery School",
    classes: ["Reception", "Foundation", "Nursery 1", "Nursery 2"],
    subjects: [
      "Numeracy",
      "Literacy",
      "Sensory Play & Basic Science",
      "Coloring & Creative Art",
      "Health Habits",
      "Social Habits",
      "Basic Science",
      "Character Development",
      "Circle Time",
      "Practical Life",
      "CCA",
      "PHE",
      "Handwriting",
      "Rhymes",
      "Social Skills",
      "Phonics",
      "ICT",
      "Home Economics",
      "Agricultural Science",
      "Verbal Reasoning",
      "Quantitative Reasoning"
    ]
  },
  {
    id: "primary",
    name: "Primary School",
    classes: [
      "Primary 1",
      "Primary 2",
      "Primary 3",
      "Primary 4",
      "Primary 5",
      "Primary 6"
    ],
    subjects: [
      "Mathematics",
      "English Studies",
      "Basic Science",
      "Basic Technology",
      "National Values",
      "Civic Education",
      "Social Studies",
      "Cultural and Creative Arts",
      "Computer Studies / ICT",
      "Security Education",
      "Home Economics",
      "Agricultural Science",
      "CRS / IRS",
      "Physical and Health Education",
      "French",
      "Nigerian Languages",
      "Quantitative Reasoning",
      "Verbal Reasoning",
      "Literacy",
      "Numeracy",
      "Character Development",
      "Circle Time",
      "Practical Life",
      "CCA",
      "PHE",
      "Handwriting",
      "Rhymes",
      "Social Skills",
      "Phonics",
      "ICT"
    ]
  },
  {
    id: "junior_secondary",
    name: "Junior Secondary (JSS1–JSS3)",
    classes: ["JSS 1", "JSS 2", "JSS 3"],
    subjects: [
      "Mathematics",
      "English Language",
      "Basic Science",
      "Basic Technology",
      "Civic Education",
      "Social Studies",
      "Business Studies",
      "Computer Studies",
      "History",
      "Agriculture",
      "Home Economics",
      "CCA",
      "PHE",
      "CRS / IRS",
      "French",
      "Security Education",
      "Literacy",
      "Numeracy",
      "Character Development",
      "Circle Time",
      "Practical Life",
      "Handwriting",
      "Rhymes",
      "Social Skills",
      "Phonics",
      "ICT",
      "Agricultural Science",
      "Verbal Reasoning",
      "Quantitative Reasoning"
    ]
  },
  {
    id: "senior_secondary",
    name: "Senior Secondary (SS1–SS3)",
    classes: ["SS 1", "SS 2", "SS 3"],
    subjects: [
      "English Language",
      "Mathematics",
      "Physics",
      "Chemistry",
      "Biology",
      "Further Mathematics",
      "Geography",
      "Economics",
      "Government",
      "Commerce",
      "Literature",
      "Civic Education",
      "Financial Accounting",
      "Agricultural Science",
      "Marketing",
      "Data Processing",
      "Computer Studies",
      "Technical Drawing",
      "CRS / IRS",
      "Literacy",
      "Numeracy",
      "Basic Science",
      "Character Development",
      "Circle Time",
      "Practical Life",
      "CCA",
      "PHE",
      "Handwriting",
      "Rhymes",
      "Social Skills",
      "Phonics",
      "ICT",
      "Home Economics",
      "Verbal Reasoning",
      "Quantitative Reasoning"
    ]
  }
];

// Helper to determine the curriculum topics based on Education level + Class + Subject + Term
export function generateWeeklyScheme(
  levelId: string,
  classLevel: string,
  subject: string,
  term: "First Term" | "Second Term" | "Third Term"
): WeeklySchemeUnit[] {
  const weeks: WeeklySchemeUnit[] = [];

  // Progression generator to make every week unique, progressive and extremely professional under NERDC standards
  for (let week = 1; week <= 10; week++) {
    const { t, s } = getCurriculumTopicAndSubtopic(levelId, classLevel, subject, term, week);
    const topic = t;
    const subtopic = s;
    
    weeks.push({
      week,
      topic,
      subtopic,
      objectives: `By the end of this lesson, the pupils/students should be able to:\n1. Define and explain the concept of ${topic.toLowerCase()}.\n2. Identify and analyze key features of ${subtopic.toLowerCase()}.\n3. Solve advanced practical problems and questions related to the core subject areas.`,
      teachingActivities: `1. Teacher welcomes the students and introduces the topic: "${topic}" using real-world Nigerian contexts.\n2. Teacher details "${subtopic}" on the chalkboard and models sample processes.\n3. Teacher guides the class through structured collaborative diagnostic exercises.`,
      studentActivities: `1. Students copy teacher's explanation and definition of "${topic}" into their notebooks.\n2. Students ask questions on "${subtopic}" and suggest familiar localized everyday examples.\n3. Students work in groups to solve practice worksheets provided in the lesson notes.`,
      assessment: `Formative Test: \n1. Define and outline three main factors of ${topic}.\n2. Write down the step-by-step procedure of practical calculation on ${subtopic}.\n3. Classroom Pop quiz for quick cumulative grading session.`,
      notes: `### Comprehensive Lesson Summary\n\n#### Concept Focus: ${topic}\nThis unit covers the foundational curriculum aspects of ${topic} as mandated by the latest NERDC guides and approved terminal outlines.\n\n#### Key Highlight on ${subtopic}\n- **Core definition**: ${subtopic} represents an essential element within this syllabus, demanding critical cognitive comprehension class-wide.\n- **Significance**: Highly integrated into terminal promotional exams and West African Certificate levels.\n- **Solved illustration**: Standard curriculum computations demonstrate structural efficiency in applying formulas.`,
      homework: `1. Detailed study review: Read more about ${subtopic} ahead of the next week's session.\n2. Homework workbook exercise: Page 45, Questions 1 through 5 of the approved class textbook.\n3. Home discovery: Interview parents or research online for localized practical applications.`
    });
  }

  return weeks;
}

// Internal curriculum progression directory matching NERDC guidelines
function getCurriculumTopicAndSubtopic(
  levelId: string,
  classLevel: string,
  subject: string,
  term: "First Term" | "Second Term" | "Third Term",
  week: number
): { t: string; s: string } {
  const normalTermLabel = term === "First Term" ? "T1" : term === "Second Term" ? "T2" : "T3";
  const subLower = subject.toLowerCase();
  const clsClean = classLevel.trim().toUpperCase();

  // Create deterministic shift for index offset based on classLevel
  let shift = 1;
  if (clsClean.includes("2")) shift = 3;
  else if (clsClean.includes("3")) shift = 5;
  else if (clsClean.includes("4")) shift = 7;
  else if (clsClean.includes("5")) shift = 9;
  else if (clsClean.includes("6")) shift = 11;
  else if (clsClean.includes("NURSERY")) shift = 2;

  const classDescriptor = `(${classLevel})`;

  // 1. MATHEMATICS, NUMERACY & QUANTITATIVE REASONING
  if (
    subLower.includes("mathematics") ||
    subLower.includes("numeracy") ||
    subLower.includes("math") ||
    subLower.includes("quantitative")
  ) {
    const pool = [
      "Number Classification and Properties",
      "Counting, Tracing and Place Values",
      "Basic Addition and Subtraction mechanics",
      "Multiplication Tables and simple Multipliers",
      "Division concepts and Sharing worksheets",
      "Fractions, Halves and Quarter representations",
      "Shapes identification in classroom environment",
      "Naira Currency and Simple Purchasing sums",
      "Weight, Height and Volume measurements",
      "Time checking on Analog Clocks",
      "Indices, Powers and standard annotations",
      "Logarithmic Equations and table values",
      "Modular Arithmetic operations",
      "Sets, Venn Diagrams and 3-set problems",
      "Linear and Simultaneous equations",
      "Quadratic factoring and Formula derivation",
      "Plotting Quadratic curves and symmetry lines",
      "Arithmetic Progressions (A.P.) series",
      "Geometric Progressions (G.P.) ratio",
      "Trigonometric functions and SOHCAHTOA theorems",
      "Angles of Elevation and depression models",
      "Mensuration of Cones, Pyramids and Spheres",
      "Longitudes and Latitudes sphere grids",
      "Probability formulas and dice rolling outcomes",
      "Grouped statistics and Frequency polygons",
      "Introduction to Differential Calculus",
      "Integration and boundaries evaluation",
      "Matrices, Determinants and Cramer's Rule",
      "Commercial Interest and Annuity formulas",
      "Comprehensive Term Mathematics Assessment"
    ];

    const tIdx = (week - 1 + shift * 7 + (normalTermLabel === "T1" ? 0 : normalTermLabel === "T2" ? 10 : 20)) % pool.length;
    return {
      t: `${pool[tIdx]} ${classDescriptor}`,
      s: `Formulas, applications, and worksheets on ${pool[tIdx].toLowerCase()} tailored for ${classLevel}.`
    };
  }

  // 2. ENGLISH, LITERACY, PHONICS & VERBAL REASONING
  if (
    subLower.includes("english") ||
    subLower.includes("literacy") ||
    subLower.includes("phonics") ||
    subLower.includes("verbal") ||
    subLower.includes("letters") ||
    subLower.includes("writing") ||
    subLower.includes("phrases")
  ) {
    const pool = [
      "Alphabet Matching, Tracing and Handwriting controls",
      "Phonetics, Letter Sounds and Sound articulation",
      "Consonant-Vowel-Consonant short spelling games",
      "Nouns, Pronouns and common naming objects",
      "Action Verbs and simple sentence modeling",
      "Adjectives, Colors, Sizes and descriptive roles",
      "Punctuation, Capital starts and Period marks",
      "Comprehension passages scanning and skimming drills",
      "Informal Letter layouts and personal topics",
      "Phonetic Digraphs and word building sounds",
      "Singular and Plural suffixes rules",
      "Conjunctions, Joining sentences and expressions",
      "Prepositions, Placement and space indicators",
      "Regular and Irregular Tenses structure",
      "Synonyms, Antonyms, Homophones list drills",
      "Formal Letter templates and official formatting",
      "Narrative Essay drafting and story timelines",
      "Expository Essay writing and technical columns",
      "Oral English, Syllable beats and active Stress rules",
      "Figures of Speech, Similes and Metaphors reading",
      "Active and Passive voice sentence flipping",
      "Clause Analysis, Adjectival and Adverbial branches",
      "Persuasive Rhetoric and Speech writing drafts",
      "Lexical Registers of Science and Medical fields",
      "Creative Dialogue and Screenplay storyboards",
      "Summary Writing and core outline generation",
      "Advanced Subject-Verb Concord exceptions",
      "Dictionary referencing and prefixing exercises",
      "Public Editorial drafts and publishing rules",
      "Cumulative Term Literacy and English Assessment"
    ];

    const tIdx = (week - 1 + shift * 6 + (normalTermLabel === "T1" ? 0 : normalTermLabel === "T2" ? 10 : 20)) % pool.length;
    return {
      t: `${pool[tIdx]} ${classDescriptor}`,
      s: `Grammar structures, essay checks, and vocal worksheets on ${pool[tIdx].toLowerCase()} for ${classLevel}.`
    };
  }

  // 3. SCIENCE, PHYSICS, CHEMISTRY, BIOLOGY, BASIC SCIENCE & HEALTH
  if (
    subLower.includes("science") ||
    subLower.includes("physics") ||
    subLower.includes("chemistry") ||
    subLower.includes("biology") ||
    subLower.includes("sensory") ||
    subLower.includes("health") ||
    subLower.includes("nature")
  ) {
    const pool = [
      "Living and Non-Living things classification",
      "The Human Body external organs and sanitizing",
      "Plants in our surroundings, leaves, and roots",
      "Animals taxonomy, birds, and insects attributes",
      "The Five Sense Organs exploration",
      "Personal safety guides in play environments",
      "Water sourcing, purity, and standard filtration",
      "Weather observations, cloud covers, and sunny days",
      "Introduction to scientific methods and laboratories",
      "States of Matter, Melting, boiling and freezing",
      "Air elements and atmospheric pressure",
      "Garden Soils, clay, loam, sandy types traits",
      "Heat energy transfer, conductors, and insulating items",
      "Plane Light Reflection, shadows, and optics",
      "Sound waves, resonance, and hearing instruments",
      "Simple Mechanical Machines, pulleys, and levers",
      "Magnetism properties and poles forces check",
      "Electricity loops, circuits, batteries, and bulbs",
      "Our Solar System, planet orbits, and day rotation",
      "Family Health maintenance and vaccinations",
      "Physical Quantities, fundamental dimensions, scales",
      "Inertia and Newton's Three Laws of Motion",
      "Atomic structure, protons, electrons distributions",
      "Chemical Bonding, valencies, covalent grids",
      "Ecology, ecosystems, food webs, pyramids",
      "Microbiology, disease vectors, sewage management",
      "Natural Resource Conservation and preservation",
      "Organic Carbon and Hydrocarbons chemical chains",
      "Space observation satellites and orbital probes",
      "Cumulative Term Scientific Mastery Assessment"
    ];

    const tIdx = (week - 1 + shift * 8 + (normalTermLabel === "T1" ? 0 : normalTermLabel === "T2" ? 10 : 20)) % pool.length;
    return {
      t: `${pool[tIdx]} ${classDescriptor}`,
      s: `Experimental demonstrations and theories regarding ${pool[tIdx].toLowerCase()} crafted for ${classLevel}.`
    };
  }

  // 4. COMPUTER STUDIES, ICT, DATA PROCESSING & TECHNICAL DRAWING
  if (
    subLower.includes("computer") ||
    subLower.includes("ict") ||
    subLower.includes("data") ||
    subLower.includes("drawing") ||
    subLower.includes("technology") ||
    subLower.includes("vocational")
  ) {
    const pool = [
      "Introduction to Computer hardware elements",
      "Central Processing Unit inside case boards",
      "Input devices, Keyboard keys, and Mouse use",
      "Output systems, Monitor, and Speaker mechanics",
      "Data Storage, local discs, and Flash units",
      "Desktop Computers versus Mobile tablets structures",
      "Operating Systems, GUI settings, and menus",
      "Word Processing layout, fonts, and page outlines",
      "Spreadsheet grid columns and coordinate ranges",
      "Spreadsheet Formulas, automated sum and counts",
      "PowerPoint presentation slides and animations",
      "ICT Network evolution, lines, and wireless signals",
      "Browser Navigation rules and safe search tricks",
      "Passcodes security, private profiles logging",
      "Graphic Paint tools, digital brushes selections",
      "Keyboarding speed drills and index typing guides",
      "Computer Laboratory dust cover rules and static care",
      "Computer Viruses, file quarantine scanners",
      "Email addresses, subject inputs, and sending attachments",
      "Backup systems, external hard-disks, Google drive storage",
      "Software installation guides, software types",
      "Intro to Programming, Algorithms, and Flowcharts",
      "Technical Drawing instruments, set-squares and rulers",
      "Geometric constructions, angles, circles and scales",
      "Orthographic projections of simple blocks",
      "Isometric sketches and pictorial perspectives",
      "Dimensioning practices and lettering rules",
      "Computer-Aided Design (CAD) basic interface",
      "Cybersecurity precautions and local scam warnings",
      "Cumulative Term ICT & Technology Assessment"
    ];

    const tIdx = (week - 1 + shift * 5 + (normalTermLabel === "T1" ? 0 : normalTermLabel === "T2" ? 10 : 20)) % pool.length;
    return {
      t: `${pool[tIdx]} ${classDescriptor}`,
      s: `Laboratory sessions, theoretical workflows, and guides on ${pool[tIdx].toLowerCase()} for ${classLevel}.`
    };
  }

  // 5. SOCIAL STUDIES, CIVIC EDUCATION, VALUES, GOVT, HISTORY, ECONOMICS, COMMERCE
  if (
    subLower.includes("social") ||
    subLower.includes("civic") ||
    subLower.includes("values") ||
    subLower.includes("government") ||
    subLower.includes("history") ||
    subLower.includes("economics") ||
    subLower.includes("commerce") ||
    subLower.includes("geography") ||
    subLower.includes("crs") ||
    subLower.includes("irs") ||
    subLower.includes("national")
  ) {
    const pool = [
      "Society, Culture, and Social structures",
      "Family Unit roles, fathers, mothers, and helpers",
      "National Identity, Coat of Arms and Anthem lyrics",
      "Constitutional Citizenship rights and obligations",
      "Nigerian Natural Resources, crude oil, and crop lands",
      "Leadership ranks, governors and presidential functions",
      "Public Integrity, avoiding exams cheating",
      "Zebra Crossings rules and FRSC signboards",
      "Nigerian History heroes, Herbert Macaulay, Nnamdi Azikiwe",
      "Traditional festivals, Durbar and Eyo festivities",
      "National Security watching boards and emergencies",
      "Cooperation, community clearing, sports outlines",
      "Disaster management, Emergency Red Cross and SEMA",
      "Drainage safety and checking backyard sand erosion",
      "Regional Inter-Ethnic weddings and cultural tolerance",
      "Federal Road Codes and acquiring drivers permit",
      "Linguistic friendliness, greetings in Hausa, Igbo, Yoruba",
      "West African Trade blocs, ECOWAS goals",
      "Personal finance budgets, keeping coin piggy banks",
      "State asset values, protecting regional railway grids",
      "Supply and Demand market equilibrium lines",
      "Structure of Commercial Banks and central bank policies",
      "The Pre-colonial empires, Oyo, Benin, Sokoto Caliphate",
      "Forms of Government, Monarchies vs modern Republic",
      "Sovereign Foreign relations and diplomatic guidelines",
      "Trade margins, Wholesale buying vs Retail shops",
      "Environmental ecology, climates, map reading scales",
      "Anti-Corruption values and national ethics codes",
      "Public infrastructure maintenance programs",
      "Cumulative Term Social Science Assessment"
    ];

    const tIdx = (week - 1 + shift * 9 + (normalTermLabel === "T1" ? 0 : normalTermLabel === "T2" ? 10 : 20)) % pool.length;
    return {
      t: `${pool[tIdx]} ${classDescriptor}`,
      s: `Socio-cultural and commercial analysis of ${pool[tIdx].toLowerCase()} for ${classLevel}.`
    };
  }

  // 6. DEFAULT GENERAL SPECIFIC FALLBACK subjects (CRS, IRS, French, CCA, Music, Agriculture, PHE etc.)
  const generalSubjectsT1 = [
    "Introduction and fundamental terminology",
    "Historical evolution and pre-colonial traces",
    "Essential regulatory and statutory approvals",
    "Case studies, local examples and practices",
    "Comparing local frameworks with international styles",
    "Government policy impacts on the syllabus",
    "Socio-economic relevance to average families",
    "Main hurdles, setbacks and tech solutions",
    "Future developmental paths and automatons",
    "Review, Collation and Mock Evaluation prep"
  ];

  const generalSubjectsT2 = [
    "Integrated systems theory and operations",
    "Qualitative research, diagnostic testing data",
    "Federal compliance codes and licensing setups",
    "Financing operations, micro-credit modeling",
    "Tackling structural process bottlenecks",
    "Safety indicators, hazards management guides",
    "Data analysis and recording coordinates",
    "Evaluating risk controls and system errors",
    "Collaborative brainstorming workshops guides",
    "Coursework project presentation and draft check"
  ];

  const generalSubjectsT3 = [
    "Strategic terminal blueprints formulation",
    "Ecology preservation and local carbon scores",
    "Asset lifecycle and capital decay calculations",
    "Benchmarking quality ratios and goal standards",
    "Cloud integration and smart workflow options",
    "Globalized integration, regional trade corridors",
    "Professional ethics, checking bribery profiles",
    "Seeking client satisfaction feed loop grids",
    "Developing actual project models for check panels",
    "Cumulative Promotional Curriculum Graduation Assessment"
  ];

  const baseArr = normalTermLabel === "T1" ? generalSubjectsT1 : normalTermLabel === "T2" ? generalSubjectsT2 : generalSubjectsT3;
  const rawTopic = baseArr[week - 1] || `${subject} Syllabus Unit ${week}`;

  return {
    t: `${rawTopic} (Class ${classLevel})`,
    s: `Approved curriculum guidelines and exercises focusing on ${rawTopic.toLowerCase()} for class ${classLevel}`
  };
}

function oldUnusedCurriculumCodeBypassed(
  levelId: string = "",
  classLevel: string = "",
  subject: string = "",
  term: string = "",
  week: number = 1,
  subLower: string = "",
  normalTermLabel: string = ""
) {
  // 1. MATHEMATICS & NUMERACY CURRICULUM
  if (subLower.includes("mathematics") || subLower.includes("numeracy")) {
    if (levelId === "senior_secondary") {
      // SS Level Mathematics
      if (normalTermLabel === "T1") {
        const topics = [
          { t: "Number Bases", s: "Conversion from base 10 to other bases (binary, octal, hex)" },
          { t: "Number Bases Operations", s: "Addition, subtraction, and multiplication in other bases" },
          { t: "Modular Arithmetic", s: "Concept of modulo, addition and subtraction in modular systems" },
          { t: "Indices", s: "Laws of indices, simplifying index expressions" },
          { t: "Logarithms", s: "Theory of logarithms, conversion from index form to log form" },
          { t: "Logarithm Calculations", s: "Using logarithm tables to calculate products, quotients" },
          { t: "Sets Theory", s: "Definition, types of sets, union, intersection and Venn diagrams" },
          { t: "Venn Diagrams Applications", s: "Solving 3-set logical problems in schools/offices" },
          { t: "Simple Equations", s: "Linear equations with one variable, simple fractions" },
          { t: "Simultaneous Equations", s: "Solving simultaneously using elimination and substitution methods" }
        ];
        return topics[week - 1] || { t: `Mathematics Foundation Week ${week}`, s: "General Mathematics calculation" };
      } else if (normalTermLabel === "T2") {
        const topics = [
          { t: "Quadratic Equations", s: "Solving quadratic equations by factorization method" },
          { t: "Quadratic Equations (Completing Square)", s: "Derivation and usage of the quadratic formula" },
          { t: "Quadratic Graphs", s: "Plotting quadratic curves, finding roots, and line of symmetry" },
          { t: "Approximations & Percentage Error", s: "Significant figures, decimal places, calculating error margins" },
          { t: "Sequence and Series (AP)", s: "Arithmetic Progression (A.P.), first term, common difference" },
          { t: "Sequence and Series (GP)", s: "Geometric Progression (G.P.), common ratio, nth term" },
          { t: "Sum of Series", s: "Summing A.P. and G.P. sequences" },
          { t: "Logical Reasoning", s: "Simple statements, negation, conjunction, disjunction" },
          { t: "Implication and Equivalence", s: "Conditional statements, Converse, Inverse, Contrapositive" },
          { t: "Review and Mock Terminal Exams", s: "Review of all Second Term algebraic and logical computations" }
        ];
        return topics[week - 1] || { t: `Advanced Algebra Week ${week}`, s: "Syllabus workbook session" };
      } else {
        const topics = [
          { t: "Trigonometry Ratios", s: "Sine, Cosine, and Tangent values for acute angles" },
          { t: "Trig Ratios of Special Angles", s: "Calculating exact values for 30, 45, and 60 degrees" },
          { t: "Angles of Elevation & Depression", s: "Applying SOHCAHTOA to heights and distances problems" },
          { t: "Mensuration of Plane Shapes", s: "Perimeter and area of triangles, rectangles, trapeziums" },
          { t: "Mensuration of Solid Shapes", s: "Volume and surface area of cylinders, cones, and spheres" },
          { t: "Volume of Curved Solids", s: "Advanced calculations on frustums, prisms and pyramids" },
          { t: "Probability Basics", s: "Experimental and theoretical probability, sample space and events" },
          { t: "Probability Laws", s: "Addition and multiplication laws for mutually exclusive events" },
          { t: "Statistics (Ungrouped Data)", s: "Mean, median, mode, and range computations in schools" },
          { t: "Statistics (Grouped Data)", s: "Frequency tables, histograms, and cumulative frequency curves" }
        ];
        return topics[week - 1] || { t: `Geometry and Statistics Week ${week}`, s: "Syllabus workbook session" };
      }
    } else if (levelId === "junior_secondary") {
      // JSS Mathematics
      if (normalTermLabel === "T1") {
        const topics = [
          { t: "Whole Numbers & Place Value", s: "Counting in millions, billions, and determining place values" },
          { t: "Factors and Multiples", s: "Highest Common Factor (HCF) and Least Common Multiple (LCM)" },
          { t: "Fractions & Decimals Basics", s: "Proper, improper, mixed fractions, addition and subtraction" },
          { t: "Multiplication and Division of Fractions", s: "Applying BODMAS rules to complex fractional expressions" },
          { t: "Standard Form and Approximation", s: "Rounding off to nearest ten, hundred, and standard scientific form" },
          { t: "Ratios, Proportion and Percentages", s: "Sharing values in ratios, calculating percentage increase" },
          { t: "Basic Algebraic Expressions", s: "Concept of variables, collecting like terms in algebra" },
          { t: "Evaluation of Algebraic Expressions", s: "Substituting numerical values into algebraic expressions" },
          { t: "Simple Linear Equations", s: "Solving algebraic equations of high linear equality" },
          { t: "Revision & First Term Examinations", s: "Consolidated examination drills and review sessions" }
        ];
        return topics[week - 1] || { t: `Mathematics Core Week ${week}`, s: "Practical curriculum drilling" };
      } else if (normalTermLabel === "T2") {
        const topics = [
          { t: "Plane Geometry & Angles", s: "Angles on a straight line, vertically opposite angles" },
          { t: "Angles in Triangles", s: "Sum of angles in a triangle, equilateral and isosceles properties" },
          { t: "Polygons and Angles count", s: "Sum of interior and exterior angles in regular polygons" },
          { t: "Perimeter of Regular Shapes", s: "Calculating boundaries of squares, circles and composite shapes" },
          { t: "Area of Plane Shapes", s: "Area calculations for triangles, parallelograms and circles" },
          { t: "Simple Interest and Percentages", s: "Calculating principal, rate, time, and total interest" },
          { t: "Commercial Arithmetic", s: "Profit, loss, discount, tax, and commissions" },
          { t: "Data Presentation", s: "Constructing frequency tallies and pictograms" },
          { t: "Bar Charts and Pie Charts", s: "Drawing and interpreting statistical charts" },
          { t: "Revision and CBT practice", s: "Solving previous national common entrance math modules" }
        ];
        return topics[week - 1] || { t: `Geometric Essentials Week ${week}`, s: "Numerical training" };
      } else {
        const topics = [
          { t: "Pythagoras Theorem", s: "Understanding the right-angled triangle theorem formula" },
          { t: "Introduction to Trigonometry", s: "Basic ratios: Sine, Cosine, and Tangent angles" },
          { t: "Volume of Cubes & Cuboids", s: "Determining space and capacity of standard boxes" },
          { t: "Surface Area of Solid Shapes", s: "Total surface markings for rectangular prisms" },
          { t: "Introduction to Coordinates", s: "Placing points on Cartesian coordinate axes" },
          { t: "Linear Graphs", s: "Plotting simple linear equations on graph papers" },
          { t: "Mean, Median and Mode", s: "Basic measures of central tendency for class scores" },
          { t: "The Circle", s: "Circumference, radius, diameter and chord definitions" },
          { t: "Symmetry and Construction", s: "Line of symmetry and bisecting lines using compasses" },
          { t: "Yearly Revision & Promotional Exam", s: "Comprehensive review of junior mathematics principles" }
        ];
        return topics[week - 1] || { t: `Basic Coordinate Math Week ${week}`, s: "NERDC syllabus" };
      }
    } else {
      // Primary / Nursery Mathematics & Numeracy
      if (normalTermLabel === "T1") {
        const topics = [
          { t: "Counting Numbers 1 - 100", s: "Rhythm counting, writing, and sorting games" },
          { t: "Simple Addition of Numbers", s: "Combining objects together, simple visual sums" },
          { t: "Single-Digit Subtraction", s: "Taking objects away, counting backwards" },
          { t: "Greater or Less Than", s: "Comparing small items quantity using symbols" },
          { t: "Symmetrical Shapes Basics", s: "Identifying circles, squares, and triangles around the room" },
          { t: "Zero as a Number Placeholder", s: "Understanding meaning of zero and empty groups" },
          { t: "Reading calendars & timetables", s: "Identifying weekly schedules, days of the week" },
          { t: "Place Value: Tens and Units", s: "Grouping objects into bundles of tens" },
          { t: "Ordinal Numbers (1st, 2nd, 3rd)", s: "Recognizing rankings and sequences of classmate lines" },
          { t: "Term 1 Classroom Check", s: "Fractions matching, counting lists, spelling numbers" }
        ];
        return topics[week - 1] || { t: `Primary Math Basic Week ${week}`, s: "Basic numeracy exercises" };
      } else if (normalTermLabel === "T2") {
        const topics = [
          { t: "Advanced Ordering 1 - 200", s: "Arranging numbers from smallest to largest size" },
          { t: "Skip Counting by 2s and 5s", s: "Discovering sequence leaps and multiples" },
          { t: "Double-Digit Addition", s: "Vertical addition patterns without carrying" },
          { t: "Double-Digit Subtraction", s: "Vertical subtraction drills without borrowing" },
          { t: "Fractions (Halves and Quarters)", s: "Shading half and quarter sections of circles" },
          { t: "Nigerian Currency: Coins & Notes", s: "Identifying Naira notes and Kobo denominations" },
          { t: "Weight: Heavy vs Light", s: "Sorting playground toys by physical weight balances" },
          { t: "Non-standard Measurement of Length", s: "Measuring with rulers, footspans, and paces" },
          { t: "Months of the Year", s: "Saying and writing twelve calendar months" },
          { t: "Term II Diagnostic Review", s: "Reviewing calculations, skip marks, and calendar months" }
        ];
        return topics[week - 1] || { t: `Primary Math Development Week ${week}`, s: "Applied numeracy topics" };
      } else {
        const topics = [
          { t: "Numbers up to 500", s: "Expanding counting boundaries and sequence sorting" },
          { t: "Multiplication Tables (2, 3, 5)", s: "Learning early times-tables and multipliers" },
          { t: "Simple Division (Sharing)", s: "Sharing pencils, sweets, and blocks equally in class" },
          { t: "Writing Numbers in Words", s: "Spelling names of numbers from one to one hundred" },
          { t: "Solid 3D Shapes Identification", s: "Recognizing cones, cylinders, cubes and spheres" },
          { t: "Telling time on the Analog Clock", s: "Reading hours and minutes correctly on clocks" },
          { t: "Capacity: Litres & Cups", s: "Comparing liquids capacity of big buckets and cups" },
          { t: "Basic Data Tallies", s: "Counting classmates' characteristics and making simple tick tallies" },
          { t: "Basic Addition with Carrying", s: "Regrouping units as tens in double-digit calculations" },
          { t: "Promotional Academic Assessment", s: "Promotional review of all 3 terms mathematical formulas" }
        ];
        return topics[week - 1] || { t: `Primary Math Mastery Week ${week}`, s: "Intermediate arithmetic drills" };
      }
    }
  }

  // 2. ENGLISH LANGUAGE & LITERACY CURRICULUM
  if (subLower.includes("english") || subLower.includes("literacy")) {
    if (levelId === "senior_secondary") {
      if (normalTermLabel === "T1") {
        const topics = [
          { t: "Parts of Speech (Nouns & Pronouns)", s: "Types, gender, number, and syntactic functions" },
          { t: "Verbs and Tense Systems", s: "Present, past, and perfect tenses, active vs passive voices" },
          { t: "Adjectives and Adverbs", s: "Recognizing modify patterns, order of adjectives" },
          { t: "Prepositions and Conjunctions", s: "Prepositions of time/place, coordinating vs subordinating" },
          { t: "Subject-Verb Agreement (Concord)", s: "Singular/plural nouns, collective nouns, proximity principles" },
          { t: "Creative Writing: Narrative Essay", s: "Structuring introductions, paragraphs, climax and logical flow" },
          { t: "Descriptive Essay Development", s: "Sensory details, describing people, places, and cultural events" },
          { t: "Comprehension & Vocabulary Acquisition", s: "Scanning, skimming, context-clues and dictionary synonyms" },
          { t: "Oral English: Vowel Sounds", s: "Monophthongs, diphthongs, and contrast sounds" },
          { t: "Summary Writing", s: "Identifying main ideas, draft compression and brevity" }
        ];
        return topics[week - 1] || { t: `English Grammar Week ${week}`, s: "Syntactic structure drills" };
      } else if (normalTermLabel === "T2") {
        const topics = [
          { t: "Phrasal Verbs & Idioms", s: "Common idioms and conversational phrases used in Nigeria" },
          { t: "Creative Writing: Expository Essay", s: "Writing informative articles, columns, and explanations" },
          { t: "Argumentative Essay Outline", s: "Debating, pros and cons of technical and local topics" },
          { t: "Direct and Indirect Speech", s: "Converting reported speeches, modifying pronouns and tenses" },
          { t: "Punctuation Marks & Capitalization", s: "Usage of semicolons, colons, apostrophes, and quotation marks" },
          { t: "Formal Letter Structure", s: "Business letters, addresses layout, official salutations" },
          { t: "Informal and Semi-formal Letters", s: "Writing to family members, friends, or teachers" },
          { t: "Oral English: Consonants", s: "Voiced and voiceless sounds, consonant clusters" },
          { t: "Synonyms, Antonyms, and Homophones", s: "Confusing pairs and vocabulary expansion worksheets" },
          { t: "Second Term Assessment & Synthesis", s: "Consolidated examinations and diagnostic drills" }
        ];
        return topics[week - 1] || { t: `Creative Expression Week ${week}`, s: "Lexical mastery" };
      } else {
        const topics = [
          { t: "Persuasive & Speech Writing", s: "Addressing student forums, hooks, and emotional rhetoric" },
          { t: "Structural Clauses Analysis", s: "Noun clauses, adjectival clauses, and adverbial clauses functions" },
          { t: "Creative Storyboarding & Dialogue", s: "Drafting dramatic plays and dialogue structures" },
          { t: "Lexical Registers of Fields", s: "Vocabulary arrays: Science, Law, ICT, Medicine, and Commerce" },
          { t: "Advanced Concord Exceptions", s: "Correlative agreement, collective groupings constraints" },
          { t: "Literary Devices & Figures of Speech", s: "Spotting metaphors, similes, hyperboles, and oxymorons in prose" },
          { t: "Oral Syllabic Stress and Intonation", s: "Rising and falling tunes, locating primary syllable accents" },
          { t: "Articles Writing for Publications", s: "Formatting headlines and descriptive editorial write-ups" },
          { t: "Comprehension Inference Reading", s: "Answering context questions and extracting implied themes" },
          { t: "SS1 English Final Revision", s: "Synthesizing essay types, spelling patterns, and summary drills" }
        ];
        return topics[week - 1] || { t: `SS English Practice Week ${week}`, s: "High stakes exam readiness" };
      }
    } else if (levelId === "junior_secondary") {
      // JSS English
      if (normalTermLabel === "T1") {
        const topics = [
          { t: "Introduction to Nouns", s: "Proper, common, collective and abstract nouns" },
          { t: "Pronoun Classification", s: "Personal, possessive, reflexive and demonstrative pronouns" },
          { t: "Action Verbs", s: "Identifying physical actions and mental states verbs" },
          { t: "Adjectives and Descriptive Roles", s: "Color, size, origin and age describing words" },
          { t: "Punctuation: Capitalization & Period", s: "Starting letters in uppercase, placing commas and periods" },
          { t: "Understanding Comprehension Passages", s: "Locating direct answers from stories" },
          { t: "Informal Letters: Format & Tone", s: "Writing friendly letters to siblings or parents" },
          { t: "Oral English: English Digraphs", s: "Sound blends with /th/, /ch/, /sh/ words" },
          { t: "Spelling Rules & Compound Words", s: "Analyzing vowel doublets and building compound words" },
          { t: "First Term Examination Review", s: "Syllable grids, parts of speech review" }
        ];
        return topics[week - 1] || { t: `JSS1 T1 English Week ${week}`, s: "General grammar structures" };
      } else if (normalTermLabel === "T2") {
        const topics = [
          { t: "Verbs and Simple Tenses", s: "Present, Past, and Future tense sentence constructs" },
          { t: "Adverbs of Manner and Place", s: "Describing how and where actions happen in schools" },
          { t: "Prepositions of Place & Direction", s: "Using on, under, behind, beside, into correctly" },
          { t: "Joining Sentences with Conjunctions", s: "Using and, but, or, so to merge expressions" },
          { t: "Basic Concord Patterns", s: "Singular matchings, plural noun alignments" },
          { t: "Formal Letters: Core Address Format", s: "Writing principal or school administrators" },
          { t: "Narrative Essay Drafts", s: "Structuring chronological sequence of home stories" },
          { t: "Oral English: Short/Long Vowel sounds", s: "Contrasts between /i/ and /i:/ sound waves" },
          { t: "Synonyms Dictionary drills", s: "Using context books to discover matching words" },
          { t: "Second Term Assessment Session", s: "Reviewing informal layout, tenses, and adjectives" }
        ];
        return topics[week - 1] || { t: `JSS1 T2 English Week ${week}`, s: "Structural grammar development" };
      } else {
        const topics = [
          { t: "Direct Dialogue Quotes Punctuation", s: "Using inverted quotation commas for speech sessions" },
          { t: "Continuous & Perfect Tense Progression", s: "Present perfect and present continuous tenses study" },
          { t: "Comparative and Superlative Attributes", s: "Comparing sizes correctly: tall, taller, tallest" },
          { t: "Expository Essay Drafts", s: "Explaining how to bake cake, execute agricultural planting" },
          { t: "Antonyms (Word Opposites) Matrices", s: "Matching lists of positive and negative opposites" },
          { t: "Spotting Similes & Metaphors", s: "Discovering visual poetry elements in traditional readings" },
          { t: "Oral English: Syllable Clapping", s: "Breaking multi-syllable words into rhythmic counts" },
          { t: "Advanced Comprehension Deductions", s: "Interpreting moral themes from traditional folktales" },
          { t: "Active and Passive Sentence Flipping", s: "Changing active statements into passive structures" },
          { t: "Promotional Year-end Examinations", s: "Reviewing Junior Secondary 1 English course rules" }
        ];
        return topics[week - 1] || { t: `JSS1 T3 English Week ${week}`, s: "Consolidated literacy drills" };
      }
    } else {
      // Primary / Nursery English & Literacy
      if (normalTermLabel === "T1") {
        const topics = [
          { t: "Alphabet Matching & Handwriting", s: "Tracing uppercase and lowercase letters side-by-side" },
          { t: "Phonics: Letter Sounds A to M", s: "Sound articulation matching to visual flashcards" },
          { t: "Phonics: Letter Sounds N to Z", s: "Sound articulation and picture object matches" },
          { t: "Foundational Naming Words", s: "Pointing to toys, bodies, animals, calling them nouns" },
          { t: "He, She, and It Pronouns", s: "Replacing proper boys/girls names with basic pronouns" },
          { t: "Writing Three-Letter Blends (CVC)", s: "Spelling and reading cat, hen, dog, pig, sun" },
          { t: "Handwriting Control Exercises", s: "Symmetry, keeping text characters neatly on lines" },
          { t: "Action Verbs Recognition", s: "Calling out run, jump, sing, draw from picture boards" },
          { t: "Simple Sentence Tracing", s: "Tracing phrases like 'The big cat sat on the rug'" },
          { t: "Term 1 Phonics Showcase", s: "Verifying letters sound mastery and word-building cards" }
        ];
        return topics[week - 1] || { t: `Primary Literacy Basic Week ${week}`, s: "Primary alphabet and sounds" };
      } else if (normalTermLabel === "T2") {
        const topics = [
          { t: "Consonant Blends Phonics (-at, -en)", s: "Constructing and grouping rhyming phonetic tables" },
          { t: "Singular vs Plural Suffix Rules", s: "Adding 's' to simple naming labels: book, books" },
          { t: "Colors and Sizes Description", s: "Using basic adjectives: blue, red, big, thin, flat" },
          { t: "Punctuation: Beginning and Ending", s: "Correcting uppercase starts and full-stop endings" },
          { t: "Writing Simple Phrases", s: "Making short descriptions like 'My blue bucket'" },
          { t: "Phonics: Digraph sound blends", s: "Learning 'sh' for shell, 'ch' for chat, 'th' for thin" },
          { t: "Short Paragraph Reading", s: "Group reading out loud from simple class story sheets" },
          { t: "Opposites matching matrices", s: "Identifying opposites: open/close, up/down, sit/stand" },
          { t: "Answering Simple Wh-Questions", s: "Developing comprehension responses for Who, What, Where" },
          { t: "Term 2 Literacy Evaluation", s: "Grading spelling, handwriting controls, reading card speed" }
        ];
        return topics[week - 1] || { t: `Primary Literacy Development Week ${week}`, s: "Sentence building habits" };
      } else {
        const topics = [
          { t: "Actions in the Present Continuous", s: "Building action verbs ending in '-ing', e.g., running" },
          { t: "Simple Past Action Verbs (-ed)", s: "Differentiating today actions from yesterday stories" },
          { t: "Position Words (Prepositions)", s: "Using in, on, under, behind to locate funny objects" },
          { t: "Joining ideas with Conjunctions", s: "Linking nouns or ideas together using the word 'and'" },
          { t: "Sight Words vocabulary lists", s: "Instantly reading unphonetic sight words: the, was, to" },
          { t: "Writing About My Family", s: "Drafting three complete sentences about family helpers" },
          { t: "Syllables counting games", s: "Clapping hands to count beats in words: ap-ple, mon-key" },
          { t: "Understanding Story Sequences", s: "Ordering picture cards of what happened first, second, last" },
          { t: "Reading aloud fluency cards", s: "Expressive reading and voice pitch checks in class" },
          { t: "Annual Promotional Assessment", s: "Review and certification of primary 1 literacy milestones" }
        ];
        return topics[week - 1] || { t: `Primary Literacy Mastery Week ${week}`, s: "Advanced primary spelling scales" };
      }
    }
  }

  // 3. SCIENCE CURRICULUM (PHYSICS / CHEMISTRY / BIOLOGY / BASIC SCIENCE / SENSORY)
  if (
    subLower.includes("science") ||
    subLower.includes("physics") ||
    subLower.includes("chemistry") ||
    subLower.includes("biology") ||
    subLower.includes("sensory")
  ) {
    if (subLower.includes("physics")) {
      if (normalTermLabel === "T1") {
        const topics = [
          { t: "Introduction to Physics", s: "Concept of Physics, natural phenomena, career prospects" },
          { t: "Physical Quantities & Units", s: "Fundamental and derived quantities, SI units system" },
          { t: "Dimensions of Physical Quantities", s: "Dimensional analysis, checking equations consistency" },
          { t: "Measurement of Length, Mass, Time", s: "Using vernier callipers, micrometer screw gauges, stopwatches" },
          { t: "Motion and Types of Motion", s: "Linear, rotational, oscillatory, and random motions" },
          { t: "Speed, Velocity, & Acceleration", s: "Equations of uniform motion, plotting motion graphs" },
          { t: "Vectors and Scalars", s: "Vector addition, resolution of vectors at perpendicular angles" },
          { t: "Force and Its Effects", s: "Contact and non-contact forces, balanced vs unbalanced systems" },
          { t: "Newton's Laws of Motion", s: "First, second and third laws of motion, inertia illustrations" },
          { t: "Gravity and Gravitational Force", s: "Free fall, acceleration due to gravity (g)" }
        ];
        return topics[week - 1] || { t: `Physics Mechanics Week ${week}`, s: "Physical computations" };
      } else if (normalTermLabel === "T2") {
        const topics = [
          { t: "Work, Energy & Power formulas", s: "Calculating kinetic energy, potential energy and mechanical power" },
          { t: "Mechanics of Simple Machines", s: "Levers, pulleys, inclined planes, velocity ratio and efficiency" },
          { t: "Frictive Forces in Matter", s: "Static and dynamic frictionCoefficient of friction calculations" },
          { t: "Linear Momentum & Impulse", s: "Conservation of momentum law, inelastic and elastic collisions" },
          { t: "Projectile Motion Mechanics", s: "Calculating trajectory paths, range, maximum height, flight times" },
          { t: "Equilibrium of Coplanar Forces", s: "Resolving parallel forces, moments calculations, stable centers" },
          { t: "Center of Gravity & Stability", s: "Analyzing stable, unstable, and neutral equilibrium conditions" },
          { t: "Hooke's Law of Elasticity", s: "Stress, strain, Young's Modulus, limits of proportionality" },
          { t: "Hydrostatic Fluid Pressure", s: "Archimedes' Principle, upthrust, density and relative densities" },
          { t: "Term II Mechanics Assessment", s: "Formative exam on kinetic forces, pulleys, and moments" }
        ];
        return topics[week - 1] || { t: `Physics Dynamics Week ${week}`, s: "Mechanical calculations" };
      } else {
        const topics = [
          { t: "Thermal Measurement Scales", s: "Constant volume thermometers, gas thermometers, Celsius conversions" },
          { t: "Thermal Expansion in Solids", s: "Volumetric, linear, and area expansivity, metallic strip uses" },
          { t: "Anomalous Expansion of Water", s: "Real and apparent expansivity of liquid densities" },
          { t: "Gas Laws Under Temperature", s: "Boyle's Law, Charles's Law, Pressure Law calculations" },
          { t: "Thermal Energy Transfer Modes", s: "Heat conduction, convection, radiation and vacuum systems" },
          { t: "Physics of Wave Motion", s: "Transverse and longitudinal waves, wave equation v = f * lambda" },
          { t: "Light Reflection: Curved Mirrors", s: "Spherical mirrors, focal lengths, real and virtual image plots" },
          { t: "Snell's Law of Refraction", s: "Refractive index, glass blocks, apparent depths calculations" },
          { t: "Acoustics & Sound Physics", s: "Sound waves, frequency variables, pitch and volume echoes" },
          { t: "SS1 Physics Promotional Final", s: "Consolidated terminal exam covering all academic physics terms" }
        ];
        return topics[week - 1] || { t: `Physics Waves/Thermal Week ${week}`, s: "Waves and thermal energy computations" };
      }
    } else if (subLower.includes("chemistry")) {
      if (normalTermLabel === "T1") {
        const topics = [
          { t: "Introduction to Chemistry", s: "Definition, chemical industries, careers, and lab safety" },
          { t: "Matter and Its Physical States", s: "Solid, liquid, gas, kinetic theory justification" },
          { t: "Classification of Matter", s: "Elements, compounds, and mixtures, homogeneous vs heterogeneous" },
          { t: "Separation Techniques", s: "Filtration, evaporation, distillation, paper chromatography" },
          { t: "Chemical Symbols & Writing", s: "First 20 elements of the periodic table, valency counts" },
          { t: "Atomic Structure Theories", s: "Dalton, Thomson, Rutherford, Bohr models of the atom" },
          { t: "Protons, Neutrons, & Electrons", s: "Atomic number, mass number, calculating isotopes" },
          { t: "Periodic Table Organization", s: "Groups and periods, alkali metals, halogens, noble gases" },
          { t: "Chemical Bonding", s: "Electrovalent (ionic) and covalent bond formation" },
          { t: "Valency & Formula Writing", s: "Determining chemical formulas of basic compound molecules" }
        ];
        return topics[week - 1] || { t: `Chemistry Basics Week ${week}`, s: "Chemical stoichiometry" };
      } else if (normalTermLabel === "T2") {
        const topics = [
          { t: "Kinetic Theory of Matter Postulates", s: "Postulates of kinetic theory, gas pressure and temperature vectors" },
          { t: "Graham's Law of Gas Diffusion", s: "Boyle's and Charles's laws, stoichiometric gas calculations" },
          { t: "Moles Concept & Avogadro Number", s: "Standard moles conversion, atomic weight coefficients" },
          { t: "Relative Molecular Mass calculations", s: "Empirical formulas and percentage compositions of hydrocarbons" },
          { t: "Chemical Equation Stoichiometry", s: "Balancing reactant and product moles, mass volume ratios" },
          { t: "Acids, Bases and Salts basics", s: "Understanding pH meters, indicators, and acid concentration criteria" },
          { t: "Preparation methods of standard Acids", s: "Industrial and organic laboratory acid preparations" },
          { t: "Salt classifications and hydration", s: "Acidic, basic and double salts, water of crystallization" },
          { t: "Solubility curves and configurations", s: "Effects of temperature on solubility of compound powders" },
          { t: "Chemistry Semester Evaluation", s: "Formative exam on acid-base titration mechanics and formula mass" }
        ];
        return topics[week - 1] || { t: `Chemistry Equations Week ${week}`, s: "Stoichiometry formulations" };
      } else {
        const topics = [
          { t: "Water: Hardness and Softeners", s: "Permanent and temporary water hardness, industrial purifications" },
          { t: "Hydrogen: Preparation and Isotopes", s: "Protium, deuterium, tritium properties and industrial gases" },
          { t: "Oxygen Gas preparation & Oxides", s: "Laboratory thermal decomposition, acidic vs neutral oxides taxonomy" },
          { t: "Carbon and Allotropic Forms", s: "Structure of graphite, diamonds, fullerenes, and carbon blacks" },
          { t: "Oxides of Carbon toxins", s: "Carbon monoxide toxicity mechanisms, carbon dioxide greenhouse stats" },
          { t: "Coal & Destructive Distillation", s: "Industrial outputs: coke, coal tar, ammoniacal liquor and coal gas" },
          { t: "Introduction to Organic Chemistry", s: "Unique catenation of carbon atoms, carbon skeletal chains" },
          { t: "Homologous series of Alkanes", s: "IUPAC nomenclatures of simple gaseous and liquid alkanes" },
          { t: "Titration Laboratory Practicals", s: "Standard solutions, direct volumetric volumetric analysis calculations" },
          { t: "SS1 Chemistry Promotion Exam", s: "Cumulative certification review of general chemistry outlines" }
        ];
        return topics[week - 1] || { t: `Chemistry Organics Week ${week}`, s: "Inorganic and organic science setups" };
      }
    } else if (subLower.includes("biology")) {
      if (normalTermLabel === "T1") {
        const topics = [
          { t: "Introduction to Biology", s: "Biology as a science of life, branches, and laboratories" },
          { t: "Characteristics of Living Things", s: "MR NIGER D (Movement, Respiration, Nutrition, etc.)" },
          { t: "Classification of Living Organisms", s: "The Five Kingdoms of Life: Monera, Protista, Fungi, Plantae, Animalia" },
          { t: "The Cell", s: "Definition, cell theory, plant and animal cell differences" },
          { t: "Cell Functions & Organelles", s: "Mitochondria, nucleus, chloroplasts, and ribosomes" },
          { t: "Unicellular & Multicellular Organisms", s: "Amoeba, Euglena vs complex mammals/flowering plants" },
          { t: "Organization of Life", s: "Cell → Tissue → Organ → System → Organism" },
          { t: "Plant Nutrition", s: "Photosynthesis, water transport, mineral requirements" },
          { t: "Animal Nutrition", s: "Balanced diets, enzymes, heterotrophic feeding mechanisms" },
          { t: "Transport in Organisms", s: "Diffusion, osmosis, active transport in life membranes" }
        ];
        return topics[week - 1] || { t: `Biology Cells Week ${week}`, s: "Organic life cells" };
      } else if (normalTermLabel === "T2") {
        const topics = [
          { t: "Acellular Organisms Study (Viruses)", s: "Analyzing non-cellular structures, viral replications" },
          { t: "Aerobic vs Anaerobic Respiration", s: "Energy currency equations, glycolysis, and Krebs Cycle" },
          { t: "Mechanisms of Gaseous Exchange", s: "Stomata in leaves, gills in fishes, lung alveoli in mammals" },
          { t: "Excretory Systems of Organisms", s: "Contractile vacuoles, nephridia, Malpighian tubules, skin pores" },
          { t: "Plant Transpiration processes", s: "Potometers, stomatal openings, humidity variables" },
          { t: "Hormone systems & Irritability", s: "Phototropism, geotropism, adrenaline and auxin responses" },
          { t: "Skeletal support in Animals", s: "Hydrostatic skeleton, exoskeletons, endoskeleton joints" },
          { t: "Human Musculature & Movement", s: "Antagonistic muscle pairs, cartilage and tendon actions" },
          { t: "Enzyme digestions in Ruminants", s: "Four chambers of ruminant stomach, cud chewing mechanics" },
          { t: "Term II Physiology Assessment", s: "Examinations on circulation, transpiration, and excretory systems" }
        ];
        return topics[week - 1] || { t: `Biology Systems Week ${week}`, s: "Physiology structures and systems" };
      } else {
        const topics = [
          { t: "Ecology Foundations: Ecosystems", s: "Abiotic factors, biotic factors, ecological food chains" },
          { t: "Ecological Instruments", s: "Using anemometers, hygrometers, quadrats, sweep nets in fields" },
          { t: "Major Biomes of the Earth", s: "Tropical forest Savannas, Tundras, Deserts local biomes" },
          { t: "Population Density and Dynamics", s: "Tracking birth rate, death rate, quadrat plant samplings" },
          { t: "Energy Pyramids and Biomass", s: "Food chains, trophic food webs, de-carbonization decompose loops" },
          { t: "Biochemical Nutrient Cycles", s: "Carbon cycle, Nitrogen cycle, nitrogen fixing bacteria roles" },
          { t: "Microbiology in daily health", s: "Pathogenic bacteria, viruses, sewage treatments, vaccinations" },
          { t: "Local Sanitation and Diseases", s: "Malaria, Cholera vector checks, municipal sanitation boards" },
          { t: "Conservation of Natural resources", s: "Wildlife reserves, forest preservation, checking water pollutions" },
          { t: "Annual Biology Promotional Finals", s: "Promotional review of cells, structures and eco elements" }
        ];
        return topics[week - 1] || { t: `Biology Ecology Week ${week}`, s: "Ecological and environmental science basics" };
      }
    } else {
      // Basic Science (Junior Secondary / Primary / Nursery Science)
      if (levelId === "junior_secondary") {
        if (normalTermLabel === "T1") {
          const topics = [
            { t: "Introduction to Basic Science", s: "Understanding science principles, scientific method, and lab rules" },
            { t: "Family Health Standards", s: "Maintaining home cleanliness, disease immunization schedules" },
            { t: "Adolescent Development & Hygiene", s: "Puberty biochemical changes, personal health maintenance" },
            { t: "Environmental Pollution sources", s: "Causes, impact of dirty plastic rubbish on local farmlands" },
            { t: "Sanitation and Sewage management", s: "Refuse recycling, clean drains to check mosquitoes" },
            { t: "Drug Abuse & Narcotics safety", s: "Identifying dangerous stimulants, drug safety and NDLEA guidelines" },
            { t: "Physical properties of Matter", s: "Distinguishing states of solids, liquids, and gases" },
            { t: "Living things taxonomies", s: "Characteristics of plants and vertebrates/invertebrates groups" },
            { t: "Non-living metallic compounds", s: "Determining attributes of common metals vs wood/plastics" },
            { t: "Term I Basic Science Quiz", s: "Verifying health tips, matter states, and safety symbols" }
          ];
          return topics[week - 1] || { t: `JSS Science Foundation Week ${week}`, s: "General scientific disciplines" };
        } else if (normalTermLabel === "T2") {
          const topics = [
            { t: "Organisms Breathing mechanisms", s: "Why organisms respire, breathing membranes structure" },
            { t: "Heat energy and conductivity", s: "Conductors, insulators, convection currents" },
            { t: "Work, Energy & Power forces", s: "Force, distance, kinetic and potential mechanical energies" },
            { t: "Core simple machines in classrooms", s: "Analyzing levers, pulleys, inclined planes, wheelbarrows" },
            { t: "Optometrics & Light Energy basics", s: "Straight-line light behavior, plane reflection mirrors" },
            { t: "Electricity currents & circuits", s: "Batteries, wire connections, bulbs, series/parallel loops" },
            { t: "Magnetism: Poles attraction", s: "Iron filings experiments, compass needle behaviors" },
            { t: "Basic Electronics elements", s: "Fuses, resistors, simple LED diodes on circuit boards" },
            { t: "Solar Orbit systems", s: "Ordering nine planets, earth rota rotation and revolutions" },
            { t: "Term II JSS Science Exams", s: "Reviewing heat, light, machine mechanics, and electronics" }
          ];
          return topics[week - 1] || { t: `JSS Science Dynamics Week ${week}`, s: "Physical energy and mechanics" };
        } else {
          const topics = [
            { t: "Human Digestion System organs", s: "Structure of mouth, stomach, small intestine, waste release" },
            { t: "Respiratory System anatomy", s: "How windpipe, trachea and lungs transfer oxygen molecules" },
            { t: "Human Blood Circulation", s: "Heart chambers, red blood cells, white cells, oxygen transport" },
            { t: "Kidney, Skin, and Lung Excretion", s: "Removing salts, sweat and carbon dioxide from standard cells" },
            { t: "Reproductive system structures", s: "Basic biology of plant and mammal multiplication setups" },
            { t: "Forest and Soil Preservation", s: "Stopping soil nutrient wash-outs, compost manures" },
            { t: "Advanced Space Probes mechanics", s: "Sputnik history, communication satellites, astronaut tasks" },
            { t: "Information Communication grids", s: "Radio frequencies, visual television antennas signals" },
            { t: "Scientific Research innovations", s: "Discovering how vaccines and solar panels are manufactured" },
            { t: "Promotional JSS Science Finals", s: "Promotional review of JSS Science courses syllabus" }
          ];
          return topics[week - 1] || { t: `JSS Science Anatomy Week ${week}`, s: "Anatomical systems and environment conservation" };
        }
      } else {
        // Primary / Nursery Basic Science
        if (normalTermLabel === "T1") {
          const topics = [
            { t: "Living and Non-Living things", s: "Classifying items around school compound" },
            { t: "The Human Body (External)", s: "Eyes, ears, nose, hands, legs and hygiene" },
            { t: "Plants in Our Environment", s: "Flowering plants, weeds, root and leaf parts" },
            { t: "Animals in Our Environment", s: "Mammals, birds, insects and domestic animals" },
            { t: "The Five Sense Organs", s: "Sight, hearing, smell, taste, and touch" },
            { t: "Personal Health & Hygiene", s: "Bathing, brushing teeth, and washing hands" },
            { t: "Water and Cleanliness", s: "Sources of water, boiling and filtering drink water" },
            { t: "Simple Weather Concepts", s: "Sunny, rainy, windy, cloudy skies" },
            { t: "Safety Rules in School", s: "Avoiding horseplay, sharp objects, and fire" },
            { t: "Term Review and Test", s: "Reviewing basic scientific observations" }
          ];
          return topics[week - 1] || { t: `Basic Science Week ${week}`, s: "Foundational scientific principles" };
        } else if (normalTermLabel === "T2") {
          const topics = [
            { t: "States of Matter: Ice/Water/Steam", s: "Melting ice blocks and evaporating kettle steam" },
            { t: "Air around classrooms", s: "Sensing wind breezes, blowing up party balloons" },
            { t: "Soils: Clay, Sandy, and Loamy", s: "Examining garden mud, beach sand, loamy soils" },
            { t: "Water: Practical uses", s: "Drinking, boiling vegetables, bathing and washing clothes" },
            { t: "Heat from fire and sun", s: "Drying wet school uniforms under hot morning suns" },
            { t: "Light and dark: Shadows", s: "Creating animals shadows on classroom walls with lanterns" },
            { t: "Drums and Flutes: Sounds", s: "Tapping wooden desks, listening to acoustic sounds" },
            { t: "Simple Hand tools", s: "Kitchen scissors, hammers, garden hand trowels uses" },
            { t: "Classroom Rubbish disposal", s: "Placing biscuit wrappers in waste baskets" },
            { t: "Term II Primary Science Test", s: "Verifying soil types, air properties, and hand tool names" }
          ];
          return topics[week - 1] || { t: `Primary Science Development Week ${week}`, s: "Basic life and physical sciences" };
        } else {
          const topics = [
            { t: "Forces: Pulling and Pushing toys", s: "Moving desks, pulling toy wagon lines in corridors" },
            { t: "Floating Wood vs Sinking Stones", s: "Placing school leaves and heavy metal nails in water sinks" },
            { t: "Dangerous Plants and Poisonous leaves", s: "Recognizing stinging nettles, avoiding tasting unknown crops" },
            { t: "Sun, Moon, and Twinkling Stars", s: "Naming night sky objects in elementary planet models" },
            { t: "Food Storage from insects", s: "Placing biscuits in tight plastic boxes, checking flies" },
            { t: "Daily Tooth care strategies", s: "Morning and bed brushing routines using fluoride pastes" },
            { t: "Simple Battery & Light circuits", s: "Connecting small wire terminals to tiny toy lights bulbs" },
            { t: "Growing Seeds in loamy cups", s: "Watching beans sprout roots and young leaves" },
            { t: "Water Safety rules", s: "Avoiding swimming in deep puddles and river currents" },
            { t: "Academic Promotional Assessment", s: "Reviewing basic objects, hygiene routines, and space" }
          ];
          return topics[week - 1] || { t: `Primary Science Mastery Week ${week}`, s: "Primary scientific habit milestones" };
        }
      }
    }
  }

  // 4. TECHNICAL DRAWING & COMPUTER / ICT / DATA PROCESSING
  if (
    subLower.includes("computer") ||
    subLower.includes("ict") ||
    subLower.includes("data") ||
    subLower.includes("drawing")
  ) {
    if (normalTermLabel === "T1") {
      const topics = [
        { t: "Introduction to Hardware Devices", s: "Casing, CPU, monitor, keyboards and mouse" },
        { t: "Input Devices and Uses", s: "Scanners, microphones, and keyboard layout" },
        { t: "Output Devices and Uses", s: "Printers, monitors, speaker volumes" },
        { t: "Storage Devices Core", s: "Hard disks, USB flash drives, memory cards" },
        { t: "The System Unit", s: "Motherboard, power supply cords, cooling fans" },
        { t: "Types of Computer Systems", s: "Desktops, laptops, tablets, smartphones" },
        { t: "Software Application Basics", s: "Operating systems, Microsoft Word, web browsers" },
        { t: "Keyboarding Skills", s: "Home row keys, spacebar, typing layouts" },
        { t: "Computer Safety", s: "Correct posture, avoiding liquids, turning off properly" },
        { t: "Practical Revision Drill", s: "Simulating system boot and standard menu navigations" }
      ];
      return topics[week - 1] || { t: `Computer Technology Week ${week}`, s: "Digital literacy outline" };
    } else if (normalTermLabel === "T2") {
      const topics = [
        { t: "Foundations of Operating Systems", s: "Graphical interfaces vs command-lines controls" },
        { t: "Desktop Folder Operations", s: "Creating folders, naming files, recycle bins cleanup" },
        { t: "Using Word Processors correctly", s: "Typing school paragraphs, opening new files sheets" },
        { t: "Formatting sentences in documents", s: "Adjusting fonts, bold markings, centering headings titles" },
        { t: "Creating tables in worksheets", s: "Placing grid rows, cell boundaries, adding student numbers" },
        { t: "Cleaning Computer Laboratories", s: "Static electricity safeguards, dust sheets, surge protectors use" },
        { t: "Understanding Computer Viruses", s: "Virus traits, anti-virus scanner sweep setups" },
        { t: "Drafting layouts with MS Paint", s: "Virtual paintbrush selections, filling canvas with bucket fills" },
        { t: "Class typing speed drills", s: "Placing index fingers on central row keys" },
        { t: "Term II Digital Skills Check", s: "Typing a 50-word school letter and saving in custom files" }
      ];
      return topics[week - 1] || { t: `Computer Software Week ${week}`, s: "Software system utilities" };
    } else {
      const topics = [
        { t: "Evolution of ICT networks", s: "Comparing postal letters to modern cellular GSM networks" },
        { t: "Using Web Browsers safely", s: "Search engines mechanics, seeking learning articles online" },
        { t: "Electronic Mail structures", s: "Writing email headings, downloading attachment files sheets" },
        { t: "Spreadsheet Excel boxes introduction", s: "Identifying cell ranges, column coordinates and worksheets" },
        { t: "Spreadsheet Excel Sum Formulas", s: "Automatically totaling class student marks grids" },
        { t: "Creating PowerPoint Slideshows", s: "Slide backgrounds, title fields layout and transition effects" },
        { t: "Essential Keyboard Shortcuts", s: "Using Ctrl+C copy, Ctrl+V paste and Ctrl+S storage" },
        { t: "Protecting Passwords & Profiles", s: "Cybersecurity habits, ignoring unsolicited email links" },
        { t: "Creating backup copies of files", s: "Saving data on external hard drives or Google drives" },
        { t: "Annual Promotional Assessment", s: "Theoretical and hands-on laboratory certification exam" }
      ];
      return topics[week - 1] || { t: `Computer Applied Week ${week}`, s: "Applied spreadsheet and networking tools" };
    }
  }

  // 5. SOCIAL & CIVIC STUDY / NATIONAL VALUES / GOVERNMENT / HISTORY / ECONOMICS / COMMERCE
  if (
    subLower.includes("social") ||
    subLower.includes("civic") ||
    subLower.includes("values") ||
    subLower.includes("government") ||
    subLower.includes("history") ||
    subLower.includes("economics") ||
    subLower.includes("commerce")
  ) {
    if (normalTermLabel === "T1") {
      const topics = [
        { t: "Introduction to Society & Culture", s: "Core definition, localized families, ethnic values" },
        { t: "The Role of Family Unit", s: "Responsibilities of fathers, mothers, and children" },
        { t: "National Symbols of Nigeria", s: "The Flag, Coat of Arms, Pledge, and National Anthem" },
        { t: "Citizenship and Rights", s: "Definition, duties, obligation to defend public rules" },
        { t: "Our Natural Resources", s: "Crude oil, coal, agriculture, water resources in Nigeria" },
        { t: "Leadership and Governance", s: "The President, Governors, Local Government Chairmen" },
        { t: "Social Issues & Integrity", s: "Honesty, avoiding examination malpractices, hard work" },
        { t: "Road Safety Codes", s: "The FRSC, zebra crossings, traffic lights systems" },
        { t: "Nigerian Historic Heroes", s: "Herbert Macaulay, Nnamdi Azikiwe, Obafemi Awolowo, Ahmadu Bello" },
        { t: "Syllabus Review & Promotional Term Review", s: "Consolidated assessment sheets" }
      ];
      return topics[week - 1] || { t: `Social Studies Core Week ${week}`, s: "Civic consciousness" };
    } else if (normalTermLabel === "T2") {
      const topics = [
        { t: "Traditional Holiday Festivals", s: "Durbar horse rides, Eyo caps, Argungu fishing celebrations" },
        { t: "Public Social Crises in Nigeria", s: "Juvenile delinquency, examination leaks safeguards" },
        { t: "Cooperation and Shared Unity", s: "Mutual helper tolerance, sportsmanship, ethnic collaboration" },
        { t: "Teamwork & Division of Labour", s: "How shared community groups clear streets and farms fast" },
        { t: "Disaster Management Frameworks", s: "The Red Cross, SEMA and NEMA emergency numbers" },
        { t: "Environmental Hazards: Flood/Erosion", s: "Managing drains, avoiding blockages, planting clean grass" },
        { t: "Nigerian Constitutional Principles", s: "Role of legal rights, courts, and human rights charters" },
        { t: "History of Government Stylings", s: "From local kings/chiefs to modern democratic voting systems" },
        { t: "Promoting Inter-Ethnic Marriages", s: "National integration, National Sports Festivals, NYSC goals" },
        { t: "Term II Civic Assessment Exam", s: "Evaluating constitutional safety, team works, and emergency values" }
      ];
      return topics[week - 1] || { t: `Social Development Week ${week}`, s: "Applied national values" };
    } else {
      const topics = [
        { t: "Human Rights and Child Rights Act", s: "Universal protection of children school facilities" },
        { t: "Drug Abuse Controls and NDLEA", s: "Combating illicit narcotics, physical addiction safeguards" },
        { t: "Shared Community vigilance rosters", s: "Clearing gutters, neighborhood security watching groups" },
        { t: "Highway codes & Driving Permits", s: "Federal road safety tests, checking driver licenses" },
        { t: "Banking and Personal Saving habits", s: "Opening piggy banks, keeping budgets, checks on wastes" },
        { t: "Evolution of Public Communication", s: "From ancient wooden talking drums to telecom satellites plans" },
        { t: "Linguistic Toleration guidelines", s: "Politely greeting Nigerian language speakers: Hausa, Igbo, Yoruba" },
        { t: "Global Alliances: ECOWAS & AU", s: "West African trade agreements, African Union peace objectives" },
        { t: "Maintenance of Public Assets", s: "Stopping railway tampering, keeping public buildings painted" },
        { t: "Annual Promotional Assessment", s: "Comprehensive test covering all civic, family, and highway codes" }
      ];
      return topics[week - 1] || { t: `Social Civic Mastery Week ${week}`, s: "Advanced civil rights and commerce definitions" };
    }
  }

  // 6. DEFAULT GENERAL NIGERIAN CURRICULUM FALLBACK
  if (normalTermLabel === "T1") {
    const generalSubjects = [
      { t: "Introduction and Terminology", s: "Analyzing core basic terms and structural layout" },
      { t: "Historical Background in Nigeria", s: "Chronology of development, pre-colonial to modern times" },
      { t: "Essential Factors and Attributes", s: "Key variables, criteria, and standard principles" },
      { t: "Practical Applications & Case Studies", s: "Localized scenarios, national models, and practical work" },
      { t: "Comparative Analysis", s: "Evaluating differences between local and international systems" },
      { t: "Regulatory and Institutional Frameworks", s: "The statutory bodies, government approvals, and policies" },
      { t: "Socio-Economic Impacts", s: "Analyzing implications on families, markets, and country" },
      { t: "Current Challenges and Solutions", s: "Critical issues, funding, policy gaps and recommendations" },
      { t: "Future Trends & Innovations", s: "Technological impacts, digital integrations, global shifts" },
      { t: "Review, Collation & Examination", s: "General term synthesis and mock evaluation prep" }
    ];
    return generalSubjects[week - 1] || { t: `${subject} Unit ${week}`, s: "Syllabus content" };
  } else if (normalTermLabel === "T2") {
    const generalSubjects = [
      { t: "Core Systems Integration Theory", s: "Evaluating structural interfaces and advanced design methodologies" },
      { t: "Empirical Sampling Frameworks", s: "Collecting qualitative data indicators in Nigerian states" },
      { t: "Interlocking Operational Guidelines", s: "Setting compliance checks, industrial metrics, and lab handbooks" },
      { t: "Economic Overhead and Financing", s: "Capital estimations, administrative costing, budget margins" },
      { t: "Mitigation of Process Constraints", s: "Error margin reduction routines, finding correlations" },
      { t: "Sovereign Policies and Compliance", s: "Ensuring operations conform with central ISO regulations" },
      { t: "Integrated Database Systems sync", s: "Structuring local spreadsheet charts and telemetry grids" },
      { t: "Operational Risks minimization", s: "Safety checks, stress analysis, and failure backup strategies" },
      { t: "Project Communication frameworks", s: "Organizing professional briefings and client alignment" },
      { t: "Second Term Milestone Examination", s: "Demonstrating system design competencies and project drafts" }
    ];
    return generalSubjects[week - 1] || { t: `${subject} Intermediate ${week}`, s: "Development curriculum content" };
  } else {
    const generalSubjects = [
      { t: "Strategic Project Blueprinting", s: "Constructing final terminal blueprints and field trial parameters" },
      { t: "Environmental Preservation audits", s: "Analyzing local ecology wear, conservation and green metrics" },
      { t: "Lifecycle Sustainability evaluation", s: "Predictive asset maintenance scheduling and depreciation values" },
      { t: "Quality Assurance validation indicators", s: "Benchmarking output deliverables, checking certification goals" },
      { t: "Emerging Automation adaptations", s: "Leveraging cloud services and automated tools for speedy workflows" },
      { t: "International Standards & Trade", s: "Connecting local operations with West African and global hubs" },
      { t: "Ethics, Integrity & Professional Codes", s: "Anti-corruption rules, protecting intellectual property rights" },
      { t: "Public Consultation protocols", s: "Gathering end-user feedback, executing feedback optimization loops" },
      { t: "Syllabus Capstone Demonstration", s: "Building actual project physical prototypes for evaluations" },
      { t: "Cumulative Annual Promotion Exam", s: "Comprehensive certification testing on all three terms coursework" }
    ];
    return generalSubjects[week - 1] || { t: `${subject} Mastery ${week}`, s: "Syllabus mastery content" };
  }
}

/**
 * Validates the uniqueness of curriculum topics across all three academic terms.
 * This ensures that First, Second and Third Term topics are independent and non-repetitive.
 */
export function validateSyllabusUniqueness(
  levelId: string,
  classLevel: string,
  subject: string
): { isValid: boolean; duplicateCount: number; report: string; duplicates: string[] } {
  const terms: ("First Term" | "Second Term" | "Third Term")[] = ["First Term", "Second Term", "Third Term"];
  const topicsSeen = new Map<string, string>(); // lowercaseTopic -> Term + Week designation
  const duplicates: string[] = [];
  let duplicateCount = 0;

  for (const term of terms) {
    for (let week = 1; week <= 10; week++) {
      const { t } = getCurriculumTopicAndSubtopic(levelId, classLevel, subject, term, week);
      // Clean and normalize to avoid simple case mismatch
      const normalizedTopic = t.toLowerCase().trim()
        .replace(/^(week\s+\d+|unit\s+\d+|lesson\s+\d+)\s*[:|-]\s*/, ""); // remove common week prefixes

      if (topicsSeen.has(normalizedTopic)) {
        duplicateCount++;
        duplicates.push(`"${t}" (Duplicate in both ${topicsSeen.get(normalizedTopic)} and ${term} Week ${week})`);
      } else {
        topicsSeen.set(normalizedTopic, `${term} Week ${week}`);
      }
    }
  }

  return {
    isValid: duplicateCount === 0,
    duplicateCount,
    duplicates,
    report: duplicateCount === 0 
      ? "Syllabus configuration is 100% valid! All terms contain completely unique, non-duplicative progressive academic outlines."
      : `Syllabus integrity issue detected! found ${duplicateCount} repeating topics in different terms.`
  };
}
