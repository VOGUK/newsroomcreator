**Newsroom Article Creator** is a lightweight, self-hosted application designed for journalists and content creators. It transforms raw source material (press releases, emails, social media posts) into high-quality, SEO-optimized news articles and social media content using AI. Built for shared hosting environments with no complex dependencies, it features an SQLite-backed architecture, modern mobile-responsive UI, and flexible AI provider support. It is a sleek, self-hosted web application that streamlines the editorial workflow.

## Key Features

* **AI-Powered Journalism**: Automates article generation based on strict inverted-pyramid editorial standards.
* **Social Media Automation**: Instantly generates tailored social media posts and SEO-optimized hashtags.
* **Modern UI**: Fully responsive, mobile-first design with built-in Light/Dark mode and adjustable text sizing.
* **Self-Hosted & Lightweight**: Uses **SQLite** (no database server installation required) and runs on standard PHP shared hosting.
* **Flexible AI Integration**: Compatible with any OpenAI-compliant API provider (Groq, OpenRouter, OpenAI, etc.).
* **Admin & Security**: Secure user management, role-based access control, and easy data portability via JSON backup/restore or CSV export.

## Installation

This application is designed for rapid deployment on standard PHP-based shared hosting.

1. **Clone or Download** the files to your web server directory.
2. **Permissions**: Ensure the folder has write permissions so the `newsroom.sqlite` file can be created automatically.
3. **Access**: Navigate to the URL in your web browser.
4. **Login**: Use the default credentials:
* **Username**: `admin`
* **Password**: `password123`


5. **Configure AI**: Once logged in, navigate to the **System** tab to enter your AI Provider's Base URL, API Key, and Model name.

## Technology Stack

* **Backend**: PHP 7.4+
* **Database**: SQLite
* **Frontend**: Vanilla JavaScript, CSS3 (Flexbox/Grid), and Feather Icons.
* **AI Integration**: OpenAI-compatible REST API.

## Security & Best Practices

* **Default Credentials**: Immediately change the default admin password via the System tab.
* **Backup**: Regularly use the "Backup JSON" feature to maintain local copies of your articles.
* **API Security**: Your API keys are stored in the local SQLite database and are not accessible via the frontend code.
OpenRouter API keys. It is best to upload the template version with the keys removed, or add `api.php` to your `.gitignore` file.
* **Logo/Icon:** Since you wanted a professional icon, I recommend searching for a free SVG icon (like "newspaper" or "edit" from [Lucide Icons](https://lucide.dev/)) and placing it as `logo.svg` in your project folder to add that final professional touch.
