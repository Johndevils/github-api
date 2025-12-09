<?php

declare(strict_types=1);

/**
 * GitHub User Profile API Proxy
 * 
 * This script acts as a proxy to fetch GitHub user data.
 * It handles validation, cURL requests, and error mapping.
 */

// 1. Set Content-Type to JSON immediately
header('Content-Type: application/json; charset=utf-8');

// Disable default PHP HTML error reporting to prevent breaking JSON output
ini_set('display_errors', '0');

/**
 * Helper function to send a JSON error response and exit
 */
function sendErrorResponse(int $httpCode, string $message): void
{
    http_response_code($httpCode);
    echo json_encode([
        'error' => true,
        'status' => $httpCode,
        'message' => $message
    ]);
    exit;
}

// 2. Validate Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse(405, 'Method Not Allowed. Only GET requests are accepted.');
}

// 3. Input Handling and Validation
if (!isset($_GET['username']) || empty(trim($_GET['username']))) {
    sendErrorResponse(400, 'Bad Request: "username" parameter is missing.');
}

$username = trim($_GET['username']);

// Sanitize: GitHub usernames may only contain alphanumeric characters or hyphens.
if (!preg_match('/^[a-zA-Z0-9-]+$/', $username)) {
    sendErrorResponse(400, 'Bad Request: Invalid username format.');
}

// 4. Prepare cURL Request
$apiUrl = "https://api.github.com/users/" . $username;

$ch = curl_init();

// --- NEW: Handle Authentication securely ---
// We read the token from the server Environment Variables.
// Do NOT hardcode the token here (e.g. $token = "ghp_...") to avoid security leaks.
$githubToken = getenv('GITHUB_TOKEN');

$requestHeaders = [];

// If a token exists in the environment, add the Authorization header
if ($githubToken) {
    $requestHeaders[] = 'Authorization: token ' . $githubToken;
}
// -------------------------------------------

// cURL Options
$options = [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true, 
    CURLOPT_FOLLOWLOCATION => true, 
    CURLOPT_TIMEOUT => 10,          
    
    // GitHub requires a User-Agent
    CURLOPT_USERAGENT => 'PHP-GitHub-Profile-Proxy/1.0',
    
    // Add the headers (includes Token if available)
    CURLOPT_HTTPHEADER => $requestHeaders
];

curl_setopt_array($ch, $options);

// 5. Execute Request
$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

// 6. Error Handling

// A. Internal cURL failure
if ($response === false) {
    sendErrorResponse(500, 'Internal Server Error: Failed to connect to GitHub API.');
}

// B. Handle specific HTTP status codes from GitHub
switch ($httpCode) {
    case 200:
        // Success
        http_response_code(200);
        echo $response;
        break;

    case 404:
        // User not found
        sendErrorResponse(404, 'User not found.');
        break;

    case 403:
        // Rate limit exceeded or forbidden
        $data = json_decode((string)$response, true);
        $msg = $data['message'] ?? 'Access Forbidden or Rate Limit Exceeded.';
        sendErrorResponse(403, $msg);
        break;

    default:
        // Handle other unexpected codes
        sendErrorResponse($httpCode, 'Upstream error from GitHub.');
        break;
}
