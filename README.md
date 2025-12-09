# GitHub User Profile API

A raw PHP RESTful API endpoint that fetches GitHub user profile data via cURL.

## Requirements
- PHP 8.0+
- cURL extension enabled
- OpenSSL enabled

## Installation
1. Clone the repo.
2. Point your web server document root to the `public/` folder.

## Usage
**Endpoint:** `GET /`

**Parameter:** `username` (string) - The GitHub username.

**Example:**
http://localhost:8000/?username=octocat

## Response
Returns JSON data from GitHub or error messages.
