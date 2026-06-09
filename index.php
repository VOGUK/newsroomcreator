<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsroom Creator</title>

    <!-- ═══ CRAWLERS / INDEXING ═══ -->
    <!-- Prevent all search engines, AI crawlers, and web archivers from indexing this app -->
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex, nocache">
    <meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="bingbot" content="noindex, nofollow">
    <!-- GPTBot (OpenAI), ClaudeBot (Anthropic), CCBot (Common Crawl used by AI trainers) -->
    <meta name="GPTBot" content="noindex">
    <meta name="ClaudeBot" content="noindex">
    <meta name="CCBot" content="noindex">
    <meta name="anthropic-ai" content="noindex">
    <meta name="cohere-ai" content="noindex">

    <!-- ═══ PWA / ADD TO HOME SCREEN ═══ -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2563eb" id="theme-color-meta">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Newsroom">
    <link rel="apple-touch-icon" href="icon-192.png">

    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        :root {
            --bg-color: #f4f6f8; --surface-color: #ffffff; --text-color: #1a1a1a;
            --text-muted: #666666; --primary-color: #2563eb; --danger-color: #dc2626;
            --border-color: #e5e7eb; --font-size: 16px;
        }
        [data-theme="dark"] {
            --bg-color: #111827; --surface-color: #1f2937; --text-color: #f9fafb;
            --text-muted: #9ca3af; --border-color: #374151; --primary-color: #3b82f6;
        }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: var(--bg-color); color: var(--text-color); font-size: var(--font-size); margin: 0; padding: 0; transition: all 0.3s ease; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; padding-bottom: 40px; }
        .hidden { display: none !important; }
        nav { background-color: var(--surface-color); border-bottom: 1px solid var(--border-color); padding: 1rem; position: sticky; top: 0; z-index: 100; }
        .nav-inner { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        .nav-brand { display: flex; align-items: center; gap: 10px; font-weight: bold; font-size: 1.2rem; }
        .nav-links { display: flex; gap: 15px; align-items: center; }
        .nav-links button { background: none; border: none; color: var(--text-color); cursor: pointer; display: flex; align-items: center; gap: 5px;}
        @media (max-width: 768px) {
            .nav-links { overflow-x: auto; scrollbar-width: none; flex-shrink: 1; padding-bottom: 2px; }
            .nav-links::-webkit-scrollbar { display: none; }
            .nav-links button { flex-shrink: 0; white-space: nowrap; }
        }
        .card { background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;}
        button { background: var(--primary-color); color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 5px; }
        button:hover { opacity: 0.9; }
        button.secondary { background: var(--surface-color); color: var(--text-color); border: 1px solid var(--border-color); }
        button.danger { background: var(--danger-color); }
        button.icon-btn { padding: 6px; background: transparent; color: var(--text-color); border: 1px solid transparent;}
        button.icon-btn:hover { border-color: var(--border-color); background: var(--bg-color); }
        input, textarea, select { width: 100%; padding: 10px; margin-bottom: 15px; background: var(--bg-color); color: var(--text-color); border: 1px solid var(--border-color); border-radius: 6px; box-sizing: border-box; font-family: inherit; font-size: inherit; }
        textarea { resize: vertical; min-height: 150px; }
        #edit-original, #edit-content { min-height: 400px; }
        .compare-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .list-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color); }
        .list-item-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap;}
        .wp-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;}
        @media (max-width: 768px) {
            .compare-grid { grid-template-columns: 1fr; }
            .user-add-grid { grid-template-columns: 1fr 1fr !important; }
            .user-add-grid > div:last-child { grid-column: span 2; }
            .wp-meta-grid { grid-template-columns: 1fr; }
        }
        #login-view { display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-box { max-width: 400px; width: 100%; text-align: center; }
        #toast-container { position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; pointer-events: none; }
        .toast { background: var(--surface-color); color: var(--text-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 10px; transform: translateX(120%); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .toast.show { transform: translateX(0); }
        .toast.error { border-left: 4px solid var(--danger-color); }
        .toast.success { border-left: 4px solid #10b981; }
        #modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9998; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.2s ease; }
        #modal-overlay.show { opacity: 1; pointer-events: all; }
        .modal-box { background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; padding: 24px; max-width: 400px; width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.2); transform: translateY(-20px); transition: transform 0.2s ease; }
        #modal-overlay.show .modal-box { transform: translateY(0); }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
        .admin-only { display: none; }
        .editor-visible { display: none; }
        .user-only { display: none; }
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
            <div id="login-logo-wrap" style="margin-bottom:10px;">
                <img id="login-logo-img" src="" alt="Logo" style="display:none; width:80px; height:80px; object-fit:contain; border-radius:10px;">
                <i id="login-logo-icon" data-feather="edit-3" style="width:48px;height:48px;"></i>
            </div>
            <h2 id="login-app-name">Newsroom Creator</h2>
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
                    <span id="nav-logo-wrap">
                        <img id="nav-logo-img" src="" alt="Logo" style="display:none; width:28px; height:28px; object-fit:contain; border-radius:4px; vertical-align:middle;">
                        <i id="nav-logo-icon" data-feather="edit-3" style="vertical-align:middle;"></i>
                    </span>
                    <span id="nav-app-name">Newsroom Creator</span>
                </div>
                <div class="nav-links">
                    <button onclick="switchView('dashboard')"><i data-feather="home"></i> <span>Dashboard</span></button>
                    <button onclick="switchView('list')"><i data-feather="list"></i> <span>Articles</span></button>
                    <button onclick="switchView('posts')" class="editor-visible"><i data-feather="globe"></i> <span>Posts</span></button>
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
                <div class="card"><div id="recent-articles-list">Loading...</div></div>
            </div>

            <div id="editor" class="view hidden">
                <div class="card-header">
                    <h2>Article Editor</h2>
                    <button onclick="switchView('dashboard')" class="secondary">Back</button>
                </div>
                
                <div class="compare-grid">
                    <div class="card">
                        <h3>Source Content</h3>
                        <p class="text-muted" style="font-size:0.85rem">Paste press releases, emails, or rival articles here.</p>
                        <textarea id="edit-original" placeholder="Paste source material here..."></textarea>
                        
                        <div style="display:flex; gap:10px; margin-bottom: 15px;">
                            <button onclick="generateArticle()" id="btn-generate"><i data-feather="cpu"></i> Generate Article</button>
                            <button onclick="spinArticle()" id="btn-spin" style="background-color: #8b5cf6;"><i data-feather="refresh-cw"></i> Spin Article</button>
                        </div>
                    </div>

                    <div class="card">
                        <div id="editor-wp-badge" class="hidden" style="color:white; padding:8px 12px; border-radius:6px; font-size:0.85rem; font-weight:bold; margin-bottom:15px; text-align:center;"></div>
                        
                        <h3>Generated Article</h3>
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:15px;">
                            <input type="text" id="edit-headline" placeholder="Headline..." style="margin-bottom:0; flex:1;">
                            <button onclick="regenerateHeadline()" id="btn-regen-headline" title="Re-generate headline" style="background-color:#8b5cf6; flex-shrink:0; white-space:nowrap;"><i data-feather="refresh-cw"></i> New Headline</button>
                        </div>
                        <div id="similarity-panel" class="hidden" style="margin-bottom:10px; padding:10px; background:var(--bg-color); border:1px solid var(--border-color); border-radius:6px; font-size:0.85rem;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                <strong>Similarity to Original Source</strong>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <button onclick="recheckSimilarity()" id="btn-recheck-similarity" title="Re-check similarity against current edited article" style="background:#6366f1; font-size:0.78rem; padding:4px 10px; white-space:nowrap;"><i data-feather="refresh-cw" style="width:12px;height:12px;"></i> Recheck</button>
                                    <span id="similarity-score-badge" style="padding:3px 10px; border-radius:12px; font-size:0.8rem; font-weight:700; color:white; background:#6b7280;">—</span>
                                </div>
                            </div>
                            <div style="background:var(--border-color); border-radius:4px; height:6px;">
                                <div id="similarity-bar" style="background:#6b7280; width:0%; height:100%; border-radius:4px; transition:width 0.4s;"></div>
                            </div>
                            <p style="font-size:0.78rem; color:var(--text-muted); margin:6px 0 0 0;">Lower similarity means more unique content. Aim for under 40% for copyright safety.</p>
                        </div>
                        <textarea id="edit-content" oninput="updateKeywordDensity(); updateHumanScore(); updateStats(); updateSimilarity();" style="margin-bottom: 5px;"></textarea>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <div id="article-stats" class="text-muted" style="font-size:0.85rem;">0 words | 0 chars | ~0 min read (out loud)</div>
                            <button onclick="checkSpelling()" id="btn-spellcheck" style="background-color: #f97316; color: white; border: none;">
                                <i data-feather="check-square"></i> Check Spelling
                            </button>
                        </div>

                        <div id="spellcheck-results" class="hidden" style="margin-top:10px; font-size:0.9rem;"></div>
                        
                        <div id="keyword-density-panel" style="font-size:0.85rem; padding:10px; background:var(--bg-color); border:1px solid var(--border-color); border-radius:6px; margin-bottom:10px;">
                            <span class="text-muted">Keyword Scanner loading...</span>
                        </div>

                        <div id="human-score-panel" style="font-size:0.85rem; padding:10px; background:var(--bg-color); border:1px solid var(--border-color); border-radius:6px; margin-bottom:15px;">
                            <span class="text-muted">Human Score loading...</span>
                        </div>

                        <!-- 1. FEATURED IMAGE SECTION -->
                        <div style="margin-top:15px; border-top: 1px solid var(--border-color); padding-top:15px;">
                            <h4 style="margin: 0 0 5px 0; font-size:0.9rem;">Attach Featured Image <span style="color:var(--danger-color);">*</span> <small style="color:var(--text-muted); font-weight:normal;">(Required — min 1200×675 px)</small></h4>
                            <input type="file" id="edit-image-file" accept="image/*" onchange="processImage(this)" style="margin-bottom: 5px;">
                            <input type="hidden" id="edit-image-base64">
                            <img id="image-preview" class="hidden" style="max-width: 100%; height: auto; border-radius: 6px; border: 1px solid var(--border-color); margin-top: 5px;">
                            
                            <div id="image-details-container" class="hidden" style="margin-top: 15px; background:var(--bg-color); padding:15px; border-radius:6px; border:1px solid var(--border-color);">
                                <label style="font-size:0.8rem; font-weight:bold;">Image Title *</label>
                                <input type="text" id="img-title" placeholder="Descriptive title">
                                
                                <label style="font-size:0.8rem; font-weight:bold;">Alt Text (SEO) *</label>
                                <input type="text" id="img-alt" placeholder="Describe the image for screen readers">
                                
                                <label style="font-size:0.8rem; font-weight:bold;">Caption (Optional)</label>
                                <input type="text" id="img-caption" placeholder="Visible caption under the photo">
                                
                                <button onclick="removeImage()" class="danger" style="margin-top:5px;"><i data-feather="trash-2"></i> Delete & Choose Another</button>
                            </div>
                        </div>

                        <!-- 2. WP PUBLISHING SETTINGS — editor + admin -->
                        <h4 id="wp-settings-heading" class="editor-visible" style="margin: 20px 0 10px 0; font-size:0.9rem; border-top: 1px solid var(--border-color); padding-top: 15px;">WordPress Publishing Settings</h4>
                        <div id="wp-settings-grid" class="wp-meta-grid editor-visible">
                            <div>
                                <label style="font-size:0.8rem; font-weight:bold;">Status</label>
                                <select id="wp-status" style="margin-bottom:0;">
                                    <option value="draft">Draft</option>
                                    <option value="publish">Publish Immediately</option>
                                    <option value="future">Scheduled (Use Date Below)</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size:0.8rem; font-weight:bold;">Date/Time</label>
                                <input type="datetime-local" id="wp-date" style="margin-bottom:0;">
                            </div>
                            <div>
                                <label style="font-size:0.8rem; font-weight:bold;">Author</label>
                                <select id="wp-author" style="margin-bottom:0;"><option value="">Default/Current</option></select>
                            </div>
                            <div>
                                <label style="font-size:0.8rem; font-weight:bold;">Category</label>
                                <select id="wp-category" multiple style="margin-bottom:0; height:60px;"></select>
                            </div>
                            <div style="grid-column: span 2;">
                                <label style="font-size:0.8rem; font-weight:bold;">Tags (Comma separated)</label>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <input type="text" id="wp-tags" placeholder="e.g. news, local, update" style="margin-bottom:0; flex:1;">
                                    <button onclick="generateTags()" id="btn-generate-tags" class="secondary" style="white-space:nowrap; flex-shrink:0;" title="Use AI to generate tags"><i data-feather="tag"></i> Generate Tags</button>
                                </div>
                            </div>
                        </div>

                        <div id="url-container" class="hidden editor-visible" style="margin-top:5px; margin-bottom:15px; padding:12px; background:var(--bg-color); border: 1px solid var(--border-color); border-radius:6px; font-size:0.85rem;">
                            <strong>WP Permalink:</strong> <a id="pub-link" href="#" target="_blank" style="color:var(--primary-color); word-break:break-all;"></a>
                            <div id="shortlink-row" style="margin-top:8px;">
                                <!-- State A: shortlink exists -->
                                <div id="shortlink-display" class="hidden" style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                    <strong>Short URL:</strong>
                                    <span id="short-link" style="font-weight:bold; color:var(--text-color);"></span>
                                    <button class="icon-btn" onclick="copyShortUrl()" style="padding:3px 7px;" title="Copy short URL">
                                        <i data-feather="copy" style="width:14px; height:14px;"></i> Copy
                                    </button>
                                </div>
                                <!-- State B: permalink exists but no shortlink yet -->
                                <div id="shortlink-create" class="hidden" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                    <button id="btn-create-shortlink" onclick="createShortlink()" style="background:#6366f1; font-size:0.82rem; padding:5px 12px;">
                                        <i data-feather="link"></i> Create Short URL (TinyURL)
                                    </button>
                                    <span id="shortlink-create-msg" style="font-size:0.8rem; color:var(--text-muted);"></span>
                                </div>
                            </div>
                        </div>

                        <!-- 3. ACTIONS — Save always visible; Submit Article for users; Push for editors + admins -->
                        <div style="margin-top:20px; display:flex; gap:10px; flex-wrap:wrap; border-top: 1px solid var(--border-color); padding-top:15px;">
                            <button onclick="saveArticle()"><i data-feather="save"></i> Save</button>
                            <button onclick="submitArticle(this.dataset.resubmit==='1')" id="btn-submit-article" class="user-only" style="background-color:#f59e0b;"><i data-feather="send"></i> Submit Article</button>
                            <button onclick="furtherActionArticle()" id="btn-further-action" class="editor-visible" style="background-color:#7c3aed;"><i data-feather="alert-circle"></i> Further Action</button>
                            <button onclick="approveArticle()" id="btn-approve-article" class="editor-visible" style="background-color:#16a34a;"><i data-feather="check-circle"></i> Approve</button>
                            <button onclick="prePushCheck()" id="btn-wp-push" class="editor-visible" style="background-color: #10b981; color: white; border: none;"><i data-feather="upload-cloud"></i> Push to Site</button>
                            <button onclick="trashCurrentArticle()" class="danger"><i data-feather="trash"></i> Delete</button>
                        </div>

                        <!-- 4. SOCIAL CONTENT — editor + admin only, only after WP push -->
                        <div style="margin-top:15px; border-top: 1px solid var(--border-color); padding-top:15px;" class="editor-visible">
                            <button id="btn-social" onclick="generateSocial()" class="secondary" disabled style="opacity:0.5;" title="Push article to WordPress first"><i data-feather="share-2"></i> Create Social Content</button>
                            
                            <div id="social-img-sug-container" class="hidden" style="margin-top:15px;">
                                <p style="font-size:0.9rem; margin-bottom:5px;"><strong>Suggested Image:</strong> <span id="edit-image-sug" class="text-muted"></span></p>
                            </div>
                            
                            <textarea id="edit-social1" style="min-height:60px; margin-top:10px;" placeholder="Post 1 (< 100 chars)"></textarea>
                            <textarea id="edit-social2" style="min-height:80px;" placeholder="Post 2 (< 300 chars)"></textarea>
                            <div style="margin-top:10px;">
                                <textarea id="edit-hashtags" style="min-height:80px; font-size:0.85rem;" placeholder="Hashtags will appear here after generating social content..."></textarea>
                            </div>
                        </div>
                        <!-- Hidden inputs so non-admin saves preserve social values -->
                        <input type="hidden" id="edit-social1-hidden">
                        <input type="hidden" id="edit-social2-hidden">
                        <input type="hidden" id="edit-hashtags-hidden">

                        <!-- 5. COPY BUTTONS — editor + admin -->
                        <div style="margin-top:10px; display:flex; gap:10px; flex-wrap:wrap;" class="editor-visible">
                            <button onclick="saveArticle()"><i data-feather="save"></i> Save</button>
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

            <div id="posts" class="view hidden">
                <div class="card">
                    <div class="card-header">
                        <h2>WordPress Posts</h2>
                        <button onclick="loadWpPosts()" class="secondary" id="btn-reload-wp-posts"><i data-feather="refresh-cw"></i> Refresh</button>
                    </div>
                    <div id="wp-posts-list"><span style="color:var(--text-muted); font-size:0.9rem;">Loading...</span></div>
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
                
                <div class="card">
                    <h2>Change Password</h2>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:15px;">Update your login password.</p>
                    <label style="font-size:0.85rem; font-weight:600;">New Password</label>
                    <input type="password" id="my-new-pass" placeholder="Enter new password">
                    <button onclick="changeMyPassword()" class="secondary"><i data-feather="key"></i> Update Password</button>
                </div>

                <div class="card admin-only">
                    <h2>Data Backup & Restore</h2>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:15px;">Export your articles or restore from a previous backup.</p>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <button onclick="window.location.href='api.php?action=backup_json'" class="secondary"><i data-feather="download"></i> Backup JSON</button>
                        <button onclick="window.location.href='api.php?action=export_csv'" class="secondary"><i data-feather="file-text"></i> Export CSV</button>
                        <button onclick="document.getElementById('restore-file').click()" class="secondary"><i data-feather="upload"></i> Restore JSON</button>
                        <input type="file" id="restore-file" style="display:none;" accept=".json" onchange="restoreJson(this)">
                    </div>
                </div>

                <div class="card admin-only">
                    <h2>Target SEO Keywords</h2>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:15px;">Enter comma-separated regional keywords. The editor will monitor these to ensure writers are capturing local traffic.</p>
                    <textarea id="sys-keywords" placeholder="e.g. Hillingdon, Uxbridge, council..."></textarea>
                    <button onclick="saveSettingsForm()"><i data-feather="save"></i> Save Target Keywords</button>
                </div>

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
                        <strong>Examples:</strong> OpenAI: <code>https://api.openai.com/v1</code> &nbsp;|&nbsp; OpenRouter: <code>https://openrouter.ai/api/v1</code>
                    </div>
                    <div style="margin-top:15px;">
                        <button onclick="saveSettingsForm()"><i data-feather="save"></i> Save AI Settings</button>
                        <button onclick="testAiSettings()" class="secondary" style="margin-left:10px;" id="btn-test-ai"><i data-feather="zap"></i> Test Connection</button>
                    </div>
                    <p id="ai-test-result" style="font-size:0.85rem; margin-top:10px;"></p>
                </div>

                <div class="card admin-only">
                    <h2>WordPress Integration (Admin)</h2>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:15px;">Connect to your WordPress site so generated articles can be pushed directly.</p>
                    <label style="font-size:0.85rem; font-weight:600;">WordPress Site URL</label>
                    <input type="text" id="wp-site-url" placeholder="e.g. https://yoursite.com">
                    <label style="font-size:0.85rem; font-weight:600;">API Key</label>
                    <div style="position:relative;">
                        <input type="password" id="wp-api-key" placeholder="Enter the key from the Newsroom Creator plugin" style="padding-right:44px;">
                        <button onclick="toggleWpKeyVisibility()" class="icon-btn" id="btn-toggle-wp-key" title="Show/hide API key" style="position:absolute; right:8px; top:50%; transform:translateY(-60%); margin:0;"><i data-feather="eye"></i></button>
                    </div>
                    <div style="margin-top:15px;">
                        <button onclick="saveSettingsForm()"><i data-feather="save"></i> Save WordPress Settings</button>
                        <button onclick="testWpSettings()" class="secondary" style="margin-left:10px;" id="btn-test-wp"><i data-feather="zap"></i> Test Connection</button>
                    </div>
                    <p id="wp-test-result" style="font-size:0.85rem; margin-top:10px;"></p>
                </div>

                <div class="card admin-only">
                    <h2>URL Shortener (Admin)</h2>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:15px;">Choose which URL shortening service to use. TinyURL works without an API key; Short.io requires a key and your custom domain.</p>
                    <label style="font-size:0.85rem; font-weight:600;">Provider</label>
                    <select id="shorturl-provider" onchange="toggleShortIoDomain()" style="margin-bottom:15px;">
                        <option value="tinyurl">TinyURL</option>
                        <option value="shortio">Short.io</option>
                    </select>
                    <label style="font-size:0.85rem; font-weight:600;">API Key <span style="font-weight:normal; color:var(--text-muted);">(Optional for TinyURL / Required for Short.io)</span></label>
                    <div style="position:relative;">
                        <input type="password" id="shorturl-api-key" placeholder="Enter API key" style="padding-right:44px; margin-bottom:15px;">
                        <button onclick="toggleShortUrlKeyVisibility()" class="icon-btn" id="btn-toggle-shorturl-key" title="Show/hide key" style="position:absolute; right:8px; top:12px; margin:0;"><i data-feather="eye"></i></button>
                    </div>
                    <div id="shortio-domain-wrap" class="hidden">
                        <label style="font-size:0.85rem; font-weight:600;">Short.io Domain</label>
                        <input type="text" id="shortio-domain" placeholder="e.g. yourdomain.short.gy" style="margin-bottom:15px;">
                    </div>
                    <button onclick="saveSettingsForm()"><i data-feather="save"></i> Save URL Shortener Settings</button>
                </div>

                <div class="card admin-only">
                    <h2>Customisation (Admin)</h2>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:15px;">Change the app name and logo shown on the login screen and header. Logo must be a PNG, minimum 512×512 pixels.</p>
                    <label style="font-size:0.85rem; font-weight:600;">App Name</label>
                    <input type="text" id="sys-app-name" placeholder="e.g. Newsroom Creator">
                    <label style="font-size:0.85rem; font-weight:600;">Logo (PNG, min 512×512)</label>
                    <input type="file" id="sys-logo-file" accept="image/png" onchange="previewLogo(this)" style="margin-bottom:10px;">
                    <div id="sys-logo-preview-wrap" style="display:none; margin-bottom:12px;">
                        <img id="sys-logo-preview" src="" alt="Logo preview" style="width:80px; height:80px; object-fit:contain; border:1px solid var(--border-color); border-radius:8px; background:var(--bg-color); padding:4px;">
                        <button onclick="clearLogo()" class="danger" style="margin-left:10px; padding:4px 10px; font-size:0.8rem;"><i data-feather="x"></i> Remove Logo</button>
                    </div>
                    <p id="sys-logo-error" style="color:var(--danger-color); font-size:0.82rem; margin-bottom:8px;"></p>
                    <button onclick="saveCustomisation()"><i data-feather="save"></i> Save Customisation</button>
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
                                <option value="editor">Editor</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div style="padding-top:22px;">
                            <button onclick="addUser()"><i data-feather="user-plus"></i> Add User</button>
                        </div>
                    </div>
                    <div id="user-list"></div>
                </div>

            </div>
        </div>
        <div id="login-status-bar" style="position:fixed; bottom:0; left:0; right:0; background:var(--surface-color); border-top:1px solid var(--border-color); padding:6px 20px; font-size:0.78rem; color:var(--text-muted); z-index:90; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:6px;">
                <i data-feather="user" style="width:13px;height:13px;"></i>
                <span id="login-status-text"></span>
            </div>
            <div id="footer-branding" style="font-size:0.75rem; color:var(--text-muted); opacity:0.7;">Newsroom Creator by VBI &copy; 2026 All Rights Reserved</div>
        </div>
    </div>

    <!-- ═══ PWA INSTALL BANNER ═══ -->
    <div id="pwa-install-banner" style="display:none; position:fixed; bottom:60px; left:0; right:0; margin:0 16px; background:var(--surface-color); border:1px solid var(--border-color); border-radius:10px; padding:14px 16px; z-index:9990; box-shadow:0 4px 18px rgba(0,0,0,0.18); align-items:center; gap:12px; flex-wrap:wrap;">
        <div style="flex:1; min-width:180px;">
            <strong style="font-size:0.95rem;">Add to Home Screen</strong>
            <p style="margin:2px 0 0; font-size:0.82rem; color:var(--text-muted);">Install Newsroom Creator for quick access on the move.</p>
        </div>
        <div style="display:flex; gap:8px; flex-shrink:0;">
            <button id="pwa-install-btn" style="background:var(--primary-color); color:white; padding:8px 16px; border-radius:6px; border:none; cursor:pointer; font-size:0.88rem; display:flex; align-items:center; gap:5px;"><i data-feather="download" style="width:14px;height:14px;"></i> Install</button>
            <button id="pwa-dismiss-btn" style="background:transparent; color:var(--text-muted); padding:8px 12px; border-radius:6px; border:1px solid var(--border-color); cursor:pointer; font-size:0.88rem;">Later</button>
        </div>
    </div>
    <!-- iOS-specific hint (shown only on iOS where beforeinstallprompt doesn't fire) -->
    <div id="pwa-ios-banner" style="display:none; position:fixed; bottom:60px; left:0; right:0; margin:0 16px; background:var(--surface-color); border:1px solid var(--border-color); border-radius:10px; padding:14px 16px; z-index:9990; box-shadow:0 4px 18px rgba(0,0,0,0.18); align-items:center; gap:12px; flex-wrap:wrap;">
        <div style="flex:1; min-width:180px;">
            <strong style="font-size:0.95rem;">Add to Home Screen</strong>
            <p style="margin:2px 0 0; font-size:0.82rem; color:var(--text-muted);">Tap the <strong>Share</strong> button <span style="font-size:1rem;">⎙</span> in Safari, then choose <strong>"Add to Home Screen"</strong>.</p>
        </div>
        <button id="pwa-ios-dismiss-btn" style="background:transparent; color:var(--text-muted); padding:8px 12px; border-radius:6px; border:1px solid var(--border-color); cursor:pointer; font-size:0.88rem; flex-shrink:0;">Got it</button>
    </div>

    <script>
        let currentUserId = null;
        let currentUserRole = null;
        let currentArticleId = 0;
        let currentWpPostId = null; 
        let currentPermalink = null;
        let currentShortUrl = null;
        let textSize = 16;
        let allArticlesData = [];
        let appSettings = {};
        let currentKeywordHits = 0; 

        function formatDateTimeStr(dateTimeStr) {
            if (!dateTimeStr) return "";
            try {
                const d = new Date(dateTimeStr.replace(/-/g, "/"));
                if (!isNaN(d.getTime())) {
                    const dd = String(d.getDate()).padStart(2, '0');
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const yy = String(d.getFullYear()).slice(-2);
                    const hh = String(d.getHours()).padStart(2, '0');
                    const min = String(d.getMinutes()).padStart(2, '0');
                    return `${dd}/${mm}/${yy} at ${hh}:${min}`;
                }
            } catch(e) {}
            return dateTimeStr;
        }

        function updateStats() {
            const text = document.getElementById('edit-content').value || '';
            const charCount = text.length;
            const wordCount = text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
            const readTimeMin = Math.ceil(wordCount / 150); 
            document.getElementById('article-stats').innerText = `${wordCount} words | ${charCount} chars | ~${readTimeMin} min read (out loud)`;
        }

        function showToast(message, isError = false) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${isError ? 'error' : 'success'}`;
            const icon = isError ? '<i data-feather="alert-circle" style="color:var(--danger-color)"></i>' : '<i data-feather="check-circle" style="color:#10b981"></i>';
            toast.innerHTML = `${icon} <span>${message}</span>`;
            container.appendChild(toast);
            feather.replace();
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 4000);
        }

        function showModal(title, message, isPrompt = false, onConfirmCallback) {
            const overlay = document.getElementById('modal-overlay');
            const titleEl = document.getElementById('modal-title');
            const msgEl = document.getElementById('modal-msg');
            const inputEl = document.getElementById('modal-input');
            
            titleEl.innerText = title;
            msgEl.innerText = message;
            
            if (isPrompt) { inputEl.classList.remove('hidden'); inputEl.value = ''; inputEl.focus(); } 
            else { inputEl.classList.add('hidden'); }

            document.getElementById('modal-cancel').onclick = () => overlay.classList.remove('show');
            document.getElementById('modal-confirm').onclick = () => {
                overlay.classList.remove('show');
                if (isPrompt) onConfirmCallback(inputEl.value);
                else onConfirmCallback();
            };
            overlay.classList.add('show');
        }

        document.addEventListener('DOMContentLoaded', async () => {
            feather.replace();
            if (localStorage.getItem('theme') === 'dark') toggleTheme();
            // Load branding for login page before auth check
            try {
                const res = await fetch('api.php?action=get_branding');
                const data = await res.json();
                if (data && data.success) applyBranding(data.app_name, data.app_logo);
            } catch(e) {}
            checkAuth();
        });

        async function api(action, method = 'GET', data = null) {
            try {
                const options = { method };
                if (data) { options.headers = { 'Content-Type': 'application/json' }; options.body = JSON.stringify(data); }
                const res = await fetch(`api.php?action=${action}`, options);
                const text = await res.text(); 
                try { return JSON.parse(text); } 
                catch (err) { return { success: false, error: "Server error: " + text.substring(0, 150) }; }
            } catch (error) { return { success: false, error: "Network error. Are you connected to the internet?" }; }
        }

        let loginTime = null;

        async function checkAuth() {
            const res = await api('check_auth');
            if (res.success) {
                currentUserId = res.user.id; 
                currentUserRole = res.user.role;
                document.getElementById('login-view').classList.add('hidden');
                document.getElementById('app-view').classList.remove('hidden');
                
                const roleCheck = (currentUserRole || '').toLowerCase();
                const isAdmin  = roleCheck === 'admin';
                const isEditor = roleCheck === 'editor' || isAdmin;
                const isUser   = roleCheck === 'user';

                function showEl(el) {
                    if (el.tagName === 'BUTTON') { el.style.display = 'inline-flex'; return; }
                    // Check if the element has an inline display:flex style attribute
                    const inlineStyle = el.getAttribute('style') || '';
                    if (inlineStyle.includes('display:flex') || inlineStyle.includes('display: flex')) {
                        el.style.display = 'flex';
                    } else {
                        el.style.display = 'block';
                    }
                }

                document.querySelectorAll('.admin-only').forEach(el => {
                    isAdmin ? showEl(el) : (el.style.display = 'none');
                });
                document.querySelectorAll('.editor-visible').forEach(el => {
                    isEditor ? showEl(el) : (el.style.display = 'none');
                });
                document.querySelectorAll('.user-only').forEach(el => {
                    isUser ? showEl(el) : (el.style.display = 'none');
                });

                // Login status bar
                if (!loginTime) loginTime = new Date();
                const hh  = String(loginTime.getHours()).padStart(2, '0');
                const min = String(loginTime.getMinutes()).padStart(2, '0');
                document.getElementById('login-status-text').innerText =
                    `${res.user.username} logged in since ${hh}:${min}`;
                feather.replace();
                
                loadSettings(); switchView('dashboard');
            }
        }

        async function login() {
            const res = await api('login', 'POST', {username: document.getElementById('login-user').value, password: document.getElementById('login-pass').value});
            if (res.success) checkAuth(); else document.getElementById('login-error').innerText = "Invalid credentials";
        }
        async function logout() { await api('logout'); location.reload(); }

        function switchView(view) {
            document.querySelectorAll('.view').forEach(el => el.classList.add('hidden'));
            document.getElementById(view).classList.remove('hidden');
            if(view === 'dashboard') loadDashboard();
            if(view === 'list') { loadArticles(); }
            if(view === 'posts') { loadWpPosts(); }
            if(view === 'recycle') loadRecycleBin();
            if(view === 'system') { 
                loadSettings(); 
                const roleCheck = (currentUserRole || '').toLowerCase();
                if (roleCheck === 'admin') loadUsers(); 
            }
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
            if (textSize < 12) textSize = 12; if (textSize > 24) textSize = 24;
            document.documentElement.style.setProperty('--font-size', textSize + 'px');
        }

        async function loadSettings() {
            const res = await api('get_settings');
            if (res && res.success) {
                appSettings = res;
                document.getElementById('sys-keywords').value = res.local_keywords || '';
                document.getElementById('ai-base-url').value = res.ai_base_url || '';
                document.getElementById('ai-api-key').value  = res.ai_api_key  || '';
                document.getElementById('ai-model').value    = res.ai_model    || '';
                document.getElementById('language-variant').value = res.language_variant || 'British';
                document.getElementById('wp-site-url').value = res.wp_site_url || '';
                document.getElementById('wp-api-key').value  = res.wp_api_key  || '';
                // Customisation
                document.getElementById('sys-app-name').value = res.app_name || '';
                if (res.app_logo) {
                    document.getElementById('sys-logo-preview').src = res.app_logo;
                    document.getElementById('sys-logo-preview-wrap').style.display = 'flex';
                    document.getElementById('sys-logo-preview-wrap').style.alignItems = 'center';
                }
                applyBranding(res.app_name, res.app_logo);
                // URL shortener
                if (document.getElementById('shorturl-provider')) {
                    document.getElementById('shorturl-provider').value = res.shorturl_provider || 'tinyurl';
                    document.getElementById('shorturl-api-key').value  = res.shorturl_api_key || '';
                    document.getElementById('shortio-domain').value     = res.shortio_domain || '';
                    toggleShortIoDomain();
                    // Update dynamic button label
                    updateShortUrlButtonLabel(res.shorturl_provider || 'tinyurl');
                }
            }
        }

        async function loadDashboard() {
            // Fetch all articles, then show last 10 + any flagged (further_action or in_progress)
            const res = await api('get_articles');
            const all = res.articles || [];
            const flaggedIds = new Set();
            all.filter(a => a.article_status === 'further_action' || a.article_status === 'in_progress').forEach(a => flaggedIds.add(a.id));
            all.slice(0, 10).forEach(a => flaggedIds.add(a.id));
            const merged = all.filter(a => flaggedIds.has(a.id));
            renderList(merged, 'recent-articles-list', true);
        }

        async function loadArticles() {
            const res = await api('get_articles');
            allArticlesData = res.articles || [];
            if (document.getElementById('sort-select').value === 'alpha') {
                allArticlesData.sort((a,b) => (a.headline||'').localeCompare(b.headline||''));
            }
            renderList(allArticlesData, 'all-articles-list', false);
        }

        function isOlderThan31Days(dateStr) {
            if (!dateStr) return false;
            try {
                const d = new Date(dateStr.replace(/-/g, '/'));
                return (Date.now() - d.getTime()) > 31 * 24 * 60 * 60 * 1000;
            } catch(e) { return false; }
        }

        function renderList(articles, containerId, isDashboard = false) {
            const container = document.getElementById(containerId);
            container.innerHTML = articles.length === 0 ? '<p class="text-muted">No articles found.</p>' : '';
            
            const roleCheck = (currentUserRole || '').toLowerCase();
            const isAdmin   = roleCheck === 'admin';
            const isEditor  = roleCheck === 'editor';
            const isUser    = roleCheck === 'user';

            articles.forEach(art => {
                // Status badge driven by article_status
                const artStatus = art.article_status || 'draft';
                let statusBadge, statusColor, statusTextColor, statusBorder;

                switch (artStatus) {
                    case 'approved':
                        statusBadge = '🟢 Approved';
                        statusColor = '#f0fdf4'; statusTextColor = '#166534'; statusBorder = '#bbf7d0';
                        break;
                    case 'in_progress':
                        statusBadge = '🔵 In Progress';
                        statusColor = '#eff6ff'; statusTextColor = '#1d4ed8'; statusBorder = '#bfdbfe';
                        break;
                    case 'review_pending':
                        statusBadge = '🟠 Review Pending';
                        statusColor = '#fff7ed'; statusTextColor = '#c2410c'; statusBorder = '#fed7aa';
                        break;
                    case 'further_action':
                        statusBadge = '🔴 Further Action Required';
                        statusColor = '#faf5ff'; statusTextColor = '#6d28d9'; statusBorder = '#ddd6fe';
                        break;
                    default:
                        statusBadge = '⚪ Draft';
                        statusColor = 'var(--bg-color)'; statusTextColor = 'var(--text-muted)'; statusBorder = 'var(--border-color)';
                }

                const statusHtml = `<span style="background:${statusColor}; border:1px solid ${statusBorder}; color:${statusTextColor}; padding:4px 8px; border-radius:4px; font-size:0.75rem; font-weight:600;">${statusBadge}</span>`;

                // Push labels — hidden from Users
                let pushHtml = '';
                if (!isUser) {
                    const wpSt = art.wp_status || '';
                    const pCount = Math.max(parseInt(art.push_count, 10) || 0, art.wp_pushed_at ? 1 : 0);
                    const dateStr = isDashboard ? '' : ` ${formatDateTimeStr(art.wp_pushed_at)}`;

                    if (pCount > 0) {
                        // Determine the WP publish state badge
                        let wpStateLabel = '', wpStateColor = '';
                        if (wpSt === 'publish') {
                            wpStateLabel = '🟢 Published'; wpStateColor = '#16a34a';
                        } else if (wpSt === 'future') {
                            wpStateLabel = '🔵 Scheduled'; wpStateColor = '#2563eb';
                        } else {
                            wpStateLabel = '⚪ Draft'; wpStateColor = '#6b7280';
                        }
                        const pushLabel = pCount > 1 ? 'Re-pushed' : 'Pushed';
                        const pushColor = pCount > 1 ? '#3a88fe' : '#10b981';
                        pushHtml = `<span style="background:${pushColor}; color:white; padding:4px 8px; border-radius:4px; font-size:0.75rem; font-weight:600;">${pushLabel}${dateStr}</span>` +
                                   `<span style="background:${wpStateColor}; color:white; padding:4px 8px; border-radius:4px; font-size:0.75rem; font-weight:600; margin-left:4px;">${wpStateLabel}</span>`;
                    } else {
                        pushHtml = `<span style="background:#b37601; color:white; padding:4px 8px; border-radius:4px; font-size:0.75rem; font-weight:600;">Not Pushed</span>`;
                    }
                }

                const isStale = !isDashboard && isOlderThan31Days(art.updated_at);
                const staleNotice = isStale ? `<br><small style="color:#2563eb;"><i>⟳ Article is 31+ days old — see Posts page for the live version.</i></small>` : '';
                container.innerHTML += `
                    <div class="list-item">
                        <div>
                            <strong>${art.headline || 'Untitled'}</strong> 
                            <br><small>Updated: ${art.updated_at}</small>
                            ${staleNotice}
                        </div>
                        <div class="list-item-actions">
                            ${statusHtml}
                            ${pushHtml}
                            <button onclick="editArticle(${art.id})" class="secondary icon-btn" title="View/Edit"><i data-feather="edit"></i></button>
                            <button onclick="trashArticle(${art.id})" class="danger icon-btn" title="Trash"><i data-feather="trash-2"></i></button>
                        </div>
                    </div>`;
            });
            feather.replace();
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

        async function loadWpPosts() {
            const roleCheck = (currentUserRole || '').toLowerCase();
            if (roleCheck !== 'admin' && roleCheck !== 'editor') return;
            const container = document.getElementById('wp-posts-list');
            if (!container) return;
            container.innerHTML = '<span style="color:var(--text-muted); font-size:0.9rem;">Loading WordPress posts...</span>';

            const res = await api('get_wp_posts');
            if (!res || !res.success) {
                container.innerHTML = `<span style="color:var(--danger-color); font-size:0.9rem;">${res?.error || 'Could not load WordPress posts. Check your WordPress connection in Settings.'}</span>`;
                return;
            }

            const posts = res.posts || [];
            if (posts.length === 0) {
                container.innerHTML = '<span style="color:var(--text-muted); font-size:0.9rem;">No posts found on WordPress.</span>';
                return;
            }

            const statusBadge = (s) => {
                const map = { publish: ['#16a34a','Published'], draft: ['#6b7280','Draft'], future: ['#2563eb','Scheduled'], pending: ['#d97706','Pending'], private: ['#7c3aed','Private'] };
                const [col, label] = map[s] || ['#6b7280', s];
                return `<span style="background:${col}; color:white; padding:2px 8px; border-radius:10px; font-size:0.75rem; font-weight:600; white-space:nowrap;">${label}</span>`;
            };

            container.innerHTML = '';
            posts.forEach(post => {
                const date = post.date ? post.date.replace('T',' ').slice(0,16) : '';
                container.innerHTML += `
                    <div class="list-item">
                        <div style="flex:1; min-width:0;">
                            <strong style="word-break:break-word;">${post.title || '(Untitled)'}</strong>
                            <br><small style="color:var(--text-muted);">Modified: ${date}</small>
                        </div>
                        <div class="list-item-actions" style="flex-shrink:0;">
                            ${statusBadge(post.status)}
                            <a href="${post.permalink}" target="_blank" class="secondary icon-btn" style="display:inline-flex; align-items:center; padding:6px; background:transparent; border:1px solid transparent; color:var(--text-color); border-radius:6px; text-decoration:none;" title="View on site"><i data-feather="eye"></i></a>
                            <a href="${post.edit_url}" target="_blank" class="secondary icon-btn" style="display:inline-flex; align-items:center; padding:6px; background:transparent; border:1px solid transparent; color:var(--text-color); border-radius:6px; text-decoration:none;" title="Edit in WordPress"><i data-feather="edit"></i></a>
                            <button onclick="trashWpPost(${post.id}, this)" class="danger icon-btn" title="Move to WordPress Trash"><i data-feather="trash-2"></i></button>
                        </div>
                    </div>`;
            });

            if (res.total > posts.length) {
                container.innerHTML += `<p style="font-size:0.8rem; color:var(--text-muted); margin-top:10px;">Showing ${posts.length} of ${res.total} posts.</p>`;
            }
            feather.replace();
        }

        async function trashWpPost(postId, btn) {
            showModal('Trash WordPress Post', 'Move this post to the WordPress Trash? It can be restored from the WordPress admin.', false, async () => {
                btn.disabled = true;
                const res = await api('trash_wp_post', 'POST', { post_id: postId });
                if (res && res.success) {
                    showToast('Post moved to WordPress Trash.');
                    loadWpPosts();
                } else {
                    btn.disabled = false;
                    showToast(res?.error || 'Failed to trash post.', true);
                }
            });
        }

        async function furtherActionArticle() {
            if (currentArticleId <= 0) return showToast('Save the article first.', true);
            const saved = await saveArticle(true);
            if (!saved) return;
            const res = await api('further_action_article', 'POST', { id: currentArticleId });
            if (res && res.success) {
                showToast('Article flagged — user will be notified to take further action.');
                const btn = document.getElementById('btn-further-action');
                if (btn) { btn.style.background = '#5b21b6'; btn.innerHTML = '<i data-feather="alert-circle"></i> Further Action Sent'; }
                const wpBadge = document.getElementById('editor-wp-badge');
                wpBadge.innerText = '🔴 Further Action Required';
                wpBadge.style.background = '#7c3aed';
                wpBadge.style.color = 'white';
                wpBadge.classList.remove('hidden');
                // Reset approve button back to unset state since it's no longer approved
                const approveBtn = document.getElementById('btn-approve-article');
                if (approveBtn) { approveBtn.style.background = '#16a34a'; approveBtn.innerHTML = '<i data-feather="check-circle"></i> Approve'; }
                feather.replace();
                loadDashboard();
            } else {
                showToast('Failed: ' + (res?.error || 'Unknown error'), true);
            }
        }

        async function approveArticle() {
            if (currentArticleId <= 0) return showToast('Save the article first before approving.', true);
            const res = await api('approve_article', 'POST', { id: currentArticleId });
            if (res && res.success) {
                showToast('Article approved!');
                const approveBtn = document.getElementById('btn-approve-article');
                if (approveBtn) {
                    approveBtn.style.background = '#15803d';
                    approveBtn.innerHTML = '<i data-feather="check-circle"></i> Approved';
                }
                const wpBadge = document.getElementById('editor-wp-badge');
                wpBadge.innerText = '🟢 Approved';
                wpBadge.style.background = '#16a34a';
                wpBadge.style.color = 'white';
                wpBadge.classList.remove('hidden');
                feather.replace();
                loadDashboard();
            } else {
                showToast('Failed to approve: ' + (res?.error || 'Unknown error'), true);
            }
        }

        async function submitArticle(isResubmit = false) {
            if (!validateImageRequired()) return;
            const saved = await saveArticle(true);
            if (!saved) return;

            const submitBtn = document.getElementById('btn-submit-article');
            // Any re-submit (further_action, review_pending, in_progress, approved) → resubmit_article (in_progress)
            // Only a brand-new first submission → submit_article (review_pending)
            const isAnyResubmit = isResubmit || (submitBtn && submitBtn.dataset.resubmit === '1');
            const action = isAnyResubmit ? 'resubmit_article' : 'submit_article';

            const res = await api(action, 'POST', { id: currentArticleId });
            if (res && res.success) {
                const wpBadge = document.getElementById('editor-wp-badge');
                if (isAnyResubmit) {
                    showToast('Article re-submitted — marked as Review Pending!');
                    if (submitBtn) {
                        submitBtn.style.background = '#b45309';
                        submitBtn.innerHTML = '<i data-feather="refresh-cw"></i> Re-Submit';
                        submitBtn.disabled = false;
                        submitBtn.dataset.resubmit = '1';
                    }
                    wpBadge.innerText = '🟠 Review Pending';
                    wpBadge.style.background = '#c2410c';
                } else {
                    showToast('Article submitted — marked as Review Pending!');
                    if (submitBtn) {
                        submitBtn.style.background = '#b45309';
                        submitBtn.innerHTML = '<i data-feather="refresh-cw"></i> Re-Submit';
                        submitBtn.disabled = false;
                        submitBtn.dataset.resubmit = '1';
                    }
                    wpBadge.innerText = '🟠 Review Pending';
                    wpBadge.style.background = '#c2410c';
                }
                wpBadge.style.color = 'white';
                wpBadge.classList.remove('hidden');
                feather.replace();
                loadDashboard();
            } else {
                showToast('Failed to submit: ' + (res?.error || 'Unknown error'), true);
            }
        }

        async function loadWpMeta() {
            const authorSel = document.getElementById('wp-author');
            const catSel = document.getElementById('wp-category');
            authorSel.innerHTML = '<option value="">Loading...</option>';
            catSel.innerHTML = '<option value="" disabled>Loading...</option>';
            const res = await api('get_wp_meta');
            if (res && res.success) {
                authorSel.innerHTML = '<option value="">Default/Current User</option>';
                res.authors.forEach(a => authorSel.innerHTML += `<option value="${a.id}">${a.name}</option>`);
                catSel.innerHTML = '';
                res.categories.forEach(c => catSel.innerHTML += `<option value="${c.id}">${c.name}</option>`);
            } else {
                authorSel.innerHTML = '<option value="">Default/Current User</option>';
                catSel.innerHTML = '<option value="" disabled>— WP not connected —</option>';
            }
        }

        async function createNewArticle() {
            currentArticleId = 0; currentWpPostId = null; 
            currentPermalink = null; currentShortUrl = null;
            updateSocialButton();

            document.getElementById('edit-original').value = '';
            document.getElementById('edit-headline').value = '';
            document.getElementById('edit-content').value = '';
            document.getElementById('edit-social1').value = '';
            document.getElementById('edit-social2').value = '';
            document.getElementById('edit-hashtags').value = '';
            document.getElementById('edit-social1-hidden').value = '';
            document.getElementById('edit-social2-hidden').value = '';
            document.getElementById('edit-hashtags-hidden').value = '';
            
            document.getElementById('edit-image-sug').innerText = '';
            document.getElementById('social-img-sug-container').classList.add('hidden');
            
            document.getElementById('wp-tags').value = '';
            document.getElementById('wp-status').value = 'draft';
            document.getElementById('wp-date').value = '';
            
            removeImage();

            document.getElementById('editor-wp-badge').classList.add('hidden');
            document.getElementById('url-container').classList.add('hidden');
            document.getElementById('btn-wp-push').innerHTML = '<i data-feather="upload-cloud"></i> Push to Site';
            const approveBtn = document.getElementById('btn-approve-article');
            if (approveBtn) { approveBtn.style.background = '#16a34a'; approveBtn.innerHTML = '<i data-feather="check-circle"></i> Approve'; }
            const furtherBtn = document.getElementById('btn-further-action');
            if (furtherBtn) { furtherBtn.style.background = '#7c3aed'; furtherBtn.innerHTML = '<i data-feather="alert-circle"></i> Further Action'; }
            const submitBtn = document.getElementById('btn-submit-article');
            if (submitBtn) { submitBtn.dataset.resubmit = '0'; submitBtn.style.background = '#f59e0b'; submitBtn.innerHTML = '<i data-feather="send"></i> Submit Article'; submitBtn.disabled = false; }
            document.getElementById('spellcheck-results').classList.add('hidden');
            document.getElementById('spellcheck-results').innerHTML = '';
            const simPanelNew = document.getElementById('similarity-panel');
            if (simPanelNew) simPanelNew.classList.add('hidden');
            spinSourceContent = '';

            await loadWpMeta(); updateKeywordDensity(); updateHumanScore(); updateStats(); feather.replace(); switchView('editor');
        }

        async function editArticle(id) {
            const res = await api('get_articles');
            const art = res.articles.find(a => a.id == id);
            if (art) {
                currentArticleId = art.id; currentWpPostId = art.wp_post_id;
                currentPermalink = art.wp_permalink || null; currentShortUrl = art.short_url || null;
                updateSocialButton();
                
                document.getElementById('edit-original').value = art.original_content || '';
                document.getElementById('edit-headline').value = art.headline || '';
                document.getElementById('edit-content').value = art.article_content || '';
                document.getElementById('edit-social1').value = art.social_1 || '';
                document.getElementById('edit-social2').value = art.social_2 || '';
                document.getElementById('edit-hashtags').value = art.hashtags || '';
                // Also populate hidden fields so user-role saves preserve these values
                document.getElementById('edit-social1-hidden').value = art.social_1 || '';
                document.getElementById('edit-social2-hidden').value = art.social_2 || '';
                document.getElementById('edit-hashtags-hidden').value = art.hashtags || '';
                
                if(art.image_suggestion) {
                    document.getElementById('edit-image-sug').innerText = art.image_suggestion;
                    document.getElementById('social-img-sug-container').classList.remove('hidden');
                } else {
                    document.getElementById('edit-image-sug').innerText = '';
                    document.getElementById('social-img-sug-container').classList.add('hidden');
                }
                
                document.getElementById('wp-tags').value = art.wp_tags || '';
                document.getElementById('wp-status').value = art.wp_status || 'draft';
                document.getElementById('wp-date').value = art.wp_date || '';
                
                const imgBase64 = art.featured_image || '';
                document.getElementById('edit-image-base64').value = imgBase64;
                if (imgBase64) {
                    document.getElementById('image-preview').src = imgBase64;
                    document.getElementById('image-preview').classList.remove('hidden');
                    document.getElementById('image-details-container').classList.remove('hidden');
                    document.getElementById('img-title').value = art.img_title || '';
                    document.getElementById('img-alt').value = art.img_alt || '';
                    document.getElementById('img-caption').value = art.img_caption || '';
                } else {
                    removeImage();
                }
                
                await loadWpMeta();
                if(art.wp_author) document.getElementById('wp-author').value = art.wp_author;
                if(art.wp_categories) {
                    const savedCats = JSON.parse(art.wp_categories);
                    Array.from(document.getElementById('wp-category').options).forEach(opt => {
                        if(savedCats.includes(parseInt(opt.value))) opt.selected = true;
                    });
                }
                
                const wpBadge = document.getElementById('editor-wp-badge');
                const wpBtn = document.getElementById('btn-wp-push');
                const artStatus = art.article_status || 'draft';
                
                if (artStatus === 'approved') {
                    let pCount = parseInt(art.push_count, 10) || 0;
                    if (pCount === 0 && art.wp_pushed_at) pCount = 1;
                    let pText = pCount > 1 ? 'Re-pushed' : 'Pushed';
                    wpBadge.innerText = `${pText} on ${formatDateTimeStr(art.wp_pushed_at)}`;
                    wpBadge.style.background = pCount > 1 ? '#3a88fe' : '#0cf055';
                    wpBadge.style.color = 'white';
                    wpBadge.classList.remove('hidden');
                    wpBtn.innerHTML = '<i data-feather="refresh-cw"></i> Re-Push to Site';
                } else if (artStatus === 'further_action') {
                    wpBadge.innerText = '🔴 Further Action Required';
                    wpBadge.style.background = '#7c3aed';
                    wpBadge.style.color = 'white';
                    wpBadge.classList.remove('hidden');
                    wpBtn.innerHTML = '<i data-feather="upload-cloud"></i> Push to Site';
                } else if (artStatus === 'in_progress') {
                    wpBadge.innerText = '🔵 In Progress';
                    wpBadge.style.background = '#1d4ed8';
                    wpBadge.style.color = 'white';
                    wpBadge.classList.remove('hidden');
                    wpBtn.innerHTML = '<i data-feather="upload-cloud"></i> Push to Site';
                } else if (artStatus === 'review_pending') {
                    wpBadge.innerText = '🟠 Review Pending';
                    wpBadge.style.background = '#c2410c';
                    wpBadge.style.color = 'white';
                    wpBadge.classList.remove('hidden');
                    wpBtn.innerHTML = '<i data-feather="upload-cloud"></i> Push to Site';
                } else {
                    wpBadge.classList.add('hidden');
                    wpBtn.innerHTML = '<i data-feather="upload-cloud"></i> Push to Site';
                }

                // Reset approve button state for editor/admin
                const approveBtn = document.getElementById('btn-approve-article');
                if (approveBtn) {
                    if (artStatus === 'approved') {
                        approveBtn.style.background = '#15803d';
                        approveBtn.innerHTML = '<i data-feather="check-circle"></i> Approved';
                    } else {
                        approveBtn.style.background = '#16a34a';
                        approveBtn.innerHTML = '<i data-feather="check-circle"></i> Approve';
                    }
                }

                // Reset further action button state for editor/admin
                const furtherBtn = document.getElementById('btn-further-action');
                if (furtherBtn) {
                    if (artStatus === 'further_action') {
                        furtherBtn.style.background = '#5b21b6';
                        furtherBtn.innerHTML = '<i data-feather="alert-circle"></i> Further Action Sent';
                    } else {
                        furtherBtn.style.background = '#7c3aed';
                        furtherBtn.innerHTML = '<i data-feather="alert-circle"></i> Further Action';
                    }
                }

                const roleForUrl = (currentUserRole || '').toLowerCase();
                if ((currentShortUrl || currentPermalink) && (roleForUrl === 'admin' || roleForUrl === 'editor')) {
                    document.getElementById('pub-link').href = currentPermalink || '#';
                    document.getElementById('pub-link').innerText = currentPermalink || '';
                    document.getElementById('url-container').classList.remove('hidden');
                    setShortlinkUI(currentShortUrl || null);
                } else {
                    document.getElementById('url-container').classList.add('hidden');
                }

                document.getElementById('spellcheck-results').classList.add('hidden');
                document.getElementById('spellcheck-results').innerHTML = '';

                // Reset submit button for user role
                const submitBtn = document.getElementById('btn-submit-article');
                if (submitBtn) {
                    const artSt = art.article_status || 'draft';
                    if (artSt === 'further_action') {
                        // Editor/Admin requested changes — show prominent Re-Submit
                        submitBtn.dataset.resubmit = '1';
                        submitBtn.style.background = '#7c3aed';
                        submitBtn.innerHTML = '<i data-feather="send"></i> Re-Submit for Review';
                        submitBtn.disabled = false;
                    } else if (artSt === 'review_pending') {
                        // Already submitted — allow re-submission after edits
                        submitBtn.dataset.resubmit = '1';
                        submitBtn.style.background = '#b45309';
                        submitBtn.innerHTML = '<i data-feather="refresh-cw"></i> Re-Submit';
                        submitBtn.disabled = false;
                    } else if (artSt === 'in_progress' || artSt === 'approved') {
                        // Always allow user to re-submit after editing
                        submitBtn.dataset.resubmit = '1';
                        submitBtn.style.background = '#b45309';
                        submitBtn.innerHTML = '<i data-feather="refresh-cw"></i> Re-Submit';
                        submitBtn.disabled = false;
                    } else {
                        submitBtn.dataset.resubmit = '0';
                        submitBtn.style.background = '#f59e0b';
                        submitBtn.innerHTML = '<i data-feather="send"></i> Submit Article';
                        submitBtn.disabled = false;
                    }
                }

                // Restore similarity panel if this article has source content to compare against
                spinSourceContent = art.original_content ? art.original_content.trim() : '';

                updateKeywordDensity(); updateHumanScore(); updateStats(); updateSimilarity(); feather.replace(); switchView('editor');
            }
        }

        function validateImageFields() {
            if (document.getElementById('edit-image-base64').value) {
                if (!document.getElementById('img-title').value.trim() ||
                    !document.getElementById('img-alt').value.trim()) {
                    showToast('Image Title and Alt Text must be entered', true);
                    return false;
                }
            }
            return true;
        }

        // Enforces that an image IS attached (used before Submit/Push)
        function validateImageRequired() {
            if (!document.getElementById('edit-image-base64').value) {
                showToast('A featured image (min 1200×675 px) must be attached before submitting or pushing.', true);
                return false;
            }
            return validateImageFields();
        }

        async function saveArticle(silent = false) {
            if (!validateImageFields()) return false;

            updateStats();
            const catSelect = document.getElementById('wp-category');
            const selectedCats = Array.from(catSelect.selectedOptions).map(opt => parseInt(opt.value));

            // Determine article_status: editors/admins saving without push → in_progress
            // Users saving → keep existing status (don't downgrade from review_pending)
            const roleCheck = (currentUserRole || '').toLowerCase();
            const isEditorOrAdmin = roleCheck === 'editor' || roleCheck === 'admin';
            let articleStatus = null;
            if (isEditorOrAdmin) {
                articleStatus = 'in_progress';
            }
            // For users, we don't send article_status so the DB value is preserved

            const data = {
                id: currentArticleId,
                original_content: document.getElementById('edit-original').value,
                headline: document.getElementById('edit-headline').value,
                article_content: document.getElementById('edit-content').value,
                social_1: document.getElementById('edit-social1').value || document.getElementById('edit-social1-hidden').value,
                social_2: document.getElementById('edit-social2').value || document.getElementById('edit-social2-hidden').value,
                hashtags: document.getElementById('edit-hashtags').value || document.getElementById('edit-hashtags-hidden').value,
                image_suggestion: document.getElementById('edit-image-sug').innerText,
                featured_image: document.getElementById('edit-image-base64').value,
                img_title: document.getElementById('img-title').value,
                img_alt: document.getElementById('img-alt').value,
                img_caption: document.getElementById('img-caption').value,
                wp_author: document.getElementById('wp-author').value,
                wp_status: document.getElementById('wp-status').value,
                wp_categories: JSON.stringify(selectedCats),
                wp_tags: document.getElementById('wp-tags').value,
                wp_date: document.getElementById('wp-date').value
            };
            if (articleStatus) data.article_status = articleStatus;

            const res = await api('save_article', 'POST', data);
            
            if (res.success) {
                currentArticleId = res.id;
                if(!silent) showToast('Article saved successfully!');
                if(!silent) loadDashboard(); 
                return true;
            } else {
                showToast('Failed to save: ' + (res.error || 'Unknown error'), true);
                return false;
            }
        }

        function updateKeywordDensity() {
            const content = document.getElementById('edit-content').value.toLowerCase();
            const words = content.match(/\b\w+\b/g) || [];
            const totalWords = words.length;
            const panel = document.getElementById('keyword-density-panel');
            
            currentKeywordHits = 0; 
            
            if(totalWords === 0) { panel.innerHTML = '<span class="text-muted">Start writing to see keyword density.</span>'; return; }

            const keywordsStr = appSettings.local_keywords || "";
            if(!keywordsStr) { panel.innerHTML = '<span class="text-muted">No target keywords configured in System.</span>'; return; }

            const keywords = keywordsStr.split(',').map(k => k.trim().toLowerCase()).filter(k => k);
            let html = '';
            let keywordsFound = 0;

            keywords.forEach(kw => {
                const regex = new RegExp(`\\b${kw}\\b`, 'gi');
                const matches = content.match(regex);
                const count = matches ? matches.length : 0;
                currentKeywordHits += count;
                if(count > 0) {
                    keywordsFound++;
                    const density = ((count / totalWords) * 100).toFixed(1);
                    html += `<span style="background:#e0f2fe; color:#0369a1; padding:2px 6px; border-radius:4px; margin-right:5px; display:inline-block; margin-bottom:5px;">${kw}: ${count} (${density}%)</span>`;
                }
            });
            
            const totalDensity = ((currentKeywordHits / totalWords) * 100).toFixed(1);

            // SEO Score: weighted combination of keyword coverage and density
            const coverageScore = keywords.length > 0 ? Math.min(100, Math.round((keywordsFound / keywords.length) * 60)) : 0; // 60pts for coverage
            const idealDensity = 2.5; // ideal ~2.5% total keyword density
            const densityVal = parseFloat(totalDensity);
            let densityScore = 0;
            if (densityVal > 0 && densityVal <= idealDensity) {
                densityScore = Math.round((densityVal / idealDensity) * 30); // up to 30pts
            } else if (densityVal > idealDensity) {
                densityScore = Math.max(0, Math.round(30 - ((densityVal - idealDensity) * 8))); // penalise over-stuffing
            }
            const lengthScore = Math.min(10, Math.round((totalWords / 300) * 10)); // up to 10pts for length (300+ words = full)
            const seoScore = Math.min(100, coverageScore + densityScore + lengthScore);

            let scoreColor = '#dc2626';
            let scoreLabel = 'Poor';
            if (seoScore >= 80) { scoreColor = '#16a34a'; scoreLabel = 'Excellent'; }
            else if (seoScore >= 60) { scoreColor = '#d97706'; scoreLabel = 'Good'; }
            else if (seoScore >= 40) { scoreColor = '#ea580c'; scoreLabel = 'Fair'; }

            panel.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <strong>Local Keywords (${totalDensity}% total density)</strong>
                    <span style="background:${scoreColor}; color:white; padding:3px 10px; border-radius:12px; font-size:0.8rem; font-weight:700;">SEO Score: ${seoScore}/100 — ${scoreLabel}</span>
                </div>` + (html || '<span style="color:var(--danger-color)">No target keywords detected.</span>');
        }

        function updateHumanScore() {
            const panel = document.getElementById('human-score-panel');
            if (!panel) return;
            const text = (document.getElementById('edit-content') || {}).value || '';
            const words = text.match(/\b\w+\b/g) || [];
            const totalWords = words.length;

            if (totalWords < 30) {
                panel.innerHTML = '<span class="text-muted">Human Score: write at least 30 words to analyse.</span>';
                return;
            }

            // --- Penalty signals (patterns strongly associated with AI-generated text) ---
            let penalties = 0;
            let signals = [];

            // 1. Filler openers — AI loves to start with these
            const fillerOpeners = [
                /\bin today'?s (fast[- ]?paced|digital|modern|ever[- ]?changing)/i,
                /\bin (an era|a world) (of|where)/i,
                /\bit'?s (no secret|worth noting|important to note|crucial to)/i,
                /\bwhen it comes to\b/i,
                /\bin (recent|today'?s) (times|years|climate)/i,
                /\bwithout further ado\b/i,
                /\blet'?s (dive in|explore|delve)/i,
                /\bwithout (a )?doubt\b/i,
                /\b(needless to say|it goes without saying)\b/i,
                /\bin (this|the following) (article|post|piece|blog)/i,
            ];
            fillerOpeners.forEach(rx => {
                if (rx.test(text)) { penalties += 6; signals.push('Filler opener phrase'); }
            });

            // 2. Transitional AI clichés
            const cliches = [
                /\bfurthermore\b/gi, /\bmoreover\b/gi, /\bnevertheless\b/gi,
                /\bnotwithstanding\b/gi, /\bconsequently\b/gi, /\bin conclusion\b/gi,
                /\bto summarize\b/gi, /\bin summary\b/gi, /\bin essence\b/gi,
                /\bit is worth (noting|mentioning)\b/gi, /\bone must consider\b/gi,
                /\bultimately\b/gi, /\bsignificantly\b/gi, /\bsubstantially\b/gi,
                /\bunderscores? the (importance|need|fact)\b/gi,
                /\bpaves? the way\b/gi, /\bshed(s)? light on\b/gi,
                /\bring(s)? true\b/gi, /\bstands? to reason\b/gi,
                /\bdelve(s)? (deeper|into)\b/gi, /\btapestry\b/gi,
                /\blandscape\b/gi, /\bparadigm\b/gi, /\bsynergy\b/gi,
                /\bgame[- ]?changer\b/gi, /\bseamlessly\b/gi, /\brobust\b/gi,
                /\bpivotal\b/gi, /\bfostering\b/gi,
            ];
            let clicheHits = 0;
            const seenCliche = {};
            cliches.forEach(rx => {
                const key = rx.source;
                if (!seenCliche[key]) {
                    const m = text.match(rx);
                    if (m) { clicheHits += m.length; seenCliche[key] = true; }
                }
            });
            if (clicheHits > 0) {
                const pen = Math.min(30, clicheHits * 4);
                penalties += pen;
                signals.push('AI clichés detected (' + clicheHits + ')');
            }

            // 3. Sentence length variance — AI tends to produce very uniform sentence lengths
            const sentences = text.split(/[.!?]+/).map(s => s.trim()).filter(s => s.length > 5);
            if (sentences.length >= 4) {
                const lens = sentences.map(s => (s.match(/\b\w+\b/g) || []).length);
                const mean = lens.reduce((a, b) => a + b, 0) / lens.length;
                const variance = lens.reduce((a, b) => a + Math.pow(b - mean, 2), 0) / lens.length;
                const stdDev = Math.sqrt(variance);
                const cv = mean > 0 ? stdDev / mean : 0; // Coefficient of variation
                if (cv < 0.25) { penalties += 12; signals.push('Very uniform sentence length'); }
                else if (cv < 0.4) { penalties += 5; signals.push('Low sentence length variance'); }
            }

            // 4. Passive voice overuse
            const passiveMatches = (text.match(/\b(is|are|was|were|be|been|being)\s+\w+ed\b/gi) || []).length;
            if (sentences.length > 0) {
                const passiveRate = passiveMatches / sentences.length;
                if (passiveRate > 0.5) { penalties += 10; signals.push('High passive voice rate'); }
                else if (passiveRate > 0.3) { penalties += 5; signals.push('Moderate passive voice'); }
            }

            // 5. Repetitive paragraph starters — AI often opens every paragraph the same way
            const paragraphs = text.split(/\n\n+/).map(p => p.trim()).filter(p => p.length > 10);
            if (paragraphs.length >= 3) {
                const starters = paragraphs.map(p => (p.split(/\s+/)[0] || '').toLowerCase());
                const starterCounts = {};
                starters.forEach(s => { starterCounts[s] = (starterCounts[s] || 0) + 1; });
                const maxRepeat = Math.max(...Object.values(starterCounts));
                if (maxRepeat >= 3) { penalties += 10; signals.push('Repetitive paragraph starters'); }
                else if (maxRepeat >= 2 && paragraphs.length >= 4) { penalties += 4; }
            }

            // 6. Adverb overload (AI loves unnecessary adverbs)
            const adverbs = (text.match(/\b(very|quite|really|extremely|incredibly|absolutely|completely|totally|highly|deeply|strongly|greatly|vastly|overly)\b/gi) || []).length;
            if (totalWords > 0 && adverbs / totalWords > 0.015) { penalties += 8; signals.push('Adverb overload'); }

            // 7. Hedge phrases (AI hedging)
            const hedges = (text.match(/\b(it (is|was|seems|appears)|there (is|are|was|were)|one (can|could|might|may|should))\b/gi) || []).length;
            if (sentences.length > 0 && hedges / sentences.length > 0.3) { penalties += 7; signals.push('Excessive hedge phrases'); }

            // --- Compute final score ---
            const rawScore = Math.max(0, 100 - penalties);
            // Clamp and apply slight curve — make 100 genuinely hard to reach
            const humanScore = Math.min(99, rawScore);

            let scoreColor = '#16a34a'; let scoreLabel = 'Sounds Human';
            if (humanScore < 40)      { scoreColor = '#dc2626'; scoreLabel = 'Clearly AI'; }
            else if (humanScore < 60) { scoreColor = '#ea580c'; scoreLabel = 'Likely AI'; }
            else if (humanScore < 75) { scoreColor = '#d97706'; scoreLabel = 'Somewhat AI'; }
            else if (humanScore < 88) { scoreColor = '#65a30d'; scoreLabel = 'Mostly Human'; }

            const signalHtml = signals.length > 0
                ? signals.map(s => `<span style="background:#fee2e2; color:#991b1b; padding:1px 6px; border-radius:4px; margin-right:4px; display:inline-block; margin-bottom:4px; font-size:0.78rem;">${s}</span>`).join('')
                : '<span style="color:#16a34a; font-size:0.8rem;">✓ No strong AI patterns detected</span>';

            panel.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <strong>Human Score</strong>
                    <span style="background:${scoreColor}; color:white; padding:3px 10px; border-radius:12px; font-size:0.8rem; font-weight:700;">${humanScore}/100 — ${scoreLabel}</span>
                </div>
                <div style="background:var(--border-color); border-radius:4px; height:6px; margin-bottom:8px;">
                    <div style="background:${scoreColor}; width:${humanScore}%; height:100%; border-radius:4px; transition:width 0.4s;"></div>
                </div>
                ${signalHtml}`;
        }

        async function regenerateHeadline() {
            const articleContent = document.getElementById('edit-content').value;
            const originalContent = document.getElementById('edit-original').value;
            const source = articleContent || originalContent;
            if (!source) return showToast('Generate an article first, then re-generate the headline.', true);

            const btn = document.getElementById('btn-regen-headline');
            btn.innerHTML = 'Generating...'; btn.disabled = true;

            const lang = (appSettings && appSettings.language_variant) || 'British';
            const systemPrompt = `You are an expert digital journalist. Generate ONE short, punchy, SEO-optimised news headline in ${lang} English for the article below. Return ONLY the headline text — no quotes, no label, no explanation.`;
            const res = await api('generate_article', 'POST', { content: source, headline_only: true, _sys: systemPrompt });

            btn.innerHTML = '<i data-feather="refresh-cw"></i> New Headline'; btn.disabled = false; feather.replace();

            if (res && res.success && res.headline) {
                document.getElementById('edit-headline').value = res.headline;
                showToast('Headline regenerated!');
            } else {
                showToast('Failed to regenerate headline: ' + (res?.error || 'Unknown error'), true);
            }
        }

        async function generateArticle() {
            const content = document.getElementById('edit-original').value;
            if(!content) return showToast("Please paste original content first.", true);
            
            const btn = document.getElementById('btn-generate');
            btn.innerHTML = 'Generating...'; btn.disabled = true;
            
            const res = await api('generate_article', 'POST', {content});
            btn.innerHTML = '<i data-feather="cpu"></i> Generate Article'; btn.disabled = false; feather.replace();
            
            if(res && res.success) {
                document.getElementById('edit-headline').value = res.headline || '';
                document.getElementById('edit-content').value = res.article_content || '';
                // Hide similarity panel — not applicable for a fresh generate
                const simPanel = document.getElementById('similarity-panel');
                if (simPanel) simPanel.classList.add('hidden');
                spinSourceContent = '';
                updateKeywordDensity(); updateHumanScore(); updateStats(); showToast('Article generated successfully!');
            } else showToast("Failed to generate: " + (res.error || "Error"), true);
        }

        // Compute a simple word-overlap similarity ratio between two texts (Jaccard-like)
        function computeSimilarity(textA, textB) {
            const tokenize = t => new Set((t.toLowerCase().match(/\b\w{3,}\b/g) || []));
            const a = tokenize(textA);
            const b = tokenize(textB);
            if (a.size === 0 || b.size === 0) return 0;
            let intersection = 0;
            a.forEach(w => { if (b.has(w)) intersection++; });
            const union = new Set([...a, ...b]).size;
            return Math.round((intersection / union) * 100);
        }

        let spinSourceContent = ''; // stores original source when spinning

        function updateSimilarity() {
            if (!spinSourceContent) return; // nothing to compare against
            const panel = document.getElementById('similarity-panel');
            if (!panel) return;
            // Ensure the panel is visible whenever we have a source to compare
            panel.classList.remove('hidden');
            const spun = document.getElementById('edit-content').value;
            const score = computeSimilarity(spinSourceContent, spun);
            const bar = document.getElementById('similarity-bar');
            const badge = document.getElementById('similarity-score-badge');
            let color = '#16a34a'; // green = low similarity (good)
            if (score > 70) color = '#dc2626';
            else if (score > 40) color = '#d97706';
            bar.style.width = score + '%';
            bar.style.background = color;
            badge.style.background = color;
            badge.innerText = score + '%';
        }

        function recheckSimilarity() {
            // Use the original source pasted in the Source Content box as the baseline,
            // falling back to the stored spinSourceContent if the box has been cleared.
            const sourceBox = document.getElementById('edit-original').value.trim();
            if (sourceBox) spinSourceContent = sourceBox;
            if (!spinSourceContent) {
                showToast('No original source content available to compare against.', true);
                return;
            }
            const panel = document.getElementById('similarity-panel');
            if (panel) panel.classList.remove('hidden');
            updateSimilarity();
            showToast('Similarity rechecked!');
            feather.replace();
        }

        async function spinArticle() {
            const content = document.getElementById('edit-original').value;
            if(!content) return showToast("Please paste original content first.", true);
            
            const btn = document.getElementById('btn-spin');
            btn.innerHTML = 'Spinning...'; btn.disabled = true;
            
            const res = await api('spin_article', 'POST', {content});
            btn.innerHTML = '<i data-feather="refresh-cw"></i> Spin Article'; btn.disabled = false; feather.replace();
            
            if(res && res.success) {
                spinSourceContent = content; // save original for similarity tracking
                document.getElementById('edit-headline').value = res.headline || '';
                document.getElementById('edit-content').value = res.article_content || '';
                updateKeywordDensity(); updateHumanScore(); updateStats();
                // Show similarity panel
                const simPanel = document.getElementById('similarity-panel');
                if (simPanel) { simPanel.classList.remove('hidden'); updateSimilarity(); }
                showToast('Spun article generated successfully!');
            } else showToast("Failed to spin article: " + (res.error || "Unknown error"), true);
        }

        function processImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = new Image();
                    img.onload = function() {
                        const MIN_W = 1200, MIN_H = 675;
                        if (img.width < MIN_W || img.height < MIN_H) {
                            showToast(`Image too small (${img.width}×${img.height}). Minimum required: ${MIN_W}×${MIN_H} pixels.`, true);
                            input.value = '';
                            return;
                        }
                        const canvas = document.createElement('canvas');
                        // Preserve aspect ratio; cap width at 1920 for file size
                        const MAX_WIDTH = 1920;
                        let width = img.width; let height = img.height;
                        if (width > MAX_WIDTH) { height = Math.round((height * MAX_WIDTH) / width); width = MAX_WIDTH; }
                        canvas.width = width; canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);
                        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                        
                        document.getElementById('image-preview').src = dataUrl;
                        document.getElementById('image-preview').classList.remove('hidden');
                        document.getElementById('edit-image-base64').value = dataUrl;
                        
                        document.getElementById('image-details-container').classList.remove('hidden');
                        
                        if(!document.getElementById('img-title').value) {
                            document.getElementById('img-title').value = document.getElementById('edit-headline').value;
                        }
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
        
        function removeImage() {
            document.getElementById('edit-image-file').value = '';
            document.getElementById('edit-image-base64').value = '';
            document.getElementById('image-preview').src = '';
            document.getElementById('image-preview').classList.add('hidden');
            document.getElementById('image-details-container').classList.add('hidden');
            document.getElementById('img-title').value = '';
            document.getElementById('img-alt').value = '';
            document.getElementById('img-caption').value = '';
        }

        function updateSocialButton() {
            const btn = document.getElementById('btn-social');
            if (!btn) return;
            const pushed = !!currentWpPostId;
            btn.disabled = !pushed;
            btn.title    = pushed ? '' : 'Push article to WordPress first';
            btn.style.opacity = pushed ? '' : '0.5';
        }

        async function generateSocial() {
            const content = document.getElementById('edit-content').value;
            if(!content) return showToast("Generate an article first.", true);
            
            showToast("Generating Social Content and Image Prompt...");
            const payload = {
                article_content: content,
                short_url: currentShortUrl,
                article_id: currentArticleId
            };

            const res = await api('generate_social', 'POST', payload);
            if(res && res.success) {
                // Server already guarantees the shortlink is appended; use values as-is
                document.getElementById('edit-social1').value = res.social_1 || '';
                document.getElementById('edit-social2').value = res.social_2 || '';
                document.getElementById('edit-hashtags').value = res.hashtags || '';

                // If the server resolved a shortlink we didn't have, cache it locally and update UI
                if (res.short_url && !currentShortUrl) {
                    currentShortUrl = res.short_url;
                    setShortlinkUI(currentShortUrl);
                    document.getElementById('url-container').classList.remove('hidden');
                }
                
                if (res.image_suggestion) {
                    document.getElementById('edit-image-sug').innerText = res.image_suggestion;
                    document.getElementById('social-img-sug-container').classList.remove('hidden');
                }
                showToast('Social content generated successfully!');
            } else showToast("Failed to generate social posts: " + (res.error || "Unknown error"), true);
        }

        async function generateTags() {
            const content = document.getElementById('edit-content').value;
            const headline = document.getElementById('edit-headline').value;
            if (!content && !headline) return showToast("Generate or write an article first.", true);

            const btn = document.getElementById('btn-generate-tags');
            btn.innerHTML = '<i data-feather="loader"></i> Generating...'; btn.disabled = true; feather.replace();

            const res = await api('generate_tags', 'POST', { article_content: content, headline });
            btn.innerHTML = '<i data-feather="tag"></i> Generate Tags'; btn.disabled = false; feather.replace();

            if (res && res.success) {
                document.getElementById('wp-tags').value = res.tags || '';
                showToast('Tags generated!');
            } else {
                showToast('Failed to generate tags: ' + (res?.error || 'Unknown error'), true);
            }
        }

        async function checkSpelling() {
            const content = document.getElementById('edit-content').value;
            if(!content) return showToast("Please write or generate article content first.", true);
            const btn = document.getElementById('btn-spellcheck');
            const resDiv = document.getElementById('spellcheck-results');
            btn.innerHTML = 'Checking...'; btn.disabled = true;

            const res = await api('check_spelling', 'POST', { article_content: content });
            btn.innerHTML = '<i data-feather="check-square"></i> Check Spelling'; btn.disabled = false; feather.replace();

            if (res && res.success) {
                resDiv.innerHTML = '';
                if (!res.errors || res.errors.length === 0) {
                    resDiv.className = 'card'; resDiv.style.borderColor = '#10b981'; resDiv.style.background = 'rgba(16, 185, 129, 0.05)';
                    resDiv.innerHTML = '<p style="color:#10b981; margin:0; font-weight:600;"><i data-feather="check-circle" style="vertical-align:middle; width:16px; height:16px; margin-right:5px;"></i> Perfect! No spelling errors detected.</p>';
                } else {
                    resDiv.className = 'card'; resDiv.style.borderColor = 'var(--danger-color)'; resDiv.style.background = 'rgba(220, 38, 38, 0.05)';
                    let html = '<p style="color:var(--danger-color); margin:0 0 10px 0; font-weight:600;"><i data-feather="alert-circle" style="vertical-align:middle; width:16px; height:16px; margin-right:5px;"></i> Flagged Words / Spelling Variants Needed:</p><ul style="margin:0; padding-left:20px; line-height:1.5;">';
                    res.errors.forEach(err => {
                        const wordEscaped = err.word.replace(/'/g, "\\'"); const sugEscaped = err.suggestion.replace(/'/g, "\\'");
                        html += `<li>Found <strong>"${err.word}"</strong>. Suggestion: <span style="color:#10b981; font-weight:bold;">${err.suggestion}</span> 
                        <button onclick="replaceWord(this, '${wordEscaped}', '${sugEscaped}')" style="background-color:var(--danger-color); color:white; border:none; padding:3px 8px; border-radius:4px; font-size:0.75rem; cursor:pointer; margin-left:10px;">Apply</button>
                        <br><small class="text-muted">Context: ...${err.context}...</small></li>`;
                    });
                    html += '</ul>'; resDiv.innerHTML = html;
                }
                resDiv.classList.remove('hidden'); feather.replace();
            } else showToast("Failed to perform spellcheck: " + (res.error || "Unknown error"), true);
        }

        function replaceWord(btn, word, suggestion) {
            const textarea = document.getElementById('edit-content');
            try {
                const regex = new RegExp(`\\b${word}\\b`);
                if (regex.test(textarea.value)) textarea.value = textarea.value.replace(regex, suggestion);
                else textarea.value = textarea.value.replace(word, suggestion);
            } catch(e) { textarea.value = textarea.value.replace(word, suggestion); }
            btn.style.backgroundColor = '#10b981'; btn.innerText = 'Applied'; btn.disabled = true;
            showToast(`Replaced "${word}" with "${suggestion}"`);
        }

        async function prePushCheck() {
            if (!validateImageRequired()) return;
            
            const saved = await saveArticle(true);
            if (!saved) return; 

            if (currentKeywordHits === 0) {
                showModal("Low SEO Warning", "This article does not contain any of your target local keywords. Are you sure you want to push it to the site?", false, pushToWordPress);
            } else {
                pushToWordPress();
            }
        }

        async function pushToWordPress() {
            const btn = document.getElementById('btn-wp-push');
            const isUpdate = currentWpPostId !== null;
            btn.innerHTML = 'Pushing...'; btn.disabled = true;

            const catSelect = document.getElementById('wp-category');
            const selectedCats = JSON.stringify(Array.from(catSelect.selectedOptions).map(opt => parseInt(opt.value)));

            // Append site suffix to image title for WordPress
            const rawImgTitle = document.getElementById('img-title').value.trim();
            const wpImgTitle = rawImgTitle && !rawImgTitle.endsWith('| Hillingdon Today')
                ? rawImgTitle + ' | Hillingdon Today'
                : rawImgTitle;
            const payload = { 
                id: currentArticleId, 
                headline: document.getElementById('edit-headline').value,
                article_content: document.getElementById('edit-content').value,
                featured_image: document.getElementById('edit-image-base64').value,
                img_title: wpImgTitle,
                img_alt: document.getElementById('img-alt').value,
                img_caption: document.getElementById('img-caption').value,
                wp_post_id: currentWpPostId,
                wp_author: document.getElementById('wp-author').value,
                wp_status: document.getElementById('wp-status').value,
                wp_categories: selectedCats,
                wp_tags: document.getElementById('wp-tags').value,
                wp_date: document.getElementById('wp-date').value
            };

            const res = await api('push_to_wordpress', 'POST', payload);
            
            btn.innerHTML = isUpdate ? '<i data-feather="refresh-cw"></i> Re-Push to Site' : '<i data-feather="upload-cloud"></i> Push to Site';
            btn.disabled = false; feather.replace();

            if (res && res.success) {
                if (res.post_id) { currentWpPostId = res.post_id; updateSocialButton(); }
                
                if (res.wp_pushed_at) {
                    const wpBadge = document.getElementById('editor-wp-badge');
                    let pCount = res.push_count || 1;
                    let pText = pCount > 1 ? 'Re-pushed' : 'Pushed';
                    wpBadge.innerText = `${pText} on ${formatDateTimeStr(res.wp_pushed_at)}`;
                    wpBadge.style.background = pCount > 1 ? '#3a88fe' : '#0cf055';
                    wpBadge.style.color = 'white';
                    wpBadge.classList.remove('hidden');
                    document.getElementById('btn-wp-push').innerHTML = '<i data-feather="refresh-cw"></i> Re-Push to Site';
                    feather.replace();
                }

                if (res.short_url) {
                    currentPermalink = res.permalink;
                    currentShortUrl = res.short_url;
                    document.getElementById('pub-link').href = currentPermalink;
                    document.getElementById('pub-link').innerText = currentPermalink;
                    document.getElementById('url-container').classList.remove('hidden');
                    setShortlinkUI(currentShortUrl);

                    let s1 = document.getElementById('edit-social1').value;
                    let s2 = document.getElementById('edit-social2').value;
                    if (s1 && !s1.includes(res.short_url)) document.getElementById('edit-social1').value = s1.trim() + '\n\n' + res.short_url;
                    if (s2 && !s2.includes(res.short_url)) document.getElementById('edit-social2').value = s2.trim() + '\n\n' + res.short_url;
                } else if (res.permalink) {
                    // Draft post — permalink exists but no shortlink yet (draft URLs can't be shortened)
                    currentPermalink = res.permalink;
                    currentShortUrl = null;
                    document.getElementById('pub-link').href = currentPermalink;
                    document.getElementById('pub-link').innerText = currentPermalink;
                    document.getElementById('url-container').classList.remove('hidden');
                    setShortlinkUI(null);
                }

                showToast('Successfully pushed to site!');
                loadDashboard(); 
            } else {
                showToast('Failed to push: ' + (res.error || 'Check console for errors'), true);
            }
        }

        function copyShortUrl() {
            if (!currentShortUrl) return;
            navigator.clipboard.writeText(currentShortUrl).then(() => {
                showToast('Short URL copied to clipboard!');
            }).catch(() => {
                // Fallback
                const el = document.createElement('textarea');
                el.value = currentShortUrl;
                document.body.appendChild(el); el.select(); document.execCommand('copy');
                document.body.removeChild(el);
                showToast('Short URL copied to clipboard!');
            });
        }

        // Updates the shortlink UI inside the url-container.
        // Pass a shortUrl string to show it, or null to show the "Create" button.
        function setShortlinkUI(shortUrl) {
            const display = document.getElementById('shortlink-display');
            const create  = document.getElementById('shortlink-create');
            const msgEl   = document.getElementById('shortlink-create-msg');
            if (!display || !create) return;

            if (shortUrl) {
                document.getElementById('short-link').innerText = shortUrl;
                display.classList.remove('hidden');
                display.style.display = 'flex';
                create.classList.add('hidden');
                create.style.display = 'none';
            } else {
                display.classList.add('hidden');
                display.style.display = 'none';
                create.classList.remove('hidden');
                create.style.display = 'flex';
                if (msgEl) msgEl.innerText = '';
            }
            feather.replace();
        }

        async function createShortlink() {
            const msgEl = document.getElementById('shortlink-create-msg');
            const btnEl = document.getElementById('btn-create-shortlink');
            btnEl.innerHTML = '<i data-feather="loader"></i> Creating...'; btnEl.disabled = true; feather.replace();
            if (msgEl) msgEl.innerText = '';

            const res = await api('create_shortlink', 'POST', { article_id: currentArticleId });

            // Re-fetch after feather.replace() to guard against stale reference
            const btnRefreshed = document.getElementById('btn-create-shortlink');
            if (btnRefreshed) { updateShortUrlButtonLabel((appSettings && appSettings.shorturl_provider) || 'tinyurl'); btnRefreshed.disabled = false; }
            feather.replace();

            if (res && res.success) {
                currentShortUrl = res.short_url;
                setShortlinkUI(currentShortUrl);
                showToast('Short URL created!');
            } else {
                // Explicitly keep the create row visible so the user can retry
                const createDiv = document.getElementById('shortlink-create');
                if (createDiv) { createDiv.classList.remove('hidden'); createDiv.style.display = 'flex'; }
                const btnRetry = document.getElementById('btn-create-shortlink');
                if (btnRetry) { btnRetry.disabled = false; }
                if (msgEl) { msgEl.style.color = 'var(--danger-color)'; msgEl.innerText = res?.error || 'Failed to create short URL.'; }
                showToast(res?.error || 'Failed to create short URL.', true);
            }
        }

        // --- Trash & Restore ---
        function trashArticle(id) {
            showModal("Delete Article", "Are you sure you want to move this to the recycle bin?", false, async () => {
                await api('trash_article', 'POST', {id});
                showToast("Moved to recycle bin"); loadDashboard(); loadArticles();
            });
        }
        function trashCurrentArticle() {
            if(currentArticleId > 0) {
                showModal("Delete Article", "Are you sure you want to move this to the recycle bin?", false, async () => {
                    await api('trash_article', 'POST', {id: currentArticleId});
                    showToast("Moved to recycle bin"); switchView('dashboard');
                });
            } else if (currentArticleId === 0) switchView('dashboard');
        }
        async function restoreArticle(id) { await api('restore_article', 'POST', {id}); showToast("Article restored"); loadRecycleBin(); }
        function hardDelete(id) {
            showModal("Permanent Delete", "Are you sure? This cannot be undone.", false, async () => {
                await api('delete_article_permanently', 'POST', {id}); showToast("Permanently deleted"); loadRecycleBin();
            });
        }

        // --- Utilities ---
        function copyToClipboard(type) {
            let text = "";
            if(type === 'article') text = document.getElementById('edit-headline').value + "\n\n" + document.getElementById('edit-content').value;
            else text = "Post 1:\n" + document.getElementById('edit-social1').value + "\n\nPost 2:\n" + document.getElementById('edit-social2').value;
            navigator.clipboard.writeText(text); showToast('Copied to clipboard!');
        }
        function copyHashtags() {
            const text = document.getElementById('edit-hashtags').value;
            if (!text) return showToast("No hashtags to copy.", true);
            navigator.clipboard.writeText(text); showToast('Hashtags copied to clipboard!');
        }

        // --- Customisation ---
        let pendingLogoBase64 = null;
        let clearLogoFlag = false;

        function previewLogo(input) {
            const errEl = document.getElementById('sys-logo-error');
            errEl.innerText = '';
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            if (file.type !== 'image/png') { errEl.innerText = 'Please select a PNG file.'; input.value = ''; return; }
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    if (img.width < 512 || img.height < 512) {
                        errEl.innerText = `Logo must be at least 512×512 px (yours is ${img.width}×${img.height}).`;
                        input.value = ''; pendingLogoBase64 = null;
                        return;
                    }
                    // Resize to 512×512 for storage/display
                    const canvas = document.createElement('canvas');
                    canvas.width = 512; canvas.height = 512;
                    canvas.getContext('2d').drawImage(img, 0, 0, 512, 512);
                    pendingLogoBase64 = canvas.toDataURL('image/png');
                    clearLogoFlag = false;
                    document.getElementById('sys-logo-preview').src = pendingLogoBase64;
                    document.getElementById('sys-logo-preview-wrap').style.display = 'flex';
                    document.getElementById('sys-logo-preview-wrap').style.alignItems = 'center';
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        function clearLogo() {
            pendingLogoBase64 = null;
            clearLogoFlag = true;
            document.getElementById('sys-logo-file').value = '';
            document.getElementById('sys-logo-preview-wrap').style.display = 'none';
            document.getElementById('sys-logo-preview').src = '';
        }

        async function saveCustomisation() {
            const appName = document.getElementById('sys-app-name').value.trim();
            const data = { app_name: appName };
            if (clearLogoFlag) data.app_logo = '';
            else if (pendingLogoBase64) data.app_logo = pendingLogoBase64;
            const res = await api('save_customisation', 'POST', data);
            if (res && res.success) {
                showToast('Customisation saved!');
                pendingLogoBase64 = null; clearLogoFlag = false;
                applyBranding(appName || 'Newsroom Creator', data.app_logo !== undefined ? data.app_logo : appSettings.app_logo);
                appSettings.app_name = appName || 'Newsroom Creator';
                if (data.app_logo !== undefined) appSettings.app_logo = data.app_logo;
            } else {
                showToast('Failed to save customisation.', true);
            }
        }

        function applyBranding(name, logoBase64) {
            const displayName = name || 'Newsroom Creator';
            // Update page title
            document.title = displayName;
            // Nav
            document.getElementById('nav-app-name').innerText = displayName;
            // Login
            document.getElementById('login-app-name').innerText = displayName;
            // Footer branding is static product identity

            const navLogoImg = document.getElementById('nav-logo-img');
            const navLogoIcon = document.getElementById('nav-logo-icon');
            const loginLogoImg = document.getElementById('login-logo-img');
            const loginLogoIcon = document.getElementById('login-logo-icon');

            if (logoBase64) {
                navLogoImg.src = logoBase64;
                navLogoImg.style.display = 'inline-block';
                navLogoIcon.style.display = 'none';
                loginLogoImg.src = logoBase64;
                loginLogoImg.style.display = 'block';
                loginLogoImg.style.margin = '0 auto 10px auto';
                loginLogoIcon.style.display = 'none';
            } else {
                navLogoImg.style.display = 'none';
                navLogoIcon.style.display = '';
                loginLogoImg.style.display = 'none';
                loginLogoIcon.style.display = '';
            }
            feather.replace();
        }

        // --- System Settings ---
        async function saveSettingsForm() {
            const shortProvider = document.getElementById('shorturl-provider') ? document.getElementById('shorturl-provider').value : 'tinyurl';
            const data = {
                local_keywords: document.getElementById('sys-keywords').value,
                ai_base_url: document.getElementById('ai-base-url').value.trim(),
                ai_api_key:  document.getElementById('ai-api-key').value.trim(),
                ai_model:    document.getElementById('ai-model').value.trim(),
                language_variant: document.getElementById('language-variant').value,
                wp_site_url: document.getElementById('wp-site-url').value.trim(),
                wp_api_key:  document.getElementById('wp-api-key').value.trim(),
                shorturl_provider: shortProvider,
                shorturl_api_key: document.getElementById('shorturl-api-key') ? document.getElementById('shorturl-api-key').value.trim() : '',
                shortio_domain: document.getElementById('shortio-domain') ? document.getElementById('shortio-domain').value.trim() : ''
            };
            const res = await api('save_settings', 'POST', data);
            if (res && res.success) { showToast('Settings saved successfully!'); loadSettings(); } 
            else showToast('Failed to save settings.', true);
        }

        async function testAiSettings() {
            const btn = document.getElementById('btn-test-ai'); const result = document.getElementById('ai-test-result');
            btn.innerHTML = 'Testing...'; btn.disabled = true; result.style.color = 'var(--text-muted)'; result.innerText = 'Sending test request...';
            await saveSettingsForm(); 
            const res = await api('check_spelling', 'POST', { article_content: 'Test.' });
            btn.innerHTML = '<i data-feather="zap"></i> Test Connection'; btn.disabled = false; feather.replace();
            if (res && res.success) { result.style.color = '#10b981'; result.innerText = '✓ Connection successful!'; } 
            else { result.style.color = 'var(--danger-color)'; result.innerText = '✗ ' + (res?.error || 'Connection failed.'); }
        }

        async function testWpSettings() {
            const btn = document.getElementById('btn-test-wp'); const result = document.getElementById('wp-test-result');
            btn.innerHTML = 'Testing...'; btn.disabled = true; result.style.color = 'var(--text-muted)'; result.innerText = 'Connecting to WordPress...';
            await saveSettingsForm(); 
            const res = await api('test_wp_connection');
            btn.innerHTML = '<i data-feather="zap"></i> Test Connection'; btn.disabled = false; feather.replace();
            if (res && res.success) {
                result.style.color = '#10b981';
                result.innerText = '✓ Connection successful!' + (res.warning ? ' ⚠ ' + res.warning : '');
                // If the server auto-corrected the URL, refresh the displayed value
                if (res.warning && res.warning.includes('auto-corrected')) loadSettings();
            } else { result.style.color = 'var(--danger-color)'; result.innerText = '✗ ' + (res?.error || 'Connection failed.'); }
        }

        function toggleShortIoDomain() {
            const provider = document.getElementById('shorturl-provider');
            const wrap = document.getElementById('shortio-domain-wrap');
            if (!provider || !wrap) return;
            if (provider.value === 'shortio') wrap.classList.remove('hidden');
            else wrap.classList.add('hidden');
            updateShortUrlButtonLabel(provider.value);
        }

        function toggleShortUrlKeyVisibility() {
            const input = document.getElementById('shorturl-api-key'); const btn = document.getElementById('btn-toggle-shorturl-key');
            if (input.type === 'password') { input.type = 'text'; btn.innerHTML = '<i data-feather="eye-off"></i>'; }
            else { input.type = 'password'; btn.innerHTML = '<i data-feather="eye"></i>'; } feather.replace();
        }

        function updateShortUrlButtonLabel(provider) {
            const btn = document.getElementById('btn-create-shortlink');
            if (!btn) return;
            const label = provider === 'shortio' ? 'Short.io' : 'TinyURL';
            btn.innerHTML = `<i data-feather="link"></i> Create Short URL (${label})`;
            feather.replace();
        }

        function toggleApiKeyVisibility() {
            const input = document.getElementById('ai-api-key'); const btn = document.getElementById('btn-toggle-key');
            if (input.type === 'password') { input.type = 'text'; btn.innerHTML = '<i data-feather="eye-off"></i>'; } 
            else { input.type = 'password'; btn.innerHTML = '<i data-feather="eye"></i>'; } feather.replace();
        }

        function toggleWpKeyVisibility() {
            const input = document.getElementById('wp-api-key'); const btn = document.getElementById('btn-toggle-wp-key');
            if (input.type === 'password') { input.type = 'text'; btn.innerHTML = '<i data-feather="eye-off"></i>'; } 
            else { input.type = 'password'; btn.innerHTML = '<i data-feather="eye"></i>'; } feather.replace();
        }

        // --- User Management ---
        async function loadUsers() {
            const res = await api('get_users');
            if (!res || !res.success) return;
            const cont = document.getElementById('user-list'); cont.innerHTML = '';
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
            const u = document.getElementById('new-user-name').value; const p = document.getElementById('new-user-pass').value; const r = document.getElementById('new-user-role').value;
            if(!u || !p) return showToast("Username and password required.", true);
            const res = await api('add_user', 'POST', {username: u, password: p, role: r});
            if(res.success) { document.getElementById('new-user-name').value = ''; document.getElementById('new-user-pass').value = ''; showToast("User added"); loadUsers(); } 
            else showToast("Error adding user: " + (res.error || "Unknown"), true);
        }
        function resetUserPass(id) { showModal("Reset Password", "Enter new password:", true, async (newPass) => { if(newPass) { const res = await api('reset_password', 'POST', {id, password: newPass}); if(res.success) showToast("Password reset successfully."); else showToast("Error resetting password.", true); } }); }
        function deleteUser(id) { showModal("Delete User", "Are you sure?", false, async () => { const res = await api('delete_user', 'POST', {id}); if(res.success) { showToast("User deleted"); loadUsers(); } else showToast("Error deleting user.", true); }); }
        async function changeMyPassword() {
            const p = document.getElementById('my-new-pass').value; if(!p) return showToast("Enter a new password.", true);
            const res = await api('check_auth'); const resetRes = await api('reset_password', 'POST', {id: res.user.id, password: p});
            if(resetRes.success) { showToast("Password updated successfully."); document.getElementById('my-new-pass').value = ''; } 
            else showToast("Failed to update password.", true);
        }
        function restoreJson(input) {
            const file = input.files[0]; if (!file) return;
            const reader = new FileReader();
            reader.onload = async function(e) {
                try { const data = JSON.parse(e.target.result); const res = await api('restore_json', 'POST', data); if(res.success) { showToast("Data restored"); loadDashboard(); } else showToast("Error restoring data.", true); } catch (err) { showToast("Invalid JSON file.", true); }
            };
            reader.readAsText(file);
        }
    </script>

    <script>
        // ═══ PWA: Add to Home Screen ═══
        let deferredInstallPrompt = null;

        const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
        const isInStandaloneMode = window.navigator.standalone === true || window.matchMedia('(display-mode: standalone)').matches;
        const pwaDismissedUntil = localStorage.getItem('pwaDismissedUntil');
        const bannerSnoozed = pwaDismissedUntil && Date.now() < parseInt(pwaDismissedUntil, 10);

        // Android / Chrome: capture the native prompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredInstallPrompt = e;
            if (!isInStandaloneMode && !bannerSnoozed) {
                const banner = document.getElementById('pwa-install-banner');
                if (banner) { banner.style.display = 'flex'; feather.replace(); }
            }
        });

        // iOS Safari: show manual hint if not already installed
        if (isIos && !isInStandaloneMode && !bannerSnoozed) {
            setTimeout(() => {
                const iosBanner = document.getElementById('pwa-ios-banner');
                if (iosBanner) { iosBanner.style.display = 'flex'; feather.replace(); }
            }, 3000);
        }

        document.getElementById('pwa-install-btn')?.addEventListener('click', async () => {
            if (!deferredInstallPrompt) return;
            deferredInstallPrompt.prompt();
            const { outcome } = await deferredInstallPrompt.userChoice;
            deferredInstallPrompt = null;
            document.getElementById('pwa-install-banner').style.display = 'none';
        });

        document.getElementById('pwa-dismiss-btn')?.addEventListener('click', () => {
            document.getElementById('pwa-install-banner').style.display = 'none';
            // Snooze for 7 days
            localStorage.setItem('pwaDismissedUntil', Date.now() + 7 * 24 * 60 * 60 * 1000);
        });

        document.getElementById('pwa-ios-dismiss-btn')?.addEventListener('click', () => {
            document.getElementById('pwa-ios-banner').style.display = 'none';
            localStorage.setItem('pwaDismissedUntil', Date.now() + 7 * 24 * 60 * 60 * 1000);
        });

        // Register service worker (required for PWA installability on Android)
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js').catch(() => {});
        }
    </script>
</body>
</html>
