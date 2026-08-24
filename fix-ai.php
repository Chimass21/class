<?php
/* ============================================================
   Brain4 - One-Time AI Fixer
   Upload this file into the SAME folder as your .env
   (usually public_html), open it in your browser, paste your
   Groq API key, press FIX. It rewrites the AI settings in .env,
   clears Laravel caches, tests Groq for real, and deletes itself.
   ============================================================ */
session_start();
if (!isset($_SESSION['fixpin'])) {
    $_SESSION['fixpin'] = substr(str_shuffle('23456789ABCDEFGHJKMNPQRSTUVWXYZ'), 0, 6);
}
$PIN = $_SESSION['fixpin'];
$msg = ''; $ok = false;

/* Find the app root by locating .env here or up to 4 levels above
   (handles both docroot=repo-root and docroot=public/ layouts). */
function findAppRoot() {
    $dir = __DIR__;
    for ($i = 0; $i < 5; $i++) {
        if (file_exists($dir.'/.env')) return $dir;
        $parent = dirname($dir);
        if ($parent === $dir) break;
        $dir = $parent;
    }
    return null;
}
function envLines($path) { return file_exists($path) ? file($path, FILE_IGNORE_NEW_LINES) : []; }
function maskKey($k) { $k = trim($k); return strlen($k) > 12 ? substr($k,0,8).'...'.substr($k,-4) : '(short?)'; }
function currentVals($root) {
    foreach (envLines($root.'/.env') as $l) {
        if (preg_match('/^OPENAI_BASE_URL=(.*)$/',$l,$m)) $base=trim($m[1]);
        if (preg_match('/^OPENAI_MODEL=(.*)$/',$l,$m)) $model=trim($m[1]);
        if (preg_match('/^OPENAI_API_KEY=(.*)$/',$l,$m)) $key=trim($m[1]);
    }
    return [$base ?? '(unset)', $model ?? '(unset)', $key ?? ''];
}

$ROOT = findAppRoot();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = trim($_POST['key'] ?? '');
    $pin = trim($_POST['pin'] ?? '');
    if ($pin !== $PIN) {
        $msg = '<b style="color:red">Wrong PIN. Refresh page to get a new one.</b>';
    } elseif ($ROOT === null) {
        $msg = '<b style="color:red">Could not find .env in this folder or any parent (searched 5 levels up). Upload this file into your site folders and retry.</b>';
    } elseif (!str_starts_with($key, 'gsk_')) {
        $msg = '<b style="color:red">That does not look like a Groq key (must start with gsk_). Get one at console.groq.com/keys</b>';
    } else {
        $envFile = $ROOT.'/.env';
        // 1. Backup .env
        $bak = $ROOT.'/.env.bak.'.date('Ymd_His');
        if (file_exists($envFile)) copy($envFile, $bak);

        // 2. Rewrite AI lines
        $lines = envLines($envFile);
        $want = [
            'OPENAI_API_KEY'   => $key,
            'OPENAI_BASE_URL'  => 'https://api.groq.com/openai',
            'OPENAI_MODEL'     => 'openai/gpt-oss-120b',
            'OPENAI_MAX_RETRIES' => '3',
            'OPENAI_TIMEOUT'     => '120',
            'OPENAI_TPM_BUDGET'  => '7800',
        ];
        $seen = [];
        foreach ($lines as $i => $l) {
            foreach ($want as $k => $v) {
                if (preg_match('/^'.$k.'=/',$l)) { $lines[$i] = "$k=$v"; $seen[$k] = true; continue 2; }
            }
        }
        foreach ($want as $k => $v) { if (empty($seen[$k])) $lines[] = "$k=$v"; }
        file_put_contents($envFile, implode("\n", $lines)."\n");

        // 3. Clear caches (best effort)
        $cleared = [];
        foreach (['bootstrap/cache/config.php','bootstrap/cache/routes-v7.php','bootstrap/cache/events.php','bootstrap/cache/packages.php','bootstrap/cache/services.php'] as $c) {
            if (file_exists($ROOT.'/'.$c)) { @unlink($ROOT.'/'.$c); $cleared[] = basename($c); }
        }

        // 4. REAL TEST against Groq
        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer '.$key],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => 'openai/gpt-oss-120b',
                'messages' => [['role'=>'user','content'=>'Reply with exactly: OK']],
                'max_tokens' => 2000,
            ]),
        ]);
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $body = json_decode((string)$resp, true);
        $reply = $body['choices'][0]['message']['content'] ?? '';

        if ($http === 200 && trim($reply) !== '') {
            $ok = true;
            $msg = '<b style="color:#0a0">SUCCESS!</b> Groq replied: "'.htmlspecialchars(trim($reply)).'".'
                 . '<br>.env updated at <code>'.htmlspecialchars($ROOT.'/.env').'</code> (backup: '.htmlspecialchars(basename($bak)).').'
                 . ($cleared ? '<br>Caches cleared: '.htmlspecialchars(implode(', ', $cleared)) : '')
                 . '<br><br><b>This fixer deleted itself. Your lesson notes will generate now.</b>';
            @unlink(__FILE__);
        } else {
            $errText = $body['error']['message'] ?? substr((string)$resp,0,300);
            $msg = "<b style='color:red'>Groq test FAILED (HTTP $http):</b> ".htmlspecialchars($errText)
                 . '<br><br>.env was still updated - try generating anyway, or check the key.';
        }
    }
}
[$cb, $cm, $ck] = $ROOT ? currentVals($ROOT) : ['(no .env found)','(no .env found)',''];
?><!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Brain4 AI Fixer</title>
<style>body{font-family:system-ui;background:#0f172a;color:#e2e8f0;display:flex;justify-content:center;padding:40px}div{max-width:520px;width:100%}input{width:100%;padding:10px;margin:8px 0;border-radius:8px;border:1px solid #334155;background:#1e293b;color:#fff;box-sizing:border-box}button{background:#22c55e;color:#04220f;border:none;padding:12px 28px;border-radius:8px;font-weight:bold;font-size:16px;cursor:pointer}pre{background:#1e293b;padding:10px;border-radius:8px;overflow:auto}</style>
</head><body><div>
<h2>🧠 Brain4 AI Fixer</h2>
<p>Current server AI config:</p>
<pre>BASE_URL: <?= htmlspecialchars($cb) ?>
MODEL:    <?= htmlspecialchars($cm) ?>
API_KEY:  <?= $ck ? htmlspecialchars(maskKey($ck)).' ('.htmlspecialchars(substr($ck,0,4)).'...)' : '(unset)' ?></pre>
<form method="post">
<label>PIN: <input name="pin" value="" placeholder="<?= htmlspecialchars($PIN) ?>" required></label>
<label>Groq API Key: <input name="key" type="password" placeholder="gsk_..." required></label>
<button type="submit">FIX MY AI NOW</button>
</form>
<?php if ($msg) echo "<p>$msg</p>"; ?>
</div></body></html>
