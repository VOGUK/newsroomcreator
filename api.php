<?php
session_start();
header('Content-Type: application/json');

// --- CONFIGURATION ---
define('DB_FILE', __DIR__ . '/newsroom.sqlite');

// --- DATABASE SETUP ---
try {
    $db = new PDO('sqlite:' . DB_FILE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Auto-create tables
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE,
        password_hash TEXT,
        role TEXT
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS articles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        original_content TEXT,
        headline TEXT,
        article_content TEXT,
        social_1 TEXT,
        social_2 TEXT,
        image_suggestion TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        deleted_at DATETIME DEFAULT NULL
    )");

    // Safe Schema Migrations for existing installations
    try {
        $db->exec("ALTER TABLE articles ADD COLUMN wp_pushed_at DATETIME DEFAULT NULL");
    } catch(PDOException $e) {
        // Column already exists, safe to ignore
    }
    
    try {
        $db->exec("ALTER TABLE articles ADD COLUMN wp_post_id INTEGER DEFAULT NULL");
    } catch(PDOException $e) {
        // Column already exists, safe to ignore
    }

    try {
        $db->exec("ALTER TABLE articles ADD COLUMN hashtags TEXT DEFAULT NULL");
    } catch(PDOException $e) {
        // Column already exists, safe to ignore
    }

    // Settings table for admin-configurable options
    $db->exec("CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT
    )");

    // Seed empty AI settings on first install if not already set
    $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('ai_base_url', '')");
    $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('ai_api_key', '')");
    $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('ai_model', '')");
    
    // Seed default language setting
    $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('language_variant', 'British')");

    // Seed empty WordPress settings on first install
    $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('wp_site_url', '')");
    $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('wp_api_key', '')");

    // Create default admin if no users exist
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('password123', PASSWORD_DEFAULT);
        $db->exec("INSERT INTO users (username, password_hash, role) VALUES ('admin', '$hash', 'admin')");
    }

    // Auto-purge items in recycle bin older than 7 days
    $db->exec("DELETE FROM articles WHERE deleted_at IS NOT NULL AND deleted_at <= datetime('now', '-7 days')");

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// --- HELPER FUNCTIONS ---
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
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

    if (!$baseUrl || !$apiKey || !$model) {
        return ['error' => 'AI provider not configured. Please visit the System page to enter your API details.'];
    }

    $endpoint = $baseUrl . '/chat/completions';

    $ch = curl_init($endpoint);
    
    $data = [
        "model" => $model,
        "messages" => [
            ["role" => "system", "content" => $systemPrompt],
            ["role" => "user", "content" => $userPrompt]
        ]
    ];
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $apiKey,
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($response === false) {
        return ['error' => 'Network/cURL Error: ' . $curlError];
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['error'])) {
        $errorMsg = is_array($result['error']) ? ($result['error']['message'] ?? 'Unknown API Error') : $result['error'];
        return ['error' => 'AI API Error: ' . $errorMsg];
    }
    
    if (!isset($result['choices'][0]['message']['content'])) {
        return ['error' => 'AI provider returned an invalid response format.'];
    }
    
    return ['content' => $result['choices'][0]['message']['content']];
}

// --- ROUTING ---
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if ($action === 'login' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($data['password'], $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        echo json_encode(['success' => true, 'user' => ['username' => $user['username'], 'role' => $user['role']]]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
    }
    exit;
}

if ($action === 'check_auth') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode(['success' => true, 'user' => ['username' => $_SESSION['username'], 'role' => $_SESSION['role']]]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

// All subsequent routes require auth
checkAuth();
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

switch ($action) {
    Case 'get_articles':
        $isTrash = isset($_GET['trash']) && $_GET['trash'] === '1';
        $limit = isset($_GET['limit']) ? "LIMIT " . intval($_GET['limit']) : "";
        $trashQuery = $isTrash ? "deleted_at IS NOT NULL" : "deleted_at IS NULL";
        
        if ($userRole === 'admin') {
            $stmt = $db->prepare("SELECT * FROM articles WHERE $trashQuery ORDER BY updated_at DESC $limit");
            $stmt->execute();
        } else {
            $stmt = $db->prepare("SELECT * FROM articles WHERE user_id = ? AND $trashQuery ORDER BY updated_at DESC $limit");
            $stmt->execute([$userId]);
        }
        echo json_encode(['success' => true, 'articles' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        break;

    Case 'save_article':
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['id']) && $data['id'] > 0) {
            $stmt = $db->prepare("UPDATE articles SET original_content=?, headline=?, article_content=?, social_1=?, social_2=?, hashtags=?, image_suggestion=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
            $stmt->execute([$data['original_content'], $data['headline'], $data['article_content'], $data['social_1'], $data['social_2'], $data['hashtags'], $data['image_suggestion'], $data['id']]);
            $id = $data['id'];
        } else {
            $stmt = $db->prepare("INSERT INTO articles (user_id, original_content, headline, article_content, social_1, social_2, hashtags, image_suggestion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $data['original_content'], $data['headline'], $data['article_content'], $data['social_1'], $data['social_2'], $data['hashtags'], $data['image_suggestion']]);
            $id = $db->lastInsertId();
        }
        echo json_encode(['success' => true, 'id' => $id]);
        break;

    Case 'trash_article':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $db->prepare("UPDATE articles SET deleted_at=CURRENT_TIMESTAMP WHERE id=?");
        $stmt->execute([$data['id']]);
        echo json_encode(['success' => true]);
        break;

    Case 'restore_article':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $db->prepare("UPDATE articles SET deleted_at=NULL WHERE id=?");
        $stmt->execute([$data['id']]);
        echo json_encode(['success' => true]);
        break;
        
    Case 'delete_article_permanently':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $db->prepare("DELETE FROM articles WHERE id=?");
        $stmt->execute([$data['id']]);
        echo json_encode(['success' => true]);
        break;

    Case 'generate_article':
        $data = json_decode(file_get_contents('php://input'), true);
        $lang = getSetting($db, 'language_variant') ?: 'British';
        
        $systemPrompt = "Role: You are an expert digital journalist. Your task is to transform the provided source content into a professional, SEO-optimized online news article. STRICT RULES: 1. Headline: Short, snappy (5-10 words). Specific, searchable. Do NOT include a subtitle. 2. Structure: Inverted Pyramid. First sentence must answer Who, What, When, Where, Why. Second paragraph establishes context. 3. Formatting: Short paragraphs (2-3 sentences). No tables. 4. Tone: Active voice, formal third person. Objective, authoritative. $lang English. No jargon. 5. Content: ONLY USE the provided facts. Keep quotes intact. Attribute statements. 6. Length: 250 - 1750 words. Return response as: HEADLINE: [Your Headline]\n\n[Article Body]";
        
        $result = callAI($db, $systemPrompt, $data['content']);
        
        if (isset($result['error'])) {
            echo json_encode(['success' => false, 'error' => $result['error']]);
            break;
        }
        
        // Parse headline and body safely
        $textContent = $result['content'];
        $parts = explode("\n\n", $textContent, 2);
        
        $headline = str_replace("HEADLINE: ", "", $parts[0]);
        $headline = str_replace("**", "", $headline); // Strip markdown bold markers
        $body = $parts[1] ?? '';
        
        // Failsafe in case the AI provider formats the text unexpectedly
        if (empty($body)) {
            $body = $headline;
            $headline = "Generated Article";
        }
        
        echo json_encode(['success' => true, 'headline' => trim($headline), 'article_content' => trim($body)]);
        break;

    Case 'suggest_image':
        $data = json_decode(file_get_contents('php://input'), true);
        $prompt = "Carefully scan and analyze the final, edited text of this article below. Based on this final content, suggest a single, highly descriptive image search prompt (max 15 words) that a photo editor could use to find a perfectly relevant stock photo:\n\n" . $data['article_content'];
        
        $result = callAI($db, "You are an expert photo editor.", $prompt);
        
        if (isset($result['error'])) {
            echo json_encode(['success' => false, 'error' => $result['error']]);
        } else {
            echo json_encode(['success' => true, 'suggestion' => trim($result['content'])]);
        }
        break;

    Case 'generate_social':
        $data = json_decode(file_get_contents('php://input'), true);
        
        $prompt = "Carefully scan and analyze the final, edited text of this article below. Based strictly on this final content, write TWO social media posts and a set of hashtags. \n" . 
                  "1. Post 1 MUST be strictly between 25 and 100 characters long. \n" . 
                  "2. Post 2 MUST be strictly under 300 characters long. \n" . 
                  "3. DO NOT include any URLs, web addresses, or link placeholders in either post. \n" . 
                  "4. Generate up to 20 of the best hashtags for this article, optimised for SEO and social media discoverability across platforms and AI searches. Include a mix of broad and niche hashtags. Return them space-separated with # prefix (e.g. #news #tech). \n" .
                  "Format strictly as JSON: {\"post1\": \"text\", \"post2\": \"text\", \"hashtags\": \"#tag1 #tag2 ...\"}. \n" . 
                  "Article: " . $data['article_content'];
        
        $result = callAI($db, "You are a social media manager and SEO expert. Output strictly in JSON format.", $prompt);
        
        if (isset($result['error'])) {
            echo json_encode(['success' => false, 'error' => $result['error']]);
            break;
        }
        
        $cleanContent = $result['content'];
        // Strip markdown json fences if present
        $cleanContent = preg_replace('/^```json\s*/i', '', trim($cleanContent));
        $cleanContent = preg_replace('/
```\s*$/', '', trim($cleanContent));
        
        $json = json_decode(trim($cleanContent), true);
        echo json_encode(['success' => true, 'social_1' => $json['post1'] ?? '', 'social_2' => $json['post2'] ?? '', 'hashtags' => $json['hashtags'] ?? '']);
        break;

    Case 'check_spelling':
        $data = json_decode(file_get_contents('php://input'), true);
        $text = $data['article_content'] ?? '';
        if (empty(trim($text))) {
            echo json_encode(['success' => true, 'errors' => []]);
            break;
        }

        $lang = getSetting($db, 'language_variant') ?: 'British';
        $systemPrompt = "You are an expert proofreader. Analyze the provided article for spelling mistakes and distinct deviations from $lang English standards (e.g., if British, it should highlight 'color' and suggest 'colour'; if American, it should highlight 'colour' and suggest 'color'). " . 
                       "Output ONLY a raw valid JSON array of objects without any markdown blocks or formatting. Each object should have 'word' (the misspelled word), 'suggestion' (the correct $lang English replacement), and 'context' (a short selection of surrounding text). If there are no errors, return exactly an empty array: []";

        $result = callAI($db, $systemPrompt, $text);
        if (isset($result['error'])) {
            echo json_encode(['success' => false, 'error' => $result['error']]);
            break;
        }

        $cleanContent = $result['content'];
        $cleanContent = preg_replace('/^```json\s*/i', '', trim($cleanContent));
        $cleanContent = preg_replace('/```\s*$/', '', trim($cleanContent));
        
        $json = json_decode(trim($cleanContent), true);
        echo json_encode(['success' => true, 'errors' => is_array($json) ? $json : []]);
        break;

    // --- SYSTEM & ADMIN ROUTES ---
    Case 'get_settings':
        if ($userRole !== 'admin') { echo json_encode(['success' => false]); exit; }
        echo json_encode([
            'success' => true,
            'ai_base_url' => getSetting($db, 'ai_base_url'),
            'ai_api_key' => getSetting($db, 'ai_api_key'),
            'ai_model' => getSetting($db, 'ai_model'),
            'language_variant' => getSetting($db, 'language_variant'),
            'wp_site_url' => getSetting($db, 'wp_site_url'),
            'wp_api_key' => getSetting($db, 'wp_api_key'),
        ]);
        break;

    Case 'save_settings':
        if ($userRole !== 'admin') { echo json_encode(['success' => false]); exit; }
        $data = json_decode(file_get_contents('php://input'), true);
        $allowed = ['ai_base_url', 'ai_api_key', 'ai_model', 'language_variant', 'wp_site_url', 'wp_api_key'];
        foreach ($allowed as $key) {
            if (isset($data[$key])) {
                $stmt = $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)");
                $stmt->execute([$key, trim($data[$key])]);
            }
        }
        echo json_encode(['success' => true]);
        break;

    Case 'test_wp_connection':
        if ($userRole !== 'admin') { echo json_encode(['success' => false, 'error' => 'Unauthorized']); exit; }

        $wpUrl    = rtrim(getSetting($db, 'wp_site_url') ?: '', '/');
        $wpApiKey = getSetting($db, 'wp_api_key') ?: '';

        if (!$wpUrl || !$wpApiKey) {
            echo json_encode(['success' => false, 'error' => 'WordPress is not configured. Please enter a site URL and API key.']);
            break;
        }

        $endpoint = $wpUrl . '/wp-json/newsroom-creator/v1/test';

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Newsroom-API-Key: ' . $wpApiKey,
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response  = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            echo json_encode(['success' => false, 'error' => 'Could not reach WordPress: ' . $curlError]);
            break;
        }

        $result = json_decode($response, true);

        if ($httpCode === 200 && isset($result['success']) && $result['success']) {
            echo json_encode(['success' => true]);
        } elseif ($httpCode === 401 || $httpCode === 403) {
            echo json_encode(['success' => false, 'error' => 'Site reachable but API key was rejected (HTTP ' . $httpCode . '). Please check the key.']);
        } elseif ($httpCode === 404) {
            echo json_encode(['success' => false, 'error' => 'WordPress site reachable but the Newsroom Creator plugin does not appear to be installed or activated (HTTP 404).']);
        } else {
            $errMsg = $result['message'] ?? ('Unexpected response from WordPress (HTTP ' . $httpCode . ').');
            echo json_encode(['success' => false, 'error' => $errMsg]);
        }
        break;

    Case 'push_to_wordpress':
        $data = json_decode(file_get_contents('php://input'), true);

        $wpUrl = rtrim(getSetting($db, 'wp_site_url') ?: '', '/');
        $wpApiKey = getSetting($db, 'wp_api_key') ?: '';

        if (!$wpUrl || !$wpApiKey) {
            echo json_encode(['success' => false, 'error' => 'WordPress is not configured. Please visit System Settings.']);
            break;
        }

        $headline = $data['headline'] ?? '';
        $body = $data['article_content'] ?? '';

        if (!$headline || !$body) {
            echo json_encode(['success' => false, 'error' => 'Headline and article content are required.']);
            break;
        }
        
        // Fetch existing wp_post_id if the article has been pushed before
        $wp_post_id = null;
        if (isset($data['id']) && $data['id'] > 0) {
            $stmt = $db->prepare("SELECT wp_post_id FROM articles WHERE id = ?");
            $stmt->execute([$data['id']]);
            $wp_post_id = $stmt->fetchColumn();
        }

        // Convert plain-text article body to simple HTML paragraphs
        $paragraphs = array_filter(array_map('trim', explode("\n\n", $body)));
        $htmlBody = implode('', array_map(fn($p) => '<p>' . nl2br(htmlspecialchars($p)) . '</p>', $paragraphs));

        $endpoint = $wpUrl . '/wp-json/newsroom-creator/v1/create-draft';
        
        $payload = [
            'title' => $headline,
            'content' => $htmlBody,
        ];
        
        // Include the post_id so the plugin can overwrite the existing draft
        if ($wp_post_id) {
            $payload['post_id'] = $wp_post_id;
        }

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Newsroom-API-Key: ' . $wpApiKey,
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            echo json_encode(['success' => false, 'error' => 'Could not reach WordPress: ' . $curlError]);
            break;
        }

        $result = json_decode($response, true);

        // Accept both 201 (Created) and 200 (OK/Updated)
        if (($httpCode === 201 || $httpCode === 200) && isset($result['post_id'])) {
            $currentTime = date('Y-m-d H:i:s');
            if (isset($data['id']) && $data['id'] > 0) {
                $stmt = $db->prepare("UPDATE articles SET wp_pushed_at = ?, wp_post_id = ? WHERE id = ?");
                $stmt->execute([$currentTime, $result['post_id'], $data['id']]);
            }
            echo json_encode([
                'success' => true,
                'post_id' => $result['post_id'],
                'edit_url' => $result['edit_url'] ?? '',
                'wp_pushed_at' => $currentTime
            ]);
        } else {
            $errMsg = $result['message'] ?? ('Unexpected response (HTTP ' . $httpCode . ')');
            echo json_encode(['success' => false, 'error' => $errMsg]);
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
                // Skip articles that were in the recycle bin
                if (!empty($row['deleted_at'])) continue;
                $stmt = $db->prepare("INSERT INTO articles (user_id, original_content, headline, article_content, social_1, social_2, image_suggestion) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $row['original_content'], $row['headline'], $row['article_content'], $row['social_1'], $row['social_2'], $row['image_suggestion']]);
            }
        }
        echo json_encode(['success' => true]);
        break;
}
?>
