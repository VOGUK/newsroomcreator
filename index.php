<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsroom Creator</title>
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        :root {
            --bg-color: #f4f6f8;
            --surface-color: #ffffff;
            --text-color: #1a1a1a;
            --text-muted: #666666;
            --primary-color: #2563eb;
            --danger-color: #dc2626;
            --border-color: #e5e7eb;
            --font-size: 16px;
        }
        [data-theme="dark"] {
            --bg-color: #111827;
            --surface-color: #1f2937;
            --text-color: #f9fafb;
            --text-muted: #9ca3af;
            --border-color: #374151;
            --primary-color: #3b82f6;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            font-size: var(--font-size);
            margin: 0; padding: 0;
            transition: all 0.3s ease;
        }

        /* --- Layout --- */
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .hidden { display: none !important; }
        
        /* --- Navigation --- */
        nav {
            background-color: var(--surface-color);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem;
            position: sticky; top: 0; z-index: 100;
        }
        .nav-inner {
            display: flex; justify-content: space-between; align-items: center;
            max-width: 1200px; margin: 0 auto;
        }
        .nav-brand { display: flex; align-items: center; gap: 10px; font-weight: bold; font-size: 1.2rem; }
        .nav-links { display: flex; gap: 15px; align-items: center; }
        .nav-links button { background: none; border: none; color: var(--text-color); cursor: pointer; display: flex; align-items: center; gap: 5px;}
        
        @media (max-width: 768px) {
            .nav-links {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                flex-shrink: 1;
                min-width: 0;
                padding-bottom: 2px;
            }
            .nav-links::-webkit-scrollbar { display: none; }
            .nav-links button { flex-shrink: 0; white-space: nowrap; }
        }
        
        /* --- UI Elements --- */
        .card {
            background: var(--surface-color); border: 1px solid var(--border-color);
            border-radius: 8px; padding: 20px; margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;}
        
        button {
            background: var(--primary-color); color: white; border: none;
            padding: 8px 16px; border-radius: 6px; cursor: pointer;
            font-size: 0.9rem; display: inline-flex; align-items: center; gap: 5px;
        }
        button:hover { opacity: 0.9; }
        button.secondary { background: var(--surface-color); color: var(--text-color); border: 1px solid var(--border-color); }
        button.danger { background: var(--danger-color); }
        button.icon-btn { padding: 6px; background: transparent; color: var(--text-color); border: 1px solid transparent;}
        button.icon-btn:hover { border-color: var(--border-color); background: var(--bg-color); }
        
        input, textarea, select {
            width: 100%; padding: 10px; margin-bottom: 15px;
            background: var(--bg-color); color: var(--text-color);
            border: 1px solid var(--border-color); border-radius: 6px;
            box-sizing: border-box; font-family: inherit; font-size: inherit;
        }
        textarea { resize: vertical; min-height: 150px; }
        #edit-original, #edit-content { min-height: 500px; }
        
        /* --- Grids --- */
        .compare-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .list-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color); }
        .list-item-actions { display: flex; gap: 10px; }
        
        @media (max-width: 768px) {
            .compare-grid { grid-template-columns: 1fr; }
            .user-add-grid { grid-template-columns: 1fr 1fr !important; }
            .user-add-grid > div:last-child { grid-column: span 2; }
        }

        /* --- Login Box --- */
        #login-view { display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-box { max-width: 400px; width: 100%; text-align: center; }

        /* --- Custom Popups & Modals --- */
        #toast-container {
            position: fixed; bottom: 20px; right: 20px; z-index: 9999;
            display: flex; flex-direction: column; gap: 10px; pointer-events: none;
        }
        .toast {
            background: var(--surface-color); color: var(--text-color);
            border: 1px solid var(--border-color); border-radius: 8px;
            padding: 12px 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex; align-items: center; gap: 10px;
            transform: translateX(120%); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .toast.show { transform: translateX(0); }
        .toast.error { border-left: 4px solid var(--danger-color); }
        .toast.success { border-left: 4px solid #10b981; }

        #modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 9998;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: opacity 0.2s ease;
        }
        #modal-overlay.show { opacity: 1; pointer-events: all; }
        .modal-box {
            background: var(--surface-color); border: 1px solid var(--border-color);
            border-radius: 8px; padding: 24px; max-width: 400px; width: 90%;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            transform: translateY(-20px); transition: transform 0.2s ease;
        }
        #modal-overlay.show .modal-box { transform: translateY(0); }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    </style>
</head>
<body>

    <div id="toast-container"></div>

    <div id="modal-overlay">
        <div class="modal-box">
            <h3 id="modal-title" style="margin-top:0;">Notice</h3>
            <p id="modal-msg"></p>
            <input type="text" id="modal-input" class="hidden" style="margin-top: 10px;">
            <div class="modal-actions">
                <button id="modal-cancel" class="secondary">Cancel</button>
                <button id="modal-confirm">Confirm</button>
            </div>
        </div>
    </div>

    <div id="login-view" class="container">
        <div class="card login-box">
            <i data-feather="edit-3" style="width:48px;height:48px;margin-bottom:10px;"></i>
            <h2>Newsroom Creator</h2>
            <input type="text" id="login-user" placeholder="Username">
            <input type="password" id="login-pass" placeholder="Password" onkeydown="if(event.key==='Enter') login()">
            <button onclick="login()" style="width:100%; justify-content:center;">Login</button>
            <p id="login-error" style="color:var(--danger-color); font-size:0.9rem;"></p>
        </div>
    </div>

    <div id="app-view" class="hidden">
        <nav>
            <div class="nav-inner">
                <div class="nav-brand">
                    <i data-feather="edit-3"></i> Newsroom Creator
                </div>
                <div class="nav-links">
                    <button onclick="switchView('dashboard')"><i data-feather="home"></i> <span>Dashboard</span></button>
                    <button onclick="switchView('list')"><i data-feather="list"></i> <span>Articles</span></button>
                    <button onclick="switchView('recycle')"><i data-feather="trash-2"></i> <span>Bin</span></button>
                    <button onclick="switchView('system')"><i data-feather="settings"></i> <span>System</span></button>
                    
                    <div style="width:1px; height:20px; background:var(--border-color); margin: 0 10px;"></div>
                    
                    <button onclick="adjustTextSize(1)" class="icon-btn" title="Increase Text"><i data-feather="zoom-in"></i></button>
                    <button onclick="adjustTextSize(-1)" class="icon-btn" title="Decrease Text"><i data-feather="zoom-out"></i></button>
                    <button onclick="toggleTheme()" class="icon-btn" id="theme-btn" title="Toggle Light/Dark"><i data-feather="moon"></i></button>
                    <button onclick="logout()" class="icon-btn" title="Logout"><i data-feather="log-out"></i></button>
                </div>
            </div>
        </nav>

        <div class="container">
            <div id="dashboard" class="view hidden">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                    <h2>Recent Articles</h2>
                    <button onclick="createNewArticle()"><i data-feather="plus"></i> New Article</button>
                </div>
                <div class="card">
                    <div id="recent-articles-list">Loading...</div>
                </div>
            </div>

            <div id="editor" class="view hidden">
                <div class="card-header">
                    <h2>Article Editor</h2>
                    <button onclick="switchView('dashboard')" class="secondary">Back</button>
                </div>
                
                <div class="compare-grid">
                    <div class="card">
                        <h3>Original Content</h3>
                        <p class="text-muted" style="font-size:0.85rem">Paste press releases, emails, or social media content here.</p>
                        <textarea id="edit-original" placeholder="Paste source material here..."></textarea>
                        <button onclick="generateArticle()" id="btn-generate">
                            <i data-feather="cpu"></i> Generate Article
                        </button>
                    </div>

                    <div class="card">
                        <div id="editor-wp-badge" class="hidden" style="background:#10b981; color:white; padding:8px 12px; border-radius:6px; font-size:0.85rem; font-weight:bold; margin-bottom:15px; text-align:center;"></div>
                        <h3>Generated Article</h3>
                        <input type="text" id="edit-headline" placeholder="Headline...">
                        <textarea id="edit-content" style="margin-bottom: 5px;"></textarea>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <div id="article-stats" class="text-muted" style="font-size:0.85rem;">0 words | 0 chars | ~0 min read (out loud)</div>
                            <button onclick="checkSpelling()" id="btn-spellcheck" style="background-color: #f97316; color: white; border: none;">
                                <i data-feather="check-square"></i> Check Spelling
                            </button>
                        </div>

                        <div style="margin-top:15px; border-top: 1px solid var(--border-color); padding-top:15px; display:flex; gap:10px; flex-wrap:wrap;">
                            <button onclick="suggestImage()" class="secondary"><i data-feather="image"></i> Suggest Image</button>
                        </div>
                        <p id="edit-image-sug" class="text-muted" style="font-size:0.9rem; margin:10px 0;"></p>
                        <div id="spellcheck-results" class="hidden" style="margin-top:10px; font-size:0.9rem;"></div>
                        
                        <div style="margin-top:15px; border-top: 1px solid var(--border-color); padding-top:15px;">
                            <button onclick="generateSocial()" class="secondary"><i data-feather="share-2"></i> Create Social Content</button>
                            <textarea id="edit-social1" style="min-height:60px; margin-top:10px;" placeholder="Post 1 (< 100 chars)"></textarea>
                            <textarea id="edit-social2" style="min-height:80px;" placeholder="Post 2 (< 300 chars)"></textarea>
                            
                            <div style="margin-top:10px;">
                                <textarea id="edit-hashtags" style="min-height:80px; font-size:0.85rem;" placeholder="Hashtags will appear here after generating social content..."></textarea>
                            </div>
                        </div>

                        <div style="margin-top:20px; display:flex; gap:10px; flex-wrap:wrap;">
                            <button onclick="saveArticle()"><i data-feather="save"></i> Save</button>
                            <button onclick="pushToWordPress()" id="btn-wp-push" style="background-color: #10b981; color: white; border: none;"><i data-feather="upload-cloud"></i> Push to WordPress</button>
                            <button onclick="trashCurrentArticle()" class="danger"><i data-feather="trash"></i> Delete</button>
                        </div>
                        <div style="margin-top:10px; display:flex; gap:10px; flex-wrap:wrap;">
                            <button onclick="copyToClipboard('article')" class="secondary"><i data-feather="copy"></i> Copy Article</button>
                            <button onclick="copyToClipboard('social')" class="secondary"><i data-feather="copy"></i> Copy Socials</button>
                            <button onclick="copyHashtags()" class="secondary"><i data-feather="hash"></i> Copy Hashtags</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="list" class="view hidden">
                <div class="card">
                    <div class="card-header">
                        <h2>All Saved Articles</h2>
                        <select id="sort-select" style="width:auto; margin:0;" onchange="loadArticles()">
                            <option value="date">Sort by Date</option>
                            <option value="alpha">Sort Alphabetically</option>
                        </select>
                    </div>
                    <div id="all-articles-list"></div>
                </div>
            </div>

            <div id="recycle" class="view hidden">
                <div class="card">
                    <div class="card-header">
                        <h2>Recycle Bin (7 Days)</h2>
                    </div>
                    <p class="text-muted" style="font-size:0.9rem">Items here will be permanently erased after 7 days.</p>
                    <div id="recycle-list"></div>
                </div>
            </div>

            <div id="system" class="view hidden">
                
                <div class="card admin-only">
                    <h2>AI Provider Settings (Admin)</h2>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:15px;">Configure the AI service used for all article generation. Any OpenAI-compatible API is supported.</p>
                    <label style="font-size:0.85rem; font-weight:600;">Base URL</label>
                    <input type="text" id="ai-base-url" placeholder="e.g. https://api.openai.com/v1">
                    <label style="font-size:0.85rem; font-weight:600;">API Key</label>
                    <div style="position:relative;">
                        <input type="password" id="ai-api-key" placeholder="Enter your API key" style="padding-right:44px;">
                        <button onclick="toggleApiKeyVisibility()" class="icon-btn" id="btn-toggle-key" title="Show/hide API key" style="position:absolute; right:8px; top:50%; transform:translateY(-60%); margin:0;"><i data-feather="eye"></i></button>
                    </div>
                    <label style="font-size:0.85rem; font-weight:600;">Model Name</label>
                    <input type="text" id="ai-model" placeholder="e.g. gpt-4o-mini">
                    
                    <label style="font-size:0.85rem; font-weight:600;">Language Variant (AI & Spell Checker)</label>
                    <select id="language-variant">
                        <option value="British">British English</option>
                        <option value="American">American English</option>
                    </select>

                    <div style="margin-top:5px; padding: 10px 14px; background:var(--bg-color); border:1px solid var(--border-color); border-radius:6px; font-size:0.8rem; color:var(--text-muted);">
                        <strong>Examples:</strong> OpenAI: <code>https://api.openai.com/v1</code> &nbsp;|&nbsp; OpenRouter: <code>https://openrouter.ai/api/v1</code> &nbsp;|&nbsp; Any OpenAI-compatible endpoint is supported.
                    </div>
                    <div style="margin-top:15px;">
                        <button onclick="saveAiSettings()"><i data-feather="save"></i> Save AI Settings</button>
                        <button onclick="testAiSettings()" class="secondary" style="margin-left:10px;" id="btn-test-ai"><i data-feather="zap"></i> Test Connection</button>
                    </div>
                    <p id="ai-test-result" style="font-size:0.85rem; margin-top:10px;"></p>
                </div>

                <div class="card admin-only">
                    <h2>WordPress Integration (Admin)</h2>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:15px;">Connect to your WordPress site so generated articles can be pushed as draft posts for review.</p>
                    <label style="font-size:0.85rem; font-weight:600;">WordPress Site URL</label>
                    <input type="text" id="wp-site-url" placeholder="e.g. https://yoursite.com">
                    <label style="font-size:0.85rem; font-weight:600;">API Key</label>
                    <div style="position:relative;">
                        <input type="password" id="wp-api-key" placeholder="Enter the key from the Newsroom Creator plugin" style="padding-right:44px;">
                        <button onclick="toggleWpKeyVisibility()" class="icon-btn" id="btn-toggle-wp-key" title="Show/hide API key" style="position:absolute; right:8px; top:50%; transform:translateY(-60%); margin:0;"><i data-feather="eye"></i></button>
                    </div>
                    <div style="margin-top:5px; padding: 10px 14px; background:var(--bg-color); border:1px solid var(--border-color); border-radius:6px; font-size:0.8rem; color:var(--text-muted);">
                        Install the <strong>Newsroom Creator</strong> plugin on your WordPress site, then copy the API key it generates into the field above.
                    </div>
                    <div style="margin-top:15px;">
                        <button onclick="saveWpSettings()"><i data-feather="save"></i> Save WordPress Settings</button>
                        <button onclick="testWpSettings()" class="secondary" style="margin-left:10px;" id="btn-test-wp"><i data-feather="zap"></i> Test Connection</button>
                    </div>
                    <p id="wp-test-result" style="font-size:0.85rem; margin-top:10px;"></p>
                </div>

                <div class="card admin-only">
                    <h2>User Management (Admin)</h2>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:15px;">Add new users or manage existing accounts.</p>
                    <div class="user-add-grid" style="display:grid; grid-template-columns: 1fr 1fr auto auto; gap:10px; align-items:start; margin-bottom:15px;">
                        <div>
                            <label style="font-size:0.85rem; font-weight:600;">Username</label>
                            <input type="text" id="new-user-name" placeholder="Username" style="margin-bottom:0;">
                        </div>
                        <div>
                            <label style="font-size:0.85rem; font-weight:600;">Password</label>
                            <input type="password" id="new-user-pass" placeholder="Password" style="margin-bottom:0;">
                        </div>
                        <div>
                            <label style="font-size:0.85rem; font-weight:600;">Role</label>
                            <select id="new-user-role" style="margin-bottom:0;">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div style="padding-top:22px;">
                            <button onclick="addUser()"><i data-feather="user-plus"></i> Add User</button>
                        </div>
                    </div>
                    <div id="user-list"></div>
                </div>

                <div class="card">
                    <h2>Data Backup & Restore</h2>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:15px;">Export your articles or restore from a previous backup.</p>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <button onclick="window.location.href='api.php?action=backup_json'" class="secondary"><i data-feather="download"></i> Backup JSON</button>
                        <button onclick="window.location.href='api.php?action=export_csv'" class="secondary"><i data-feather="file-text"></i> Export CSV</button>
                        <button onclick="document.getElementById('restore-file').click()" class="secondary"><i data-feather="upload"></i> Restore JSON</button>
                        <input type="file" id="restore-file" style="display:none;" accept=".json" onchange="restoreJson(this)">
                    </div>
                </div>

                <div class="card">
                    <h2>Change Password</h2>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:15px;">Update your login password.</p>
                    <label style="font-size:0.85rem; font-weight:600;">New Password</label>
                    <input type="password" id="my-new-pass" placeholder="Enter new password">
                    <button onclick="changeMyPassword()" class="secondary"><i data-feather="key"></i> Update Password</button>
                </div>

            </div>

        </div>
    </div>

    <script>
        let currentUserId = null;
        let currentUserRole = null;
        let currentArticleId = 0;
        let currentWpPostId = null; // Tracks existing WordPress post ID for overwriting
        let textSize = 16;
        let allArticlesData = [];

        // Helper to format timestamps nicely
        function formatDateTimeStr(dateTimeStr) {
            if (!dateTimeStr) return "";
            try {
                const d = new Date(dateTimeStr.replace(/-/g, "/"));
                if (!isNaN(d.getTime())) {
                    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }) + 
                           ' at ' + d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
                }
            } catch(e) {}
            return dateTimeStr;
        }

        // --- Stats Tracking Logic ---
        function updateStats() {
            const text = document.getElementById('edit-content').value || '';
            const charCount = text.length;
            const wordCount = text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
            const readTimeMin = Math.ceil(wordCount / 150); // Typical reading out loud speed ~150 wpm
            document.getElementById('article-stats').innerText = `${wordCount} words | ${charCount} chars | ~${readTimeMin} min read (out loud)`;
        }

        // --- Custom GUI Popups (Toasts & Modals) ---
        function showToast(message, isError = false) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${isError ? 'error' : 'success'}`;
            
            const icon = isError ? '<i data-feather="alert-circle" style="color:var(--danger-color)"></i>' : '<i data-feather="check-circle" style="color:#10b981"></i>';
            toast.innerHTML = `${icon} <span>${message}</span>`;
            
            container.appendChild(toast);
            feather.replace();

            // Trigger animation
            setTimeout(() => toast.classList.add('show'), 10);
            
            // Remove after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300); // wait for exit animation
            }, 3000);
        }

        function showModal(title, message, isPrompt = false, onConfirmCallback) {
            const overlay = document.getElementById('modal-overlay');
            const titleEl = document.getElementById('modal-title');
            const msgEl = document.getElementById('modal-msg');
            const inputEl = document.getElementById('modal-input');
            const btnConfirm = document.getElementById('modal-confirm');
            const btnCancel = document.getElementById('modal-cancel');

            titleEl.innerText = title;
            msgEl.innerText = message;
            
            if (isPrompt) {
                inputEl.classList.remove('hidden');
                inputEl.value = '';
                inputEl.focus();
            } else {
                inputEl.classList.add('hidden');
            }

            const close = () => overlay.classList.remove('show');

            btnCancel.onclick = () => { close(); };
            
            btnConfirm.onclick = () => {
                close();
                if (isPrompt) {
                    onConfirmCallback(inputEl.value);
                } else {
                    onConfirmCallback();
                }
            };

            overlay.classList.add('show');
        }

        // --- Initialization ---
        document.addEventListener('DOMContentLoaded', () => {
            feather.replace();
            checkAuth();
            
            // Load theme
            if (localStorage.getItem('theme') === 'dark') toggleTheme();
        });

        // --- API Calls ---
        async function api(action, method = 'GET', data = null) {
            try {
                const options = { method };
                if (data) {
                    options.headers = { 'Content-Type': 'application/json' };
                    options.body = JSON.stringify(data);
                }
                const res = await fetch(`api.php?action=${action}`, options);
                const text = await res.text(); 
                
                try {
                    return JSON.parse(text); 
                } catch (err) {
                    console.error("Server returned an error instead of JSON:", text);
                    return { success: false, error: "Check console. Server error: " + text.substring(0, 100) };
                }
            } catch (error) {
                console.error("Network or Fetch Error:", error);
                return { success: false, error: "Network error. Are you connected to the internet?" };
            }
        }

        // --- Auth ---
        async function checkAuth() {
            const res = await api('check_auth');
            if (res.success) {
                currentUserId = res.user.id;
                currentUserRole = res.user.role;
                document.getElementById('login-view').classList.add('hidden');
                document.getElementById('app-view').classList.remove('hidden');
                document.querySelectorAll('.admin-only').forEach(el => el.style.display = (currentUserRole === 'admin' ? 'block' : 'none'));
                switchView('dashboard');
            }
        }

        async function login() {
            const u = document.getElementById('login-user').value;
            const p = document.getElementById('login-pass').value;
            const res = await api('login', 'POST', {username: u, password: p});
            if (res.success) checkAuth();
            else document.getElementById('login-error').innerText = "Invalid credentials";
        }

        async function logout() {
            await api('logout');
            location.reload();
        }

        // --- UI & View Management ---
        function switchView(view) {
            document.querySelectorAll('.view').forEach(el => el.classList.add('hidden'));
            document.getElementById(view).classList.remove('hidden');
            if(view === 'dashboard') loadDashboard();
            if(view === 'list') loadArticles();
            if(view === 'recycle') loadRecycleBin();
            if(view === 'system' && currentUserRole === 'admin') { loadUsers(); loadAiSettings(); }
        }

        function toggleTheme() {
            const isDark = document.body.dataset.theme === 'dark';
            document.body.dataset.theme = isDark ? '' : 'dark';
            localStorage.setItem('theme', isDark ? 'light' : 'dark');
            document.getElementById('theme-btn').innerHTML = isDark ? '<i data-feather="moon"></i>' : '<i data-feather="sun"></i>';
            feather.replace();
        }

        function adjustTextSize(change) {
            textSize += (change * 2);
            if (textSize < 12) textSize = 12;
            if (textSize > 24) textSize = 24;
            document.documentElement.style.setProperty('--font-size', textSize + 'px');
        }

        // --- Core Application Logic ---
        async function loadDashboard() {
            const res = await api('get_articles&limit=10');
            renderList(res.articles || [], 'recent-articles-list');
        }

        async function loadArticles() {
            const res = await api('get_articles');
            allArticlesData = res.articles || [];
            
            const sort = document.getElementById('sort-select').value;
            if (sort === 'alpha') {
                allArticlesData.sort((a,b) => (a.headline||'').localeCompare(b.headline||''));
            }
            renderList(allArticlesData, 'all-articles-list');
        }

        async function loadRecycleBin() {
            const res = await api('get_articles&trash=1');
            const container = document.getElementById('recycle-list');
            container.innerHTML = '';
            (res.articles || []).forEach(art => {
                container.innerHTML += `
                    <div class="list-item">
                        <div><strong>${art.headline || 'Untitled'}</strong> <br><small>Deleted: ${art.deleted_at}</small></div>
                        <div class="list-item-actions">
                            <button onclick="restoreArticle(${art.id})" class="secondary icon-btn" title="Restore"><i data-feather="refresh-cw"></i></button>
                            <button onclick="hardDelete(${art.id})" class="danger icon-btn" title="Permanent Delete"><i data-feather="x"></i></button>
                        </div>
                    </div>`;
            });
            feather.replace();
        }

        function renderList(articles, containerId) {
            const container = document.getElementById(containerId);
            container.innerHTML = articles.length === 0 ? '<p class="text-muted">No articles found.</p>' : '';
            articles.forEach(art => {
                let wpLabel = '';
                if (art.wp_pushed_at) {
                    wpLabel = `<span style="background:#10b981; color:white; padding:2px 6px; border-radius:4px; font-size:0.75rem; font-weight:600; margin-left:10px; display:inline-block;">Article pushed on ${formatDateTimeStr(art.wp_pushed_at)}</span>`;
                }
                container.innerHTML += `
                    <div class="list-item">
                        <div><strong>${art.headline || 'Untitled'}</strong> ${wpLabel} <br><small>${art.updated_at}</small></div>
                        <div class="list-item-actions">
                            <button onclick="editArticle(${art.id})" class="secondary icon-btn" title="View/Edit"><i data-feather="edit"></i></button>
                            <button onclick="trashArticle(${art.id})" class="danger icon-btn" title="Trash"><i data-feather="trash-2"></i></button>
                        </div>
                    </div>`;
            });
            feather.replace();
        }

        function createNewArticle() {
            currentArticleId = 0;
            currentWpPostId = null;
            document.getElementById('edit-original').value = '';
            document.getElementById('edit-headline').value = '';
            document.getElementById('edit-content').value = '';
            document.getElementById('edit-social1').value = '';
            document.getElementById('edit-social2').value = '';
            document.getElementById('edit-hashtags').value = '';
            document.getElementById('edit-image-sug').innerText = '';
            document.getElementById('editor-wp-badge').classList.add('hidden');
            document.getElementById('btn-wp-push').innerHTML = '<i data-feather="upload-cloud"></i> Push to WordPress';
            document.getElementById('spellcheck-results').classList.add('hidden');
            document.getElementById('spellcheck-results').innerHTML = '';
            updateStats();
            feather.replace();
            switchView('editor');
        }

        async function editArticle(id) {
            const res = await api('get_articles');
            const art = res.articles.find(a => a.id == id);
            if (art) {
                currentArticleId = art.id;
                currentWpPostId = art.wp_post_id || null;
                
                document.getElementById('edit-original').value = art.original_content || '';
                document.getElementById('edit-headline').value = art.headline || '';
                document.getElementById('edit-content').value = art.article_content || '';
                document.getElementById('edit-social1').value = art.social_1 || '';
                document.getElementById('edit-social2').value = art.social_2 || '';
                document.getElementById('edit-hashtags').value = art.hashtags || '';
                document.getElementById('edit-image-sug').innerText = art.image_suggestion ? `Suggestion: ${art.image_suggestion}` : '';
                
                const wpBadge = document.getElementById('editor-wp-badge');
                const wpBtn = document.getElementById('btn-wp-push');
                
                if (art.wp_pushed_at) {
                    wpBadge.innerText = `Article pushed on ${formatDateTimeStr(art.wp_pushed_at)}`;
                    wpBadge.classList.remove('hidden');
                    wpBtn.innerHTML = '<i data-feather="refresh-cw"></i> Update WP Draft';
                } else {
                    wpBadge.classList.add('hidden');
                    wpBtn.innerHTML = '<i data-feather="upload-cloud"></i> Push to WordPress';
                }

                document.getElementById('spellcheck-results').classList.add('hidden');
                document.getElementById('spellcheck-results').innerHTML = '';

                updateStats();
                feather.replace();
                switchView('editor');
            }
        }

        async function saveArticle() {
            updateStats();
            const data = {
                id: currentArticleId,
                original_content: document.getElementById('edit-original').value,
                headline: document.getElementById('edit-headline').value,
                article_content: document.getElementById('edit-content').value,
                social_1: document.getElementById('edit-social1').value,
                social_2: document.getElementById('edit-social2').value,
                hashtags: document.getElementById('edit-hashtags').value,
                image_suggestion: document.getElementById('edit-image-sug').innerText.replace('Suggestion: ', '')
            };
            const res = await api('save_article', 'POST', data);
            if (res.success) {
                currentArticleId = res.id;
                showToast('Article saved successfully!');
                loadDashboard(); // Refresh background data
            } else {
                showToast('Failed to save: ' + (res.error || 'Unknown error'), true);
            }
        }

        // --- AI Actions ---
        async function generateArticle() {
            const btn = document.getElementById('btn-generate');
            const content = document.getElementById('edit-original').value;
            
            if(!content) return showToast("Please paste some original content first.", true);
            
            btn.innerHTML = 'Generating...'; 
            btn.disabled = true;
            
            const res = await api('generate_article', 'POST', {content});
            
            btn.innerHTML = '<i data-feather="cpu"></i> Generate Article'; 
            btn.disabled = false; 
            feather.replace();
            
            if(res && res.success) {
                document.getElementById('edit-headline').value = res.headline || '';
                document.getElementById('edit-content').value = res.article_content || '';
                showToast('Article generated successfully!');
            } else {
                showToast("Failed to generate: " + (res.error || "The AI returned an empty response."), true);
            }
        }

        async function suggestImage() {
            const content = document.getElementById('edit-content').value;
            if(!content) return showToast("Generate an article first.", true);
            
            const res = await api('suggest_image', 'POST', {article_content: content});
            if(res && res.success) {
                document.getElementById('edit-image-sug').innerText = "Suggestion: " + res.suggestion;
                showToast('Image suggestion generated!');
            } else {
                showToast("Failed to generate suggestion: " + (res.error || "Unknown error"), true);
            }
        }

        async function generateSocial() {
            const content = document.getElementById('edit-content').value;
            if(!content) return showToast("Generate an article first.", true);
            
            const res = await api('generate_social', 'POST', {article_content: content});
            if(res && res.success) {
                document.getElementById('edit-social1').value = res.social_1 || '';
                document.getElementById('edit-social2').value = res.social_2 || '';
                document.getElementById('edit-hashtags').value = res.hashtags || '';
                showToast('Social content generated!');
            } else {
                showToast("Failed to generate social posts: " + (res.error || "Unknown error"), true);
            }
        }

        async function checkSpelling() {
            const content = document.getElementById('edit-content').value;
            if(!content) return showToast("Please write or generate article content first.", true);

            const btn = document.getElementById('btn-spellcheck');
            const resDiv = document.getElementById('spellcheck-results');
            
            btn.innerHTML = 'Checking...';
            btn.disabled = true;

            const res = await api('check_spelling', 'POST', { article_content: content });
            
            btn.innerHTML = '<i data-feather="check-square"></i> Check Spelling';
            btn.disabled = false;
            feather.replace();

            if (res && res.success) {
                resDiv.innerHTML = '';
                if (!res.errors || res.errors.length === 0) {
                    resDiv.className = 'card';
                    resDiv.style.borderColor = '#10b981';
                    resDiv.style.background = 'rgba(16, 185, 129, 0.05)';
                    resDiv.innerHTML = '<p style="color:#10b981; margin:0; font-weight:600;"><i data-feather="check-circle" style="vertical-align:middle; width:16px; height:16px; margin-right:5px;"></i> Perfect! No spelling errors detected.</p>';
                } else {
                    resDiv.className = 'card';
                    resDiv.style.borderColor = 'var(--danger-color)';
                    resDiv.style.background = 'rgba(220, 38, 38, 0.05)';
                    let html = '<p style="color:var(--danger-color); margin:0 0 10px 0; font-weight:600;"><i data-feather="alert-circle" style="vertical-align:middle; width:16px; height:16px; margin-right:5px;"></i> Flagged Words / Spelling Variants Needed:</p><ul style="margin:0; padding-left:20px; line-height:1.5;">';
                    res.errors.forEach(err => {
                        const wordEscaped = err.word.replace(/'/g, "\\'");
                        const sugEscaped = err.suggestion.replace(/'/g, "\\'");
                        html += `<li>Found <strong>"${err.word}"</strong>. Suggestion: <span style="color:#10b981; font-weight:bold;">${err.suggestion}</span> 
                        <button onclick="replaceWord(this, '${wordEscaped}', '${sugEscaped}')" style="background-color:var(--danger-color); color:white; border:none; padding:3px 8px; border-radius:4px; font-size:0.75rem; cursor:pointer; margin-left:10px;">Apply</button>
                        <br><small class="text-muted">Context: ...${err.context}...</small></li>`;
                    });
                    html += '</ul>';
                    resDiv.innerHTML = html;
                }
                resDiv.classList.remove('hidden');
                feather.replace();
            } else {
                showToast("Failed to perform spellcheck: " + (res.error || "Unknown error"), true);
            }
        }

        // Apply replacement word function
        function replaceWord(btn, word, suggestion) {
            const textarea = document.getElementById('edit-content');
            try {
                // Try word boundaries first to avoid partial word replacements
                const regex = new RegExp(`\\b${word}\\b`);
                if (regex.test(textarea.value)) {
                    textarea.value = textarea.value.replace(regex, suggestion);
                } else {
                    // Fallback to basic string replace
                    textarea.value = textarea.value.replace(word, suggestion);
                }
            } catch(e) {
                textarea.value = textarea.value.replace(word, suggestion);
            }
            
            // Visual feedback on the button
            btn.style.backgroundColor = '#10b981';
            btn.innerText = 'Applied';
            btn.disabled = true;

            showToast(`Replaced "${word}" with "${suggestion}"`);
        }

        function copyHashtags() {
            const text = document.getElementById('edit-hashtags').value;
            if (!text) return showToast("No hashtags to copy.", true);
            navigator.clipboard.writeText(text);
            showToast('Hashtags copied to clipboard!');
        }

        // --- Trash & Restore ---
        function trashArticle(id) {
            showModal("Delete Article", "Are you sure you want to move this to the recycle bin?", false, async () => {
                await api('trash_article', 'POST', {id});
                showToast("Moved to recycle bin");
                loadDashboard(); loadArticles();
            });
        }
        
        function trashCurrentArticle() {
            if(currentArticleId > 0) {
                showModal("Delete Article", "Are you sure you want to move this to the recycle bin?", false, async () => {
                    await api('trash_article', 'POST', {id: currentArticleId});
                    showToast("Moved to recycle bin");
                    switchView('dashboard');
                });
            } else if (currentArticleId === 0) {
                switchView('dashboard');
            }
        }

        async function restoreArticle(id) {
            await api('restore_article', 'POST', {id});
            showToast("Article restored");
            loadRecycleBin();
        }

        function hardDelete(id) {
            showModal("Permanent Delete", "Are you sure? This cannot be undone.", false, async () => {
                await api('delete_article_permanently', 'POST', {id});
                showToast("Permanently deleted");
                loadRecycleBin();
            });
        }

        // --- Utilities ---
        function copyToClipboard(type) {
            let text = "";
            if(type === 'article') {
                text = document.getElementById('edit-headline').value + "\n\n" + document.getElementById('edit-content').value;
            } else {
                text = "Post 1:\n" + document.getElementById('edit-social1').value + "\n\nPost 2:\n" + document.getElementById('edit-social2').value;
            }
            navigator.clipboard.writeText(text);
            showToast('Copied to clipboard!');
        }

        // --- AI Settings ---
        async function loadAiSettings() {
            const res = await api('get_settings');
            if (!res || !res.success) return;
            document.getElementById('ai-base-url').value = res.ai_base_url || '';
            document.getElementById('ai-api-key').value  = res.ai_api_key  || '';
            document.getElementById('ai-model').value    = res.ai_model    || '';
            document.getElementById('language-variant').value = res.language_variant || 'British';
            document.getElementById('wp-site-url').value = res.wp_site_url || '';
            document.getElementById('wp-api-key').value  = res.wp_api_key  || '';
        }

        async function saveWpSettings() {
            const data = {
                wp_site_url: document.getElementById('wp-site-url').value.trim(),
                wp_api_key:  document.getElementById('wp-api-key').value.trim(),
            };
            if (!data.wp_site_url || !data.wp_api_key) {
                return showToast('Please fill in the WordPress URL and API key.', true);
            }
            const res = await api('save_settings', 'POST', data);
            if (res && res.success) showToast('WordPress settings saved successfully!');
            else showToast('Failed to save WordPress settings.', true);
        }

        async function testWpSettings() {
            const btn = document.getElementById('btn-test-wp');
            const result = document.getElementById('wp-test-result');
            btn.innerHTML = 'Testing...'; btn.disabled = true;
            result.style.color = 'var(--text-muted)';
            result.innerText = 'Connecting to WordPress...';
            // Save current values first so the test uses whatever is in the fields
            await api('save_settings', 'POST', {
                wp_site_url: document.getElementById('wp-site-url').value.trim(),
                wp_api_key:  document.getElementById('wp-api-key').value.trim(),
            });
            const res = await api('test_wp_connection');
            btn.innerHTML = '<i data-feather="zap"></i> Test Connection'; btn.disabled = false; feather.replace();
            if (res && res.success) {
                result.style.color = '#10b981';
                result.innerText = '✓ Connection successful! WordPress site is reachable and the API key is valid.';
            } else {
                result.style.color = 'var(--danger-color)';
                result.innerText = '✗ ' + (res?.error || 'Connection failed. Check your site URL and API key.');
            }
        }

        function toggleWpKeyVisibility() {
            const input = document.getElementById('wp-api-key');
            const btn   = document.getElementById('btn-toggle-wp-key');
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = '<i data-feather="eye-off"></i>';
            } else {
                input.type = 'password';
                btn.innerHTML = '<i data-feather="eye"></i>';
            }
            feather.replace();
        }

        async function pushToWordPress() {
            const headline = document.getElementById('edit-headline').value.trim();
            const content  = document.getElementById('edit-content').value.trim();

            if (!headline || !content) {
                return showToast('Please generate or enter an article before pushing to WordPress.', true);
            }

            if (currentArticleId === 0) {
                const data = {
                    id: 0,
                    original_content: document.getElementById('edit-original').value,
                    headline: headline,
                    article_content: content,
                    social_1: document.getElementById('edit-social1').value,
                    social_2: document.getElementById('edit-social2').value,
                    hashtags: document.getElementById('edit-hashtags').value,
                    image_suggestion: document.getElementById('edit-image-sug').innerText.replace('Suggestion: ', '')
                };
                const autoSave = await api('save_article', 'POST', data);
                if (autoSave && autoSave.success) {
                    currentArticleId = autoSave.id;
                } else {
                    return showToast('Failed to save article locally before pushing to WordPress.', true);
                }
            }

            const btn = document.getElementById('btn-wp-push');
            const isUpdate = currentWpPostId !== null;
            
            btn.innerHTML = isUpdate ? 'Updating...' : 'Pushing...';
            btn.disabled  = true;

            const res = await api('push_to_wordpress', 'POST', { 
                id: currentArticleId, 
                headline, 
                article_content: content,
                wp_post_id: currentWpPostId // Pass to backend so it can overwrite
            });

            btn.innerHTML = isUpdate ? '<i data-feather="refresh-cw"></i> Update WP Draft' : '<i data-feather="upload-cloud"></i> Push to WordPress';
            btn.disabled  = false;
            feather.replace();

            if (res && res.success) {
                if (res.post_id) currentWpPostId = res.post_id;
                
                if (res.wp_pushed_at) {
                    const wpBadge = document.getElementById('editor-wp-badge');
                    wpBadge.innerText = `Article pushed on ${formatDateTimeStr(res.wp_pushed_at)}`;
                    wpBadge.classList.remove('hidden');
                }
                
                btn.innerHTML = '<i data-feather="refresh-cw"></i> Update WP Draft';
                feather.replace();
                
                let msg = isUpdate ? 'Draft updated in WordPress!' : 'Draft created in WordPress!';
                if (res.edit_url) {
                    msg += ' <a href="' + res.edit_url + '" target="_blank" style="color:inherit;text-decoration:underline;">Edit post →</a>';
                }
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = 'toast success';
                toast.innerHTML = '<i data-feather="check-circle" style="color:#10b981"></i> <span>' + msg + '</span>';
                container.appendChild(toast);
                feather.replace();
                setTimeout(() => toast.classList.add('show'), 10);
                setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 6000);
            } else {
                showToast('WordPress push failed: ' + (res?.error || 'Unknown error'), true);
            }
        }

        async function saveAiSettings() {
            const data = {
                ai_base_url: document.getElementById('ai-base-url').value.trim(),
                ai_api_key:  document.getElementById('ai-api-key').value.trim(),
                ai_model:    document.getElementById('ai-model').value.trim(),
                language_variant: document.getElementById('language-variant').value
            };
            if (!data.ai_base_url || !data.ai_api_key || !data.ai_model) {
                return showToast('Please fill in all AI provider fields.', true);
            }
            const res = await api('save_settings', 'POST', data);
            if (res && res.success) showToast('AI & Language settings saved successfully!');
            else showToast('Failed to save settings.', true);
        }

        async function testAiSettings() {
            const btn = document.getElementById('btn-test-ai');
            const result = document.getElementById('ai-test-result');
            btn.innerHTML = 'Testing...'; btn.disabled = true;
            result.style.color = 'var(--text-muted)';
            result.innerText = 'Sending test request...';
            // Save first so the test uses whatever is currently in the fields
            await api('save_settings', 'POST', {
                ai_base_url: document.getElementById('ai-base-url').value.trim(),
                ai_api_key:  document.getElementById('ai-api-key').value.trim(),
                ai_model:    document.getElementById('ai-model').value.trim(),
                language_variant: document.getElementById('language-variant').value
            });
            const res = await api('suggest_image', 'POST', { article_content: 'A quick brown fox jumps over the lazy dog.' });
            btn.innerHTML = '<i data-feather="zap"></i> Test Connection'; btn.disabled = false; feather.replace();
            if (res && res.success) {
                result.style.color = '#10b981';
                result.innerText = '✓ Connection successful! AI responded correctly.';
            } else {
                result.style.color = 'var(--danger-color)';
                result.innerText = '✗ ' + (res?.error || 'Connection failed. Check your URL, API key and model name.');
            }
        }

        function toggleApiKeyVisibility() {
            const input = document.getElementById('ai-api-key');
            const btn   = document.getElementById('btn-toggle-key');
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = '<i data-feather="eye-off"></i>';
            } else {
                input.type = 'password';
                btn.innerHTML = '<i data-feather="eye"></i>';
            }
            feather.replace();
        }

        // --- System/Admin Functions ---
        async function loadUsers() {
            const res = await api('get_users');
            if (!res || !res.success) return;
            
            const cont = document.getElementById('user-list');
            cont.innerHTML = '';
            (res.users || []).forEach(u => {
                cont.innerHTML += `
                    <div class="list-item">
                        <div><strong>${u.username}</strong> (${u.role})</div>
                        <div class="list-item-actions">
                            <button onclick="resetUserPass(${u.id})" class="secondary icon-btn" title="Reset Password"><i data-feather="key"></i></button>
                            <button onclick="deleteUser(${u.id})" class="danger icon-btn" title="Delete"><i data-feather="trash-2"></i></button>
                        </div>
                    </div>`;
            });
            feather.replace();
        }

        async function addUser() {
            const u = document.getElementById('new-user-name').value;
            const p = document.getElementById('new-user-pass').value;
            const r = document.getElementById('new-user-role').value;
            
            if(!u || !p) return showToast("Username and password required.", true);
            
            const res = await api('add_user', 'POST', {username: u, password: p, role: r});
            if(res.success) {
                document.getElementById('new-user-name').value = '';
                document.getElementById('new-user-pass').value = '';
                showToast("User added successfully");
                loadUsers();
            } else {
                showToast("Error adding user: " + (res.error || "Unknown"), true);
            }
        }

        function resetUserPass(id) {
            showModal("Reset Password", "Enter new password for this user:", true, async (newPass) => {
                if(newPass) {
                    const res = await api('reset_password', 'POST', {id, password: newPass});
                    if(res.success) showToast("Password reset successfully.");
                    else showToast("Error resetting password.", true);
                }
            });
        }

        function deleteUser(id) {
            showModal("Delete User", "Are you sure you want to delete this user?", false, async () => {
                const res = await api('delete_user', 'POST', {id});
                if(res.success) {
                    showToast("User deleted");
                    loadUsers();
                } else showToast("Error deleting user.", true);
            });
        }

        async function changeMyPassword() {
            const p = document.getElementById('my-new-pass').value;
            if(!p) return showToast("Enter a new password.", true);
            
            const res = await api('check_auth'); 
            const resetRes = await api('reset_password', 'POST', {id: res.user.id, password: p});
            
            if(resetRes.success) {
                showToast("Password updated successfully.");
                document.getElementById('my-new-pass').value = '';
            } else {
                showToast("Failed to update password.", true);
            }
        }

        function restoreJson(input) {
            const file = input.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = async function(e) {
                try {
                    const data = JSON.parse(e.target.result);
                    const res = await api('restore_json', 'POST', data);
                    if(res.success) {
                        showToast("Data restored successfully.");
                        loadDashboard();
                    } else {
                        showToast("Error restoring data from database side.", true);
                    }
                } catch (err) { 
                    showToast("Invalid JSON file.", true); 
                }
            };
            reader.readAsText(file);
        }
    </script>
</body>
</html>
