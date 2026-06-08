<?php
// Suppress PHP warnings/notices so they never appear in the JSON response body.
ini_set('display_errors', '0');
error_reporting(0);

session_start();
header('Content-Type: application/json');

define('DB_FILE', __DIR__ . '/newsroom.sqlite');

try {
    $db = new PDO('sqlite:' . DB_FILE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, password_hash TEXT, role TEXT)");
    
    $db->exec("CREATE TABLE IF NOT EXISTS articles (
        id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, original_content TEXT, headline TEXT, article_content TEXT,
        social_1 TEXT, social_2 TEXT, image_suggestion TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, deleted_at DATETIME DEFAULT NULL
    )");

    $columns = [
        'wp_pushed_at' => 'DATETIME DEFAULT NULL',
        'wp_post_id' => 'INTEGER DEFAULT NULL',
        'hashtags' => 'TEXT DEFAULT NULL',
        'featured_image' => 'TEXT DEFAULT NULL',
        'wp_author' => 'INTEGER DEFAULT NULL',
        'wp_status' => 'TEXT DEFAULT NULL',
        'wp_categories' => 'TEXT DEFAULT NULL',
        'wp_tags' => 'TEXT DEFAULT NULL',
        'wp_date' => 'TEXT DEFAULT NULL',
        'push_count' => 'INTEGER DEFAULT 0',
        'img_title' => 'TEXT DEFAULT NULL',
        'img_alt' => 'TEXT DEFAULT NULL',
        'img_caption' => 'TEXT DEFAULT NULL',
        'wp_permalink' => 'TEXT DEFAULT NULL', 
        'short_url' => 'TEXT DEFAULT NULL',
        'article_status' => "TEXT DEFAULT 'draft'"
    ];
    
    foreach($columns as $col => $def) {
        try { $db->exec("ALTER TABLE articles ADD COLUMN $col $def"); } catch(PDOException $e) {}
    }

    $db->exec("CREATE TABLE IF NOT EXISTS settings (key TEXT PRIMARY KEY, value TEXT)");
    $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('language_variant', 'British')");
    $newKeywords = 'Keyword 1, Keyword 2, Keyword 3, Keyword 4, Keyword 5, Keyword 6, Keyword 7, Keyword 8, Keyword 9, Keyword 10, local, community';
    $oldKeywords = 'Hillingdon, Uxbridge, Hayes, Ruislip, Northwood, Yiewsley, West Drayton, Harefield, council, borough, local, community';
    // Insert default if no row exists; if the old location-specific default is still set, replace it with the generic placeholder
    $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('local_keywords', " . $db->quote($newKeywords) . ")");
    $stmt = $db->prepare("SELECT value FROM settings WHERE key='local_keywords'");
    $stmt->execute();
    $existingKw = $stmt->fetchColumn();
    if ($existingKw === $oldKeywords) {
        $db->prepare("UPDATE settings SET value=? WHERE key='local_keywords'")->execute([$newKeywords]);
    }

    $stmt = $db->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('password123', PASSWORD_DEFAULT);
        $db->exec("INSERT INTO users (username, password_hash, role) VALUES ('admin', '$hash', 'admin')");
    }
    $db->exec("DELETE FROM articles WHERE deleted_at IS NOT NULL AND deleted_at <= datetime('now', '-7 days')");

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

function checkAuth() {
    if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'Unauthorized']); exit; }
}

function getSetting($db, $key) {
    $stmt = $db->prepare("SELECT value FROM settings WHERE key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['value'] : null;
}

function callAI($db, $systemPrompt, $userPrompt) {
    $baseUrl = rtrim(getSetting($db, 'ai_base_url') ?: '', '/');
    $apiKey  = getSetting($db, 'ai_api_key') ?: '';
    $model   = getSetting($db, 'ai_model') ?: '';

    if (!$baseUrl || !$apiKey || !$model) return ['error' => 'AI provider not configured.'];

    $ch = curl_init($baseUrl . '/chat/completions');
    $data = ["model" => $model, "messages" => [["role" => "system", "content" => $systemPrompt], ["role" => "user", "content" => $userPrompt]]];
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $apiKey, "Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === false) return ['error' => 'Network/cURL Error: ' . $curlError];
    $result = json_decode($response, true);
    if (!is_array($result)) return ['error' => 'AI returned non-JSON response (HTTP ' . $httpCode . '). Check your AI Base URL and API Key in Settings.'];
    if (isset($result['error'])) return ['error' => 'AI API Error: ' . ($result['error']['message'] ?? 'Unknown')];
    if (!isset($result['choices'][0]['message']['content'])) return ['error' => 'Unexpected AI response format (HTTP ' . $httpCode . '). Raw: ' . substr($response, 0, 200)];
    return ['content' => $result['choices'][0]['message']['content']];
}

function esc_html_shim($text) {
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Shorten a URL via is.gd using format=simple + file_get_contents with ignore_errors,
 * exactly as the official is.gd PHP example recommends.
 * Returns ['shorturl' => '...'] on success or ['error' => '...'] on failure.
 */
function shortenWithIsgd($longUrl) {
    if (empty($longUrl)) return ['error' => 'No URL provided.'];

    $parsed = parse_url($longUrl);
    if (empty($parsed['scheme']) || empty($parsed['host'])) {
        return ['error' => 'Invalid URL — must start with http:// or https://'];
    }

    $apiUrl = 'https://is.gd/create.php?format=simple&url=' . urlencode($longUrl);

    // ignore_errors is essential — without it PHP will not return the response body
    // when is.gd sends a 4xx error HTTP status code (per the official is.gd example).
    $ctx = stream_context_create([
        'http' => [
            'ignore_errors'   => true,
            'timeout'         => 10,
            'user_agent'      => 'Newsroom-Creator/1.5',
        ],
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ],
    ]);

    $response = @file_get_contents($apiUrl, false, $ctx);

    if ($response === false || !isset($http_response_header)) {
        error_log('Newsroom Creator — is.gd: failed to reach API for URL: ' . $longUrl);
        return ['error' => 'Could not reach is.gd — check your server outbound internet access.'];
    }

    // Extract HTTP status code
    $httpStatus = 200;
    if (preg_match('{HTTP/\d+\.\d+\s+(\d+)}', $http_response_header[0], $m)) {
        $httpStatus = intval($m[1]);
    }

    $body = trim($response);

    if ($httpStatus === 200 && !empty($body) && strpos($body, 'Error:') !== 0) {
        return ['shorturl' => $body];
    }

    $msg = !empty($body) ? $body : 'Unknown error (HTTP ' . $httpStatus . ')';
    error_log('Newsroom Creator — is.gd error: ' . $msg . ' | URL: ' . $longUrl);
    return ['error' => $msg];
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if ($action === 'login' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($data['password'], $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id']; $_SESSION['username'] = $user['username']; $_SESSION['role'] = $user['role'];
        echo json_encode(['success' => true, 'user' => ['username' => $user['username'], 'role' => $user['role']]]);
    } else echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
    exit;
}

if ($action === 'get_branding') {
    echo json_encode([
        'success'  => true,
        'app_name' => getSetting($db, 'app_name'),
        'app_logo' => getSetting($db, 'app_logo')
    ]);
    exit;
}

if ($action === 'check_auth') {
    if (isset($_SESSION['user_id'])) echo json_encode(['success' => true, 'user' => ['username' => $_SESSION['username'], 'role' => $_SESSION['role']]]);
    else echo json_encode(['success' => false]);
    exit;
}
if ($action === 'logout') { session_destroy(); echo json_encode(['success' => true]); exit; }

checkAuth();
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

switch ($action) {
    Case 'get_articles':
        $isTrash = isset($_GET['trash']) && $_GET['trash'] === '1';
        $limit = isset($_GET['limit']) ? "LIMIT " . intval($_GET['limit']) : "";
        $trashQuery = $isTrash ? "deleted_at IS NOT NULL" : "deleted_at IS NULL";
        
        if ($userRole === 'admin' || $userRole === 'editor') {
            $stmt = $db->query("SELECT * FROM articles WHERE $trashQuery ORDER BY updated_at DESC $limit");
        } else {
            $stmt = $db->prepare("SELECT * FROM articles WHERE user_id = ? AND $trashQuery ORDER BY updated_at DESC $limit");
            $stmt->execute([$userId]);
        }
        echo json_encode(['success' => true, 'articles' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        break;

    Case 'save_article':
        $data = json_decode(file_get_contents('php://input'), true);

        // Fields that are always written
        $fields = ['original_content', 'headline', 'article_content', 'social_1', 'social_2', 'hashtags', 'image_suggestion', 'featured_image', 'wp_author', 'wp_status', 'wp_categories', 'wp_tags', 'wp_date', 'img_title', 'img_alt', 'img_caption', 'wp_permalink', 'short_url'];
        $values = [];
        foreach($fields as $f) $values[] = $data[$f] ?? null;

        if (isset($data['id']) && $data['id'] > 0) {
            if (array_key_exists('article_status', $data) && $data['article_status'] !== null) {
                // Explicit status provided — include it in the update
                $values[] = $data['article_status'];
                $values[] = $data['id'];
                $stmt = $db->prepare("UPDATE articles SET original_content=?, headline=?, article_content=?, social_1=?, social_2=?, hashtags=?, image_suggestion=?, featured_image=?, wp_author=?, wp_status=?, wp_categories=?, wp_tags=?, wp_date=?, img_title=?, img_alt=?, img_caption=?, wp_permalink=?, short_url=?, article_status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
            } else {
                // No status provided — preserve whatever is already in the DB
                $values[] = $data['id'];
                $stmt = $db->prepare("UPDATE articles SET original_content=?, headline=?, article_content=?, social_1=?, social_2=?, hashtags=?, image_suggestion=?, featured_image=?, wp_author=?, wp_status=?, wp_categories=?, wp_tags=?, wp_date=?, img_title=?, img_alt=?, img_caption=?, wp_permalink=?, short_url=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
            }
            $stmt->execute($values);
            $id = $data['id'];
        } else {
            $statusVal = (array_key_exists('article_status', $data) && $data['article_status'] !== null) ? $data['article_status'] : 'draft';
            $values[] = $statusVal;
            array_unshift($values, $userId);
            $stmt = $db->prepare("INSERT INTO articles (user_id, original_content, headline, article_content, social_1, social_2, hashtags, image_suggestion, featured_image, wp_author, wp_status, wp_categories, wp_tags, wp_date, img_title, img_alt, img_caption, wp_permalink, short_url, article_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute($values);
            $id = $db->lastInsertId();
        }
        echo json_encode(['success' => true, 'id' => $id]);
        break;

    Case 'further_action_article':
        // Editor/Admin flags article — sets article_status to 'further_action' so User knows to rework it
        if ($userRole !== 'admin' && $userRole !== 'editor') { echo json_encode(['success' => false, 'error' => 'Unauthorized']); exit; }
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        if ($id > 0) {
            $db->prepare("UPDATE articles SET article_status='further_action', updated_at=CURRENT_TIMESTAMP WHERE id=?")->execute([$id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid article ID']);
        }
        break;

    Case 'resubmit_article':
        // User re-submits — sets article_status to 'review_pending' (back in editor's review queue)
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        if ($id > 0) {
            $db->prepare("UPDATE articles SET article_status='review_pending', updated_at=CURRENT_TIMESTAMP WHERE id=?")->execute([$id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid article ID']);
        }
        break;

    Case 'approve_article':
        // Editor/Admin approves article — sets article_status to 'approved'
        if ($userRole !== 'admin' && $userRole !== 'editor') { echo json_encode(['success' => false, 'error' => 'Unauthorized']); exit; }
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        if ($id > 0) {
            $db->prepare("UPDATE articles SET article_status='approved', updated_at=CURRENT_TIMESTAMP WHERE id=?")->execute([$id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid article ID']);
        }
        break;

    Case 'submit_article':
        // User submits article — sets article_status to 'review_pending'
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        if ($id > 0) {
            $db->prepare("UPDATE articles SET article_status='review_pending', updated_at=CURRENT_TIMESTAMP WHERE id=?")->execute([$id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid article ID']);
        }
        break;

    Case 'trash_article':
        $data = json_decode(file_get_contents('php://input'), true);
        $db->prepare("UPDATE articles SET deleted_at=CURRENT_TIMESTAMP WHERE id=?")->execute([$data['id']]);
        echo json_encode(['success' => true]); break;

    Case 'restore_article':
        $data = json_decode(file_get_contents('php://input'), true);
        $db->prepare("UPDATE articles SET deleted_at=NULL WHERE id=?")->execute([$data['id']]);
        echo json_encode(['success' => true]); break;
        
    Case 'delete_article_permanently':
        $data = json_decode(file_get_contents('php://input'), true);
        $db->prepare("DELETE FROM articles WHERE id=?")->execute([$data['id']]);
        echo json_encode(['success' => true]); break;

    Case 'generate_article':
        $data = json_decode(file_get_contents('php://input'), true);
        $lang = getSetting($db, 'language_variant') ?: 'British';
        $keywords = getSetting($db, 'local_keywords') ?: '';
        $keywordInstruction = '';
        if (!empty(trim($keywords))) {
            $sourceText = strtolower($data['content'] ?? '');
            $kwList = array_filter(array_map('trim', explode(',', $keywords)));
            $presentKws = array_filter($kwList, function($kw) use ($sourceText) { return strpos($sourceText, strtolower($kw)) !== false; });
            if (!empty($presentKws)) {
                $keywordInstruction = " 5. Naturally incorporate these keywords which appear in the source: " . implode(', ', $presentKws) . ".";
            }
        }
        $systemPrompt = "Role: You are an expert digital journalist. Transform the source content into a professional, SEO-optimized news article. Rules: 1. Headline: Short, searchable. 2. Structure: Inverted Pyramid. 3. Active voice, formal third person. $lang English. 4. ONLY USE provided facts.$keywordInstruction Return response as: HEADLINE: [Your Headline]\n\n[Article Body]";
        $result = callAI($db, $systemPrompt, $data['content']);
        if (isset($result['error'])) { echo json_encode(['success' => false, 'error' => $result['error']]); break; }
        
        $parts = explode("\n\n", $result['content'], 2);
        $headline = str_replace(["HEADLINE: ", "**"], "", $parts[0]);
        $body = $parts[1] ?? $headline;
        echo json_encode(['success' => true, 'headline' => trim($headline), 'article_content' => trim($body)]);
        break;

    Case 'spin_article':
        $data = json_decode(file_get_contents('php://input'), true);
        $keywords = getSetting($db, 'local_keywords') ?: '';
        $keywordInstruction = '';
        if (!empty(trim($keywords))) {
            $sourceText = strtolower($data['content'] ?? '');
            $kwList = array_filter(array_map('trim', explode(',', $keywords)));
            $presentKws = array_filter($kwList, function($kw) use ($sourceText) { return strpos($sourceText, strtolower($kw)) !== false; });
            if (!empty($presentKws)) {
                $keywordInstruction = " Also naturally retain these keywords which already appear in the source: " . implode(', ', $presentKws) . ".";
            }
        }
        $systemPrompt = "You are an expert article spinner and copywriter. Rewrite the provided source content to pass plagiarism and copyright checks while retaining the original core meaning. The goal is to produce a completely unique text variation.$keywordInstruction You MUST respond with ONLY a raw JSON object — no markdown, no code fences, no explanation before or after. The JSON must contain exactly two keys: \"headline\" (string: a new, punchy SEO-optimized headline) and \"article_content\" (string: the rewritten article broken into paragraphs separated by \\n\\n). Example format: {\"headline\": \"Your headline here\", \"article_content\": \"Paragraph one.\\n\\nParagraph two.\"}";
        
        $result = callAI($db, $systemPrompt, $data['content']);
        if (isset($result['error'])) { echo json_encode(['success' => false, 'error' => $result['error']]); break; }
        
        $raw = trim($result['content']);

        // Strategy 1: strip markdown code fences (```json ... ``` or ``` ... ```)
        $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $cleaned = preg_replace('/\s*```\s*$/i', '', $cleaned);
        $cleaned = trim($cleaned);

        // Strategy 2: extract the first {...} JSON object via regex
        if (!$cleaned || $cleaned[0] !== '{') {
            if (preg_match('/\{[\s\S]*\}/u', $raw, $matches)) {
                $cleaned = $matches[0];
            }
        }

        // Strategy 3: attempt to fix common issues (trailing commas, smart quotes)
        $cleaned = preg_replace('/,\s*([\}\]])/u', '$1', $cleaned);           // remove trailing commas
        $cleaned = str_replace(["\u{201C}", "\u{201D}", "\u{2018}", "\u{2019}"], ['"', '"', "'", "'"], $cleaned); // smart quotes

        $json = json_decode($cleaned, true);

        if ($json && isset($json['headline']) && isset($json['article_content'])) {
            echo json_encode(['success' => true, 'headline' => $json['headline'], 'article_content' => $json['article_content']]);
        } else {
            // Strategy 4: fallback — parse as plain text using the generate_article format
            $parts = explode("\n\n", $raw, 2);
            $fallbackHeadline = trim(str_replace(["HEADLINE: ", "**"], "", $parts[0]));
            $fallbackBody     = isset($parts[1]) ? trim($parts[1]) : $raw;
            if (strlen($fallbackHeadline) > 5 && strlen($fallbackBody) > 20) {
                echo json_encode(['success' => true, 'headline' => $fallbackHeadline, 'article_content' => $fallbackBody]);
            } else {
                echo json_encode(['success' => false, 'error' => 'AI returned an unexpected format. Raw: ' . substr($raw, 0, 200)]);
            }
        }
        break;

    Case 'generate_social':
        $data = json_decode(file_get_contents('php://input'), true);

        // --- 1. Resolve the shortlink (DB is authoritative; client value is fallback) ---
        $short_url = '';
        $artId = intval($data['article_id'] ?? 0);

        if ($artId > 0) {
            // Always query the DB — it's the authoritative source after a WP push
            $artStmt = $db->prepare("SELECT short_url, wp_permalink FROM articles WHERE id = ?");
            $artStmt->execute([$artId]);
            $artRow = $artStmt->fetch(PDO::FETCH_ASSOC);

            if ($artRow && !empty($artRow['short_url'])) {
                // Already have a stored shortlink — use it directly
                $short_url = trim($artRow['short_url']);
            } elseif ($artRow && !empty($artRow['wp_permalink'])) {
                // Have a permalink but no shortlink yet — create one via is.gd
                $permalink = $artRow['wp_permalink'];
                // Only shorten real public permalinks (not draft ?p=123 preview URLs)
                if (strpos($permalink, '?p=') === false) {
                    $isgd = shortenWithIsgd($permalink);
                    $short_url = $isgd['shorturl'] ?? '';
                    if (!empty($short_url)) {
                        $db->prepare("UPDATE articles SET short_url = ? WHERE id = ?")
                           ->execute([$short_url, $artId]);
                    }
                }
            }
        }

        // Client-supplied value as final fallback (e.g. article_id not sent)
        if (empty($short_url) && !empty($data['short_url'])) {
            $short_url = trim($data['short_url']);
        }

        // --- 2. Ask the AI to write the posts (no URL in content — we add it deterministically) ---
        $prompt = "Carefully scan and analyze the final, edited text of this article below. Based strictly on this final content, do the following:\n1. Write Post 1 strictly between 25 and 100 characters long (plain text only, no URLs).\n2. Write Post 2 strictly under 280 characters long (plain text only, no URLs).\n3. Generate up to 20 of the best hashtags.\n4. Suggest a highly descriptive image search prompt (max 15 words) for a stock photo editor.\nRespond with ONLY a raw JSON object, no markdown fences:\n{\"post1\": \"text\", \"post2\": \"text\", \"hashtags\": \"#tag1 #tag2\", \"image_suggestion\": \"prompt text\"}\nArticle:\n" . ($data['article_content'] ?? '');
        $result = callAI($db, "You are a social media manager. Respond with only raw JSON, no code fences, no explanation.", $prompt);
        if (isset($result['error'])) { echo json_encode(['success' => false, 'error' => $result['error']]); break; }

        $raw     = trim($result['content']);
        $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $cleaned = preg_replace('/\s*```\s*$/i', '', $cleaned);
        $cleaned = trim($cleaned);
        if (!$cleaned || $cleaned[0] !== '{') {
            if (preg_match('/\{[\s\S]*\}/u', $raw, $matches)) $cleaned = $matches[0];
        }
        $cleaned = preg_replace('/,\s*([\}\]])/u', '$1', $cleaned);
        $cleaned = str_replace(["\u{201C}", "\u{201D}", "\u{2018}", "\u{2019}"], ['"', '"', "'", "'"], $cleaned);
        $json    = json_decode($cleaned, true);

        $post1 = isset($json['post1']) ? trim($json['post1']) : '';
        $post2 = isset($json['post2']) ? trim($json['post2']) : '';

        // --- 3. Deterministically append shortlink to BOTH posts ---
        if (!empty($short_url)) {
            // Strip any URL the AI may have hallucinated to avoid duplicates
            $post1 = trim(preg_replace('/https?:\/\/\S+/i', '', $post1));
            $post2 = trim(preg_replace('/https?:\/\/\S+/i', '', $post2));
            $post1 = ($post1 !== '' ? $post1 : '') . "\n\n" . $short_url;
            $post2 = ($post2 !== '' ? $post2 : '') . "\n\n" . $short_url;
        }

        echo json_encode([
            'success'          => true,
            'social_1'         => $post1,
            'social_2'         => $post2,
            'hashtags'         => $json['hashtags'] ?? '',
            'image_suggestion' => $json['image_suggestion'] ?? '',
            'short_url'        => $short_url
        ]);
        break;

    Case 'generate_tags':
        if ($userRole !== 'admin' && $userRole !== 'editor') { echo json_encode(['success' => false, 'error' => 'Unauthorized']); exit; }
        $data = json_decode(file_get_contents('php://input'), true);
        $content = trim($data['article_content'] ?? '');
        $headline = trim($data['headline'] ?? '');
        if (empty($content) && empty($headline)) { echo json_encode(['success' => false, 'error' => 'No article content to analyse.']); break; }

        $systemPrompt = "You are an SEO expert. Analyse the article and generate a list of relevant WordPress tags. Return ONLY a raw JSON object with one key: \"tags\" — a comma-separated string of tags (no hashtags, no numbering, just plain words or short phrases). Example: {\"tags\": \"local news, council, planning, housing\"}. Generate between 5 and 15 tags.";
        $userPrompt = "Headline: {$headline}\n\n{$content}";
        $result = callAI($db, $systemPrompt, $userPrompt);
        if (isset($result['error'])) { echo json_encode(['success' => false, 'error' => $result['error']]); break; }

        $raw = trim($result['content']);
        $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $cleaned = preg_replace('/\s*```\s*$/i', '', $cleaned);
        $cleaned = trim($cleaned);
        if (!$cleaned || $cleaned[0] !== '{') {
            if (preg_match('/\{[\s\S]*\}/u', $raw, $matches)) $cleaned = $matches[0];
        }
        $cleaned = preg_replace('/,\s*([\}\]])/u', '$1', $cleaned);
        $json = json_decode($cleaned, true);
        if ($json && isset($json['tags'])) {
            echo json_encode(['success' => true, 'tags' => $json['tags']]);
        } else {
            // Fallback: treat the whole response as a tag list if it looks like CSV
            $fallback = strip_tags($raw);
            echo json_encode(['success' => true, 'tags' => trim($fallback, " \t\n\r,")]);
        }
        break;

    Case 'check_spelling':
        $data = json_decode(file_get_contents('php://input'), true);
        $text = $data['article_content'] ?? '';
        if (empty(trim($text))) { echo json_encode(['success' => true, 'errors' => []]); break; }

        $lang = getSetting($db, 'language_variant') ?: 'British';
        $systemPrompt = "You are an expert proofreader. Analyze the provided article for spelling mistakes and distinct deviations from $lang English standards. Output ONLY a raw valid JSON array of objects. Each object should have 'word' (the misspelled word), 'suggestion' (the correct $lang English replacement), and 'context' (a short selection of surrounding text). If there are no errors, return exactly an empty array: []";

        $result = callAI($db, $systemPrompt, $text);
        if (isset($result['error'])) { echo json_encode(['success' => false, 'error' => $result['error']]); break; }

        $raw = trim($result['content']);
        $cleaned = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $cleaned = preg_replace('/\s*```\s*$/i', '', $cleaned);
        $cleaned = trim($cleaned);
        if (!$cleaned || ($cleaned[0] !== '[' && $cleaned[0] !== '{')) {
            if (preg_match('/\[[\s\S]*\]/u', $raw, $matches)) $cleaned = $matches[0];
        }
        $cleaned = preg_replace('/,\s*([\}\]])/u', '$1', $cleaned);
        $json = json_decode($cleaned, true);
        echo json_encode(['success' => true, 'errors' => is_array($json) ? $json : []]);
        break;

    Case 'get_wp_meta':
        $wpUrl = rtrim(getSetting($db, 'wp_site_url') ?: '', '/');
        $wpApiKey = getSetting($db, 'wp_api_key') ?: '';
        if (!$wpUrl || !$wpApiKey) { echo json_encode(['success' => false, 'error' => 'WordPress not configured.']); break; }
        
        $ch = curl_init($wpUrl . '/wp-json/newsroom-creator/v1/meta');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Newsroom-API-Key: ' . $wpApiKey]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response) echo $response;
        else echo json_encode(['success' => false]);
        break;

    Case 'push_to_wordpress':
        $data = json_decode(file_get_contents('php://input'), true);
        $wpUrl = rtrim(getSetting($db, 'wp_site_url') ?: '', '/');
        $wpApiKey = getSetting($db, 'wp_api_key') ?: '';

        if (!$wpUrl || !$wpApiKey) { echo json_encode(['success' => false, 'error' => 'WordPress is not configured.']); break; }

        $paragraphs = array_filter(array_map('trim', explode("\n\n", $data['article_content'])));
        $htmlParts = array();
        foreach ($paragraphs as $p) {
            $htmlParts[] = '<p>' . nl2br(esc_html_shim($p)) . '</p>';
        }
        $htmlBody = implode('', $htmlParts);

        $payload = [
            'title' => $data['headline'],
            'content' => $htmlBody,
            'featured_image_base64' => $data['featured_image'] ?? null,
            'img_title' => $data['img_title'] ?? null,
            'img_alt' => $data['img_alt'] ?? null,
            'img_caption' => $data['img_caption'] ?? null,
            'status' => $data['wp_status'] ?? 'draft',
            'author' => $data['wp_author'] ?? null,
            'categories' => json_decode($data['wp_categories'] ?? '[]', true),
            'tags' => $data['wp_tags'] ?? null,
            'date' => $data['wp_date'] ?? null
        ];
        
        if (isset($data['wp_post_id']) && $data['wp_post_id'] > 0) $payload['post_id'] = $data['wp_post_id'];

        $ch = curl_init($wpUrl . '/wp-json/newsroom-creator/v1/create-draft');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'X-Newsroom-API-Key: ' . $wpApiKey]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            echo json_encode(['success' => false, 'error' => 'Could not reach WordPress site: ' . $curlErr]);
            break;
        }

        $result = json_decode($response, true);
        
        if ($httpCode >= 400 || !is_array($result) || !isset($result['success'])) {
            $msg = is_array($result) && isset($result['message']) ? $result['message'] : 'Unexpected WP response (HTTP ' . $httpCode . '): ' . substr(strip_tags($response), 0, 200);
            echo json_encode(['success' => false, 'error' => $msg]);
            break;
        }

        if (($httpCode === 201 || $httpCode === 200) && isset($result['post_id'])) {
            $currentTime = date('Y-m-d H:i:s');
            
            $permalink = $result['permalink'] ?? '';
            $short_url = '';

            // Only attempt to shorten a real public permalink (not a ?p=123 draft preview URL)
            if (!empty($permalink) && strpos($permalink, '?p=') === false) {
                $isgd = shortenWithIsgd($permalink);
                $short_url = $isgd['shorturl'] ?? '';
            }
            
            if (isset($data['id']) && $data['id'] > 0) {
                $db->prepare("UPDATE articles SET wp_pushed_at = ?, wp_post_id = ?, push_count = push_count + 1, wp_permalink = ?, short_url = ?, article_status = 'approved' WHERE id = ?")
                   ->execute([$currentTime, $result['post_id'], $permalink, $short_url, $data['id']]);
            }
            
            $stmt = $db->prepare("SELECT push_count FROM articles WHERE id = ?");
            $stmt->execute([$data['id']]);
            $new_count = $stmt->fetchColumn() ?: 1;

            echo json_encode([
                'success' => true, 
                'post_id' => $result['post_id'], 
                'edit_url' => $result['edit_url'] ?? '', 
                'wp_pushed_at' => $currentTime, 
                'push_count' => $new_count,
                'permalink' => $permalink,
                'short_url' => $short_url
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => $result['message'] ?? 'Unexpected response']);
        }
        break;

    Case 'create_shortlink':
        if ($userRole !== 'admin' && $userRole !== 'editor') { echo json_encode(['success' => false, 'error' => 'Unauthorized']); exit; }
        $data = json_decode(file_get_contents('php://input'), true);
        $artId = intval($data['article_id'] ?? 0);
        if ($artId <= 0) { echo json_encode(['success' => false, 'error' => 'Invalid article ID']); break; }

        // Fetch current permalink and any existing shortlink
        $artStmt = $db->prepare("SELECT wp_permalink, short_url FROM articles WHERE id = ?");
        $artStmt->execute([$artId]);
        $artRow = $artStmt->fetch(PDO::FETCH_ASSOC);

        if (!$artRow || empty($artRow['wp_permalink'])) {
            echo json_encode(['success' => false, 'error' => 'No WordPress permalink found. Push the article to WordPress first.']);
            break;
        }

        // Return cached shortlink if we already have one
        if (!empty($artRow['short_url'])) {
            echo json_encode(['success' => true, 'short_url' => $artRow['short_url'], 'cached' => true]);
            break;
        }

        $permalink = $artRow['wp_permalink'];
        if (strpos($permalink, '?p=') !== false) {
            echo json_encode(['success' => false, 'error' => 'Short URLs can only be created for published articles. This article is still a draft — publish it in WordPress first, then re-push.']);
            break;
        }

        $short_url = shortenWithIsgd($permalink);
        if (empty($short_url['shorturl'])) {
            $errMsg = $short_url['error'] ?? 'Unknown error from is.gd.';
            echo json_encode(['success' => false, 'error' => 'is.gd: ' . $errMsg]);
            break;
        }

        // Persist the new shortlink
        $db->prepare("UPDATE articles SET short_url = ? WHERE id = ?")->execute([$short_url['shorturl'], $artId]);
        echo json_encode(['success' => true, 'short_url' => $short_url['shorturl'], 'cached' => false]);
        break;

    Case 'get_wp_posts':
        if ($userRole !== 'admin' && $userRole !== 'editor') { echo json_encode(['success' => false, 'error' => 'Unauthorized']); exit; }
        $wpUrl    = rtrim(getSetting($db, 'wp_site_url') ?: '', '/');
        $wpApiKey = getSetting($db, 'wp_api_key') ?: '';
        if (!$wpUrl || !$wpApiKey) { echo json_encode(['success' => false, 'error' => 'WordPress not configured.']); break; }

        $ch = curl_init($wpUrl . '/wp-json/newsroom-creator/v1/posts');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Newsroom-API-Key: ' . $wpApiKey]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response) echo $response;
        else echo json_encode(['success' => false, 'error' => 'Could not reach WordPress.']);
        break;

    Case 'trash_wp_post':
        if ($userRole !== 'admin' && $userRole !== 'editor') { echo json_encode(['success' => false, 'error' => 'Unauthorized']); exit; }
        $data     = json_decode(file_get_contents('php://input'), true);
        $wpUrl    = rtrim(getSetting($db, 'wp_site_url') ?: '', '/');
        $wpApiKey = getSetting($db, 'wp_api_key') ?: '';
        if (!$wpUrl || !$wpApiKey) { echo json_encode(['success' => false, 'error' => 'WordPress not configured.']); break; }

        $ch = curl_init($wpUrl . '/wp-json/newsroom-creator/v1/trash-post');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'X-Newsroom-API-Key: ' . $wpApiKey]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['post_id' => intval($data['post_id'] ?? 0)]));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response) echo $response;
        else echo json_encode(['success' => false, 'error' => 'Could not reach WordPress.']);
        break;

    Case 'get_settings':
        $res = [
            'success' => true,
            'ai_base_url' => getSetting($db, 'ai_base_url'),
            'ai_api_key' => getSetting($db, 'ai_api_key'),
            'ai_model' => getSetting($db, 'ai_model'),
            'language_variant' => getSetting($db, 'language_variant'),
            'wp_site_url' => getSetting($db, 'wp_site_url'),
            'wp_api_key' => getSetting($db, 'wp_api_key'),
            'local_keywords' => getSetting($db, 'local_keywords'),
            'app_name' => getSetting($db, 'app_name'),
            'app_logo' => getSetting($db, 'app_logo')
        ];
        echo json_encode($res);
        break;

    Case 'save_settings':
        if ($userRole !== 'admin') { echo json_encode(['success' => false]); exit; }
        $data = json_decode(file_get_contents('php://input'), true);
        $allowed = ['ai_base_url', 'ai_api_key', 'ai_model', 'language_variant', 'wp_site_url', 'wp_api_key', 'local_keywords'];
        foreach ($allowed as $key) {
            if (isset($data[$key])) {
                $stmt = $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)");
                $stmt->execute([$key, trim($data[$key])]);
            }
        }
        echo json_encode(['success' => true]);
        break;

    Case 'save_customisation':
        if ($userRole !== 'admin') { echo json_encode(['success' => false, 'error' => 'Unauthorized']); exit; }
        $data = json_decode(file_get_contents('php://input'), true);
        $allowed = ['app_name', 'app_logo'];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $stmt = $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)");
                $stmt->execute([$key, $data[$key]]);
            }
        }
        echo json_encode(['success' => true]);
        break;
        
    Case 'test_wp_connection':
        if ($userRole !== 'admin') { echo json_encode(['success' => false, 'error' => 'Unauthorized']); exit; }
        $wpUrl = rtrim(getSetting($db, 'wp_site_url') ?: '', '/');
        $wpApiKey = getSetting($db, 'wp_api_key') ?: '';
        if (!$wpUrl || !$wpApiKey) { echo json_encode(['success' => false, 'error' => 'WordPress URL and API key must be saved first.']); break; }

        $testUrl = $wpUrl . '/wp-json/newsroom-creator/v1/test';
        $ch = curl_init($testUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'X-Newsroom-API-Key: ' . $wpApiKey]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr   = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode === 0) {
            echo json_encode(['success' => false, 'error' => 'Could not reach site: ' . ($curlErr ?: 'No response. Check the URL is correct and reachable.')]);
            break;
        }
        $result = json_decode($response, true);
        if ($httpCode === 200 && isset($result['success']) && $result['success']) echo json_encode(['success' => true]);
        else {
            $detail = is_array($result) && isset($result['message']) ? $result['message'] : substr(strip_tags($response), 0, 120);
            echo json_encode(['success' => false, 'error' => 'Connection failed (HTTP ' . $httpCode . '). ' . $detail]);
        }
        break;

    Case 'get_users':
        if ($userRole !== 'admin') { echo json_encode(['success' => false]); exit; }
        $stmt = $db->query("SELECT id, username, role FROM users");
        echo json_encode(['success' => true, 'users' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        break;
        
    Case 'add_user':
        if ($userRole !== 'admin') { echo json_encode(['success' => false]); exit; }
        $data = json_decode(file_get_contents('php://input'), true);
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
        $stmt->execute([$data['username'], $hash, $data['role']]);
        echo json_encode(['success' => true]);
        break;
        
    Case 'reset_password':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($userRole !== 'admin' && $userId != $data['id']) { echo json_encode(['success' => false]); exit; }
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password_hash=? WHERE id=?");
        $stmt->execute([$hash, $data['id']]);
        echo json_encode(['success' => true]);
        break;

    Case 'delete_user':
        if ($userRole !== 'admin') { echo json_encode(['success' => false]); exit; }
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $db->prepare("DELETE FROM users WHERE id=?");
        $stmt->execute([$data['id']]);
        echo json_encode(['success' => true]);
        break;

    Case 'export_csv':
        $stmt = $db->query("SELECT * FROM articles");
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="newsroom_export.csv"');
        $out = fopen('php://output', 'w');
        if (!empty($articles)) {
            fputcsv($out, array_keys($articles[0]));
            foreach ($articles as $row) fputcsv($out, $row);
        }
        fclose($out);
        exit;

    Case 'backup_json':
        $stmt = $db->query("SELECT * FROM articles");
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="newsroom_backup.json"');
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
        
    Case 'restore_json':
        $data = json_decode(file_get_contents('php://input'), true);
        if (is_array($data)) {
            foreach ($data as $row) {
                if (!empty($row['deleted_at'])) continue;
                $stmt = $db->prepare("INSERT INTO articles (user_id, original_content, headline, article_content, social_1, social_2, image_suggestion) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $row['original_content'], $row['headline'], $row['article_content'], $row['social_1'], $row['social_2'], $row['image_suggestion']]);
            }
        }
        echo json_encode(['success' => true]);
        break;
}
?>
