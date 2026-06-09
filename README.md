# Newsroom Creator

**Newsroom Creator** is a lightweight, self-hosted application designed for journalists and content creators. It is a sleek, self-hosted web application that streamlines the editorial workflow.

It provides an end-to-end web workflow that converts raw source material into balanced, publication-ready news articles using customisable AI parameters, and orchestrates a collaborative editorial review pipeline. 

Built for shared hosting environments with no complex dependencies, it features an SQLite-backed architecture, modern mobile-responsive UI, and flexible AI provider support. 

## Key Features

* **Modern UI:** Fully responsive, mobile-first design with built-in Light/Dark mode and adjustable text sizing.
* **Flexible AI Integration:** Compatible with any OpenAI-compliant API provider (OpenRouter, OpenAI, etc.).
* **Multi-Mode AI Journalism Core:** Automates article generation based on strict inverted-pyramid editorial standards. Interface contains two distinct engines to accommodate varied journalistic strategies - *Generate Article Core: Structures raw, unstructured sources into clean informational blocks utilizing strict inverted-pyramid structural logic. The system restricts data output to the verifiable parameters present inside the source text to completely avoid hallucination risks. | Spin Article Core: Completely rewrites preexisting articles, releases, or wire copy to pass plagiarism checks and safety audits while accurately preserving the original informational payload. The engine operates a fallback text parser to secure content delivery if the upstream JSON response requires structural repair*.
* **Advanced Multi-Tier Workflow & Role Roles:** Incorporates formal role-based access controls (RBAC) to support standard newsroom hierarchical workflows - *Admin Role: Grants total oversight, including structural system settings, white-label branding configurations, database migration pathways, and full user account management. | Editor Role: Grants global oversight of all generated copy across the platform. Editors manage the unified production queues, evaluate writer submissions, adjust configuration variables, assign statuses, and push content live to production websites. | User Role: Restricts the view to a writer’s own workspace. Writers can generate and edit their assigned drafts, run content metrics, and formally submit completed articles up to the editing desk*.
* **Integrated Editorial Pipeline & Monitoring:** Granular Lifecycle Tracking as articles advance along clearly flagged states: Draft, In Progress, Review Pending, Further Action Required, and Approved.
* **Regional Keyword Scanner:** Admins can save a comma-separated list of priority regional or topical terms. The editor performs a live keyword scanner against the active document workspace to verify compliance with local traffic acquisition goals.
* **Content Telemetry & Metrics:** Real-time text-parsing widgets calculate word totals, character volumes, and estimated reading durations when spoken out loud.
* **Integrated Spelling & Grammar Check:** Includes built-in mechanical verification processes supporting localization choices for both British English and American English.
* **Social Engine & URL Shortening:** Post Automation Suite which is locked until an article is safely stored on the production site. Once published, it generates short-form promotional snippets alongside conceptual stock photo prompts for marketing channels. Generates tailored social media posts and SEO-optimized hashtags.
* **Native TinyURL and Short.io API Shortener:** Implements a localised optimization engine using the official structural criteria of both the TinyURL and Short.io developer framework. Allows admin to set which service they wish to use and enter their API key on the System page.
* **Security, Portability & Customization:** Complete Enterprise White-Labeling which allows administrators to customize the platform name and replace application assets with high-resolution brand logos (PNG, minimum 512x512 pixels) across the ecosystem.
* **Automated Recycle Bin Management:** Features a built-in data recovery layer that holds trashed items for 7 days before issuing terminal DELETE calls to clean storage space.
* **Data Portability Engines:** Admin accounts feature complete data backup pipelines, exporting records into structured CSV or JSON formats, alongside full data restoration utilities.
* **Self-Hosted & Lightweight:** Uses SQLite (no database server installation required) and runs on standard PHP shared hosting.

## Companion Wordpress plugin

Converts remote WordPress setups into instant publishing endpoints via a secure REST API route
* **Bi-directional Meta Mapping:** Pulls live structural variables from WordPress directly into the Newsroom Creator UI, allowing editors to assign real Authors, map multiple Categories, append comma-separated Tags, and schedule programmatic Publish Dates (future, draft, or publish) directly from the workspace.
* **Media Processing & Asset Management:** Processes uploaded image files locally via Base64 serialization, transporting them across the API tunnel to create managed assets inside the WordPress Media Library. Includes dedicated text controls for Image Titles, Captions, and Alt Text.
* **Slug Collisions and Protection:** The plugin intercepts titles and implements localized validation loops to confirm URL slug uniqueness, appending cryptographic short hashes to prevent content overwrites on matched filenames.
* **Remote Post Control Panel:** Editors can monitor existing posts from the web app interface, track re-push execution logs, open permalinks, or remotely trigger WordPress Trash sequences across active endpoints.

## Installation

This application is designed for rapid deployment on standard PHP-based shared hosting.

1. **Clone or Download** the files to your web server directory.
2. **Permissions**: Ensure the folder has write permissions so the `newsroom.sqlite` file can be created automatically.
3. **Access**: Navigate to the URL in your web browser.
4. **Login**: Use the default credentials:
* **Username**: `admin`
* **Password**: `password123`


5. **Configure AI**: Once logged in, navigate to the **System** tab to enter your AI Provider's Base URL, API Key, and Model name.
6. **Install Wordpress Plugin**: [newsroom-creator-plugin.zip] Install the Wordpress plugin, then enter the Site URL and API key found on the Settings page for articles to be pushed easily to Wordpress. 

## Technology Stack
* **Backend**: PHP 7.4+
* **Database**: SQLite
* **Frontend**: Vanilla JavaScript, CSS3 (Flexbox/Grid), and Feather Icons.
* **AI Integration**: Fully compatible with any upstream REST provider that supports the Open-AI Chat Completion specification (e.g., OpenAI, OpenRouter etc.).

## Security & Best Practices

* **Default Credentials**: Immediately change the default admin password via the System tab.
* **Backup**: Regularly use the "Backup JSON" feature to maintain local copies of your articles.
* **API Security**: Your API keys are stored in the local SQLite database and are not accessible via the frontend code.

**Copyright (c) 2026 VBI.**
*See the LICENSE full terms.
Modification is strictly prohibited without a Commercial Modification License.*
