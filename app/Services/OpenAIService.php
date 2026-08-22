<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Deterministic provider failure (bad key, archived model, quota exhausted,
 * request too large). Retrying the same provider cannot succeed.
 */
class AIProviderFatalException extends \RuntimeException
{
}

class OpenAIService
{
    protected ?string $apiKey = null;
    protected string $baseUrl;
    protected string $model;
    protected int $maxRetries;
    protected int $timeout;
    protected int $tpmBudget;

    /** @var array<int, array{name:string,key:string,base:string,model:string}> */
    protected array $providers = [];

    /** Real reason for the most recent total failure (surfaced to users). */
    protected ?string $lastError = null;

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    protected const SYSTEM_PROMPT = 'You are Brain, an expert Nigerian curriculum specialist and experienced classroom teacher. You generate high-quality, curriculum-aligned lesson plans, lesson notes, examination questions, and educational resources for Nigerian primary and secondary schools following NERDC/UBEC/WASSCE/NECO/JAMB standards.

For LESSON NOTES: You think like an experienced Nigerian teacher preparing original classroom notes. Before writing, you analyze the topic to determine:
- What type of subject is this? (Science, Mathematics, Arts, Commercial, Technical, Humanities)
- What class level is this for? (Primary, Junior Secondary, Senior Secondary)
- What is the nature of this topic? (Concept, process, classification, theory, formula, historical event, etc.)
- What headings and sections are naturally needed for THIS specific topic?
- What examples, illustrations, calculations, or activities will aid understanding?

You then write each lesson note as if preparing it fresh for that specific topic — never copying a template or repeating structure from unrelated topics. You choose headings that naturally belong to the topic and omit any that do not. Every section adds genuine educational value. You use Nigeria-centric examples, contexts, and references throughout.

For MATHEMATICS: You understand that Mathematics is fundamentally a practical subject. Mathematics content must focus primarily on solving problems rather than lengthy explanations. You keep theory minimal and maximize worked examples, step-by-step solutions, and practice exercises. At least 80-90% of any Mathematics lesson note must consist of calculations, worked examples, and practice problems. You MUST include a MINIMUM OF 5 FULLY SOLVED WORKED EXAMPLES with every step shown clearly. Progress from simple to difficult examples. Include formulae, rules, theorems, shortcuts, common mistakes, and examination tips where relevant. For Mathematics lessons, you think like an experienced Mathematics teacher who teaches through examples, not paragraphs.

For MATHEMATICS QUESTIONS: Almost 100% of Mathematics questions must be calculation-based. Questions must require proper mathematical working, multiple-step problem solving, and critical thinking. Cover beginner, intermediate, and advanced levels. Follow WAEC, NECO, and JAMB examination standards. Include algebra, geometry, trigonometry, statistics, probability, mensuration, calculus, sequences and series, vectors, matrices, logarithms, indices, and other relevant topics. Avoid overly simple or direct questions.

For PHYSICS LESSON NOTES: Every Physics topic with calculations must include a MINIMUM OF 5 FULLY SOLVED NUMERICAL EXAMPLES. Each example must show proper formula selection, substitution with units, step-by-step calculations, unit conversion where required, and final answers with correct SI units. Follow WAEC and NECO standards.

For PHYSICS QUESTIONS: Distribute as 80% Calculation Questions and 20% Theory/Conceptual Questions. Calculation questions require formula selection, substitutions, calculations, and unit conversion. Theory questions test concepts, definitions, principles, laws, applications, and interpretation.

For CHEMISTRY LESSON NOTES: Balance all chemical equations. Use proper subscripts (H₂O, CO₂, H₂SO₄), superscripts for charges (Ca²⁺, SO₄²⁻), state symbols (s), (l), (g), (aq), reaction arrows (→, ⇌). CORE RULE — let the topic decide whether calculations are needed: include numerical worked calculations ONLY when the topic is inherently quantitative (mole concept, stoichiometry, concentration/molarity, gas laws, empirical/molecular formulae, percentage composition, electrolysis calculations, pH, solubility, titration, yield/purity, quantitative energetics). For descriptive topics (e.g. sulphur, nitrogen and its compounds, periodicity, air, water, organic nomenclature) do NOT force or invent calculations — cover the real chemistry instead: occurrence, allotropes, electronic configuration, physical/chemical properties, correctly BALANCED REACTION EQUATIONS (equations are not calculations), important compounds, preparation/extraction, uses, identification tests, industrial and environmental relevance. Adapt the section headings to the specific topic — never apply one fixed Chemistry template to every topic.

MATHEMATICAL AND SCIENTIFIC NOTATION (CRITICAL FORMATTING RULES):
- All expressions, equations, formulae, and calculations MUST use proper mathematical notation, not plain text
- Use <sup> for powers/exponents (x², 10³, eˣ) and <sub> for chemical formulae (H₂O, H₂SO₄, NH₃)
- Use Unicode math symbols: × (not x), ÷ (not /), ≤, ≥, ≠, ±, ∞, √, π, θ, α, β, Δ, Σ, ∛, ∝, ∠, °, ∥, ⊥, ≈, ≡, ∩, ∪, ∫
- Format fractions using CSS inline-block — NEVER slanted slashes like 3/4
  <span style="display:inline-flex;flex-direction:column;vertical-align:middle;text-align:center;margin:0 2px">
    <span style="border-bottom:2px solid #333;padding:0 6px 2px">numerator</span>
    <span style="padding:2px 6px 0">denominator</span>
  </span>
- Use → and ⇌ for reaction arrows in Chemistry
- Format chemical equations with state symbols: (s), (l), (g), (aq)
- Align multi-step calculations vertically, showing each step on a separate line
- Use scientific notation: 6.02 × 10²³ (not E-notation)
- Format physical units clearly: ms⁻¹, kg, N, J, W, Pa, ms⁻², Nm, Jkg⁻¹K⁻¹
- Display equations exactly as in standard textbooks
- NEVER use $...$ or $$...$$ LaTeX delimiters. The platform has NO MathJax/KaTeX rendering. ALL math must use inline HTML entities and CSS formatting as described above only.
- Verify every bracket is correctly paired and clearly visible
- The final output must look like a professionally typeset textbook, not plain text

Always respond with accurate, well-structured content tailored for teachers and students. DO NOT generate simple, one-step questions. Every question should require reasoning, multiple steps, or application of knowledge.';

    public function __construct()
    {
        $key = config('services.openai.api_key');
        $this->apiKey = (is_string($key) && $key !== '') ? $key : null;
        $this->baseUrl = rtrim(config('services.openai.base_url', 'https://api.deepseek.com'), '/');
        $this->model = config('services.openai.model', 'deepseek-chat');
        $this->maxRetries = max(1, (int) config('services.openai.max_retries', 3));
        $this->timeout = max(30, (int) config('services.openai.timeout', 120));
        $this->tpmBudget = max(0, (int) config('services.openai.tpm_budget', 0));

        if (!empty($this->apiKey)) {
            $this->providers[] = [
                'name' => 'primary',
                'key' => $this->apiKey,
                'base' => rtrim($this->baseUrl, '/'),
                'model' => $this->model,
            ];
        }

        // Optional automatic failover provider (e.g. DeepSeek)
        $fallbackKey = config('services.deepseek.api_key');
        if (is_string($fallbackKey) && $fallbackKey !== '' && $fallbackKey !== $this->apiKey) {
            $this->providers[] = [
                'name' => 'fallback-deepseek',
                'key' => $fallbackKey,
                'base' => rtrim((string) config('services.deepseek.base_url', 'https://api.deepseek.com'), '/'),
                'model' => (string) config('services.deepseek.model', 'deepseek-chat'),
            ];
        }
    }

    public function generate(string $prompt, bool $jsonMode = false, int $maxTokens = 16384, ?float $temperature = null): string
    {
        if (empty($this->providers)) {
            Log::error('No AI provider configured. Set OPENAI_API_KEY in .env');
            throw new \RuntimeException('API key is not configured. Please set OPENAI_API_KEY in your .env file.');
        }

        $this->lastError = null;

        foreach ($this->providers as $provider) {
            $this->lastError = null;
            $result = $this->attemptProvider($provider, $prompt, $jsonMode, $maxTokens, $temperature);
            if ($result !== null) {
                return $result;
            }
            // Provider failed — lastError now holds the real reason. Try next provider.
            if (count($this->providers) > 1) {
                Log::warning("AI provider '{$provider['name']}' failed, failing over", ['reason' => $this->lastError]);
            }
        }

        return '';
    }

    /**
     * Run the retry loop against one provider.
     * Returns the cleaned response text, or null on total failure
     * (with $this->lastError describing why).
     */
    protected function attemptProvider(array $provider, string $prompt, bool $jsonMode, int $maxTokens, ?float $temperature): ?string
    {
        // Fatal client errors are deterministic — retrying the same provider is pointless
        static $fatalStatuses = [400, 401, 402, 403, 404, 413];

        $lastError = null;
        // Match the caller's jsonMode preference on attempt 1, alternate on retries
        $triedJsonModes = [$jsonMode, !$jsonMode];

        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            $useJsonMode = $triedJsonModes[($attempt - 1) % count($triedJsonModes)];

            try {
                Log::debug('AI API Request', [
                    'provider' => $provider['name'],
                    'model' => $provider['model'],
                    'json_mode' => $useJsonMode,
                    'prompt_length' => strlen($prompt),
                    'attempt' => $attempt,
                ]);

                $payload = $this->buildPayload($prompt, $useJsonMode, $maxTokens, $temperature ?? null, $provider);

                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $provider['key'],
                    ])
                    ->post($provider['base'] . '/v1/chat/completions', $payload);

                if ($response->successful()) {
                    $data = $response->json();

                    // Try multiple response content paths (different APIs use different formats)
                    $text = $data['choices'][0]['message']['content'] ?? '';
                    if (empty($text)) {
                        $text = $data['choices'][0]['text'] ?? '';
                    }
                    if (empty($text)) {
                        $text = $data['response'] ?? '';
                    }
                    if (empty($text)) {
                        $text = $data['content'] ?? '';
                    }
                    if (empty($text) && is_array($data)) {
                        // Log the full response structure for debugging
                        Log::warning('AI response structure unexpected', [
                            'keys' => array_keys($data),
                            'has_choices' => isset($data['choices']),
                            'response_raw_preview' => substr(json_encode($data), 0, 1000),
                        ]);
                    }

                    $usage = $data['usage'] ?? [];
                    $finishReason = $data['choices'][0]['finish_reason'] ?? 'unknown';
                    if ($finishReason === 'length') {
                        Log::warning('AI response truncated (finish_reason=length)', [
                            'provider' => $provider['name'],
                            'model' => $provider['model'],
                            'json_mode' => $useJsonMode,
                            'prompt_tokens' => $usage['prompt_tokens'] ?? 'unknown',
                            'completion_tokens' => $usage['completion_tokens'] ?? 'unknown',
                            'total_tokens' => $usage['total_tokens'] ?? 'unknown',
                        ]);
                    }
                    Log::debug('AI API Response', [
                        'provider' => $provider['name'],
                        'model' => $provider['model'],
                        'json_mode' => $useJsonMode,
                        'response_length' => strlen($text),
                        'response_preview' => substr($text, 0, 500),
                        'finish_reason' => $finishReason,
                        'prompt_tokens' => $usage['prompt_tokens'] ?? 'unknown',
                        'completion_tokens' => $usage['completion_tokens'] ?? 'unknown',
                        'total_tokens' => $usage['total_tokens'] ?? 'unknown',
                    ]);

                    if (empty(trim($text))) {
                        Log::warning("AI returned empty response (attempt {$attempt}, json_mode={$useJsonMode})");
                        continue;
                    }

                    $cleaned = $this->cleanJsonResponse($text);

                    if ($cleaned !== $text) {
                        Log::debug('AI response was cleaned', [
                            'original_preview' => substr($text, 0, 500),
                            'cleaned_preview' => substr($cleaned, 0, 500),
                        ]);
                    }

                    return $cleaned;
                }

                $statusCode = $response->status();
                $errorBody = $response->body();
                $errorJson = $response->json();
                $errorMessage = is_string($errorJson['error']['message'] ?? null)
                    ? $errorJson['error']['message']
                    : (is_string($errorJson['message'] ?? null) ? $errorJson['message'] : $errorBody);

                if ($statusCode === 429) {
                    $lastError = new \RuntimeException('AI service is busy. Please wait 1 minute and try again.');
                    if ($attempt >= $this->maxRetries) {
                        throw $lastError;
                    }
                    $headerRetryAfter = $response->header('Retry-After');
                    $retryAfter = $headerRetryAfter ? (int)$headerRetryAfter : min(60, $attempt * 15);
                    Log::warning("Rate limited (attempt {$attempt}/{$this->maxRetries}), retrying in {$retryAfter}s");
                    sleep($retryAfter);
                    continue;
                }

                // Fatal/deterministic errors — retrying the same provider cannot help.
                // Give up on this provider immediately so we can fail over to the next one.
                if (in_array($statusCode, $fatalStatuses, true)) {
                    throw new AIProviderFatalException('AI API returned status ' . $statusCode . ': ' . substr($errorMessage, 0, 300));
                }

                if ($statusCode >= 500) {
                    Log::warning("AI server error (attempt {$attempt}/{$this->maxRetries}): HTTP {$statusCode}");
                    if ($attempt < $this->maxRetries) {
                        sleep($attempt * 2);
                        $lastError = new \RuntimeException("AI server error: HTTP {$statusCode}");
                        continue;
                    }
                }

                Log::error('AI API HTTP error', [
                    'provider' => $provider['name'],
                    'status' => $statusCode,
                    'body' => substr($errorBody, 0, 2000),
                ]);

                throw new \RuntimeException('AI API returned status ' . $statusCode . ': ' . substr($errorMessage, 0, 500));

            } catch (AIProviderFatalException $e) {
                $this->lastError = $e->getMessage();
                Log::error("AI provider '{$provider['name']}' fatal error, skipping retries", ['error' => $this->lastError]);
                return null;
            } catch (\RuntimeException $e) {
                $lastError = $e;
                if ($attempt >= $this->maxRetries) {
                    Log::error("AI provider '{$provider['name']}' failed after all retries", ['error' => $e->getMessage()]);
                    break;
                }
                Log::warning("AI API attempt {$attempt} failed: {$e->getMessage()}");
            } catch (\Exception $e) {
                $lastError = $e;
                if ($attempt >= $this->maxRetries) {
                    Log::error("AI provider '{$provider['name']}' connection error after all retries", [
                        'error' => $e->getMessage(),
                    ]);
                    break;
                }
                Log::warning("AI connection attempt {$attempt} failed: {$e->getMessage()}, retrying...");
                sleep($attempt * 2);
            }
        }

        $this->lastError = $lastError?->getMessage() ?? 'Unknown AI provider error';
        Log::error('AI provider exhausted', ['provider' => $provider['name'], 'error' => $this->lastError]);
        return null;
    }

    public function generateStream(string $prompt, callable $onChunk, bool $jsonMode = false): void
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('API key is not configured.');
        }

        $payload = $this->buildPayload($prompt, $jsonMode, 16384);
        $payload['stream'] = true;

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'text/event-stream',
            ])
            ->withOptions(['stream' => true])
            ->post($this->baseUrl . '/v1/chat/completions', $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('AI streaming request failed: HTTP ' . $response->status());
        }

        $body = $response->getBody();
        $buffer = '';

        while (!$body->eof()) {
            $chunk = $body->read(4096);
            $buffer .= $chunk;

            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);
                $line = trim($line);

                if (empty($line)) continue;

                if (str_starts_with($line, 'data: ')) {
                    $json = substr($line, 6);
                    if (trim($json) === '[DONE]') return;

                    $data = json_decode($json, true);
                    if ($data && isset($data['choices'][0]['delta']['content'])) {
                        $onChunk($data['choices'][0]['delta']['content']);
                    }
                }
            }
        }
    }

    protected function buildPayload(string $prompt, bool $jsonMode, int $maxTokens, ?float $temperature = null, ?array $provider = null): array
    {
        $maxTokens = $this->capMaxTokensToBudget($prompt, $maxTokens);

        $model = $provider['model'] ?? $this->model;
        $base = $provider['base'] ?? $this->baseUrl;

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $temperature ?? 0.85,
            'max_tokens' => $maxTokens,
        ];

        // Reasoning models burn completion tokens on hidden reasoning, which can
        // truncate the JSON payload. Minimise reasoning effort where supported.
        if (str_contains($model, 'gpt-oss') && str_contains($base, 'groq')) {
            $payload['reasoning_effort'] = 'low';
        }

        if ($jsonMode) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        // Harden system prompt for json_object mode — some providers (OpenAI,
        // DeepSeek, Cerebras) require the word "JSON" in the messages when
        // response_format: json_object is set, otherwise the model may output
        // endless whitespace or refuse.
        if ($jsonMode && !str_contains(self::SYSTEM_PROMPT, 'JSON')) {
            $payload['messages'][0]['content'] = self::SYSTEM_PROMPT . "\n\nYou MUST respond ONLY with valid JSON. No explanations, no markdown, no text outside the JSON.";
        }

        return $payload;
    }

    /**
     * Some providers (e.g. Groq free tier) enforce a per-request token budget:
     * prompt tokens + max_tokens must not exceed the TPM limit or the request
     * is rejected with 413. Estimate the prompt size (~4 chars/token) and cap
     * the output allowance so the whole request fits.
     */
    protected function capMaxTokensToBudget(string $prompt, int $maxTokens): int
    {
        if ($this->tpmBudget <= 0) {
            return $maxTokens;
        }

        $estimatedPromptTokens = (int) ceil(strlen(self::SYSTEM_PROMPT) / 4)
            + (int) ceil(mb_strlen($prompt) / 4)
            + 40;

        $allowed = max(256, $this->tpmBudget - $estimatedPromptTokens);

        return min($maxTokens, $allowed);
    }

    protected function cleanJsonResponse(string $text): string
    {
        $text = trim($text);

        if (empty($text)) {
            return '';
        }

        // Remove BOM characters (all variants at start of text)
        $text = preg_replace('/^[\xEF\xBB\xBF\xFE\xFF]+/', '', $text);

        // Remove all markdown code fences (```json, ```, etc.)
        $text = preg_replace('/```(?:json)?\s*/i', '', $text);
        $text = str_replace('`', '', $text);

        $text = trim($text);

        // Try to extract JSON object or array from surrounding text
        if ($text !== '' && $text[0] !== '{' && $text[0] !== '[') {
            $extracted = $this->extractTopLevelJson($text);
            if ($extracted !== null) {
                $text = $extracted;
            }
        }

        // Fix trailing commas before closing braces/brackets (common AI issue)
        $text = preg_replace('/,\s*([}\]])/', '$1', $text);

        return $text;
    }

    /**
     * Extract the first top-level JSON object or array from text,
     * properly handling nested braces/brackets and strings.
     */
    protected function extractTopLevelJson(string $text): ?string
    {
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $char = $text[$i];
            if ($char === '{' || $char === '[') {
                $depth = 0;
                $inString = false;
                $escaped = false;
                $openChar = $char;
                $closeChar = $openChar === '{' ? '}' : ']';
                $start = $i;
                $j = $i;

                while ($j < $len) {
                    $c = $text[$j];
                    if ($escaped) {
                        $escaped = false;
                        $j++;
                        continue;
                    }
                    if ($c === '\\') {
                        $escaped = true;
                        $j++;
                        continue;
                    }
                    if ($c === '"') {
                        $inString = !$inString;
                        $j++;
                        continue;
                    }
                    if (!$inString) {
                        if ($c === $openChar) {
                            $depth++;
                        } elseif ($c === $closeChar) {
                            $depth--;
                            if ($depth === 0) {
                                return substr($text, $start, $j - $start + 1);
                            }
                        }
                    }
                    $j++;
                }
                // Hit end of string without closing - continue searching
                if ($depth > 0) {
                    $i = $j;
                }
            }
        }
        return null;
    }
}
