<?php
/**
 * Test OAuth2 Token Generation
 * Debug script to verify JWT signing works correctly
 */

require_once '.hta_config/config.php';

echo "Testing OAuth2 Token Generation...\n\n";

// Test 1: Check OpenSSL
echo "1. Checking OpenSSL...\n";
if (!extension_loaded('openssl')) {
    echo "   ❌ OpenSSL extension not loaded!\n";
    exit(1);
}
echo "   ✅ OpenSSL extension loaded\n";

// Test 2: Check constants
echo "\n2. Checking Firebase constants...\n";
echo "   Project ID: " . (defined('FIREBASE_PROJECT_ID') ? FIREBASE_PROJECT_ID : 'NOT DEFINED') . "\n";
echo "   Client Email: " . (defined('FIREBASE_CLIENT_EMAIL') ? FIREBASE_CLIENT_EMAIL : 'NOT DEFINED') . "\n";
echo "   Private Key: " . (defined('FIREBASE_PRIVATE_KEY') ? substr(FIREBASE_PRIVATE_KEY, 0, 50) . '...' : 'NOT DEFINED') . "\n";

if (!defined('FIREBASE_PRIVATE_KEY') || empty(FIREBASE_PRIVATE_KEY)) {
    echo "   ❌ PRIVATE KEY is not defined or empty!\n";
    exit(1);
}

// Test 3: Format private key
echo "\n3. Formatting private key...\n";
$privateKey = FIREBASE_PRIVATE_KEY;

// Check if key already has proper format
if (strpos($privateKey, '-----BEGIN PRIVATE KEY-----') === false) {
    echo "   Key needs formatting...\n";
    // Remove any existing headers/footers and whitespace
    $keyContent = preg_replace('/-----BEGIN PRIVATE KEY-----/', '', $privateKey);
    $keyContent = preg_replace('/-----END PRIVATE KEY-----/', '', $keyContent);
    $keyContent = preg_replace('/\s+/', '', $keyContent);
    
    // Reformat with proper line breaks (64 chars per line)
    $formattedKey = "-----BEGIN PRIVATE KEY-----\n";
    $formattedKey .= chunk_split($keyContent, 64, "\n");
    $formattedKey .= "-----END PRIVATE KEY-----\n";
    $privateKey = $formattedKey;
    
    echo "   Formatted key:\n";
    echo "   " . substr($privateKey, 0, 100) . "...\n";
} else {
    echo "   ✅ Key already properly formatted\n";
}

// Test 4: Create JWT
echo "\n4. Creating JWT...\n";
try {
    $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
    $now = time();
    $claimSet = json_encode([
        'iss' => FIREBASE_CLIENT_EMAIL,
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $now + 3600
    ]);
    
    $headerEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $claimSetEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($claimSet));
    
    $signatureInput = $headerEncoded . '.' . $claimSetEncoded;
    
    echo "   Header: $headerEncoded\n";
    echo "   Claims: $claimSetEncoded\n";
    
    // Test signing
    $signature = '';
    $signResult = openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    
    if (!$signResult) {
        $error = openssl_error_string();
        echo "   ❌ Signing failed: $error\n";
        exit(1);
    }
    
    echo "   ✅ Signing successful\n";
    
    $signatureEncoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    $jwt = $headerEncoded . '.' . $claimSetEncoded . '.' . $signatureEncoded;
    
    echo "   JWT length: " . strlen($jwt) . " chars\n";
    
} catch (Exception $e) {
    echo "   ❌ JWT creation failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 5: Exchange for access token
echo "\n5. Exchanging JWT for access token...\n";

$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
    'assertion' => $jwt
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo "   ❌ cURL error: $curlError\n";
    exit(1);
}

echo "   HTTP Code: $httpCode\n";
echo "   Response: $response\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "\n   ✅ SUCCESS! Access token obtained\n";
    echo "   Token type: " . ($data['token_type'] ?? 'unknown') . "\n";
    echo "   Expires in: " . ($data['expires_in'] ?? 'unknown') . " seconds\n";
    echo "   Access token: " . substr($data['access_token'], 0, 50) . "...\n";
} else {
    $error = json_decode($response, true);
    echo "\n   ❌ FAILED!\n";
    echo "   Error: " . ($error['error'] ?? 'Unknown') . "\n";
    echo "   Description: " . ($error['error_description'] ?? 'No description') . "\n";
}

echo "\nDone.\n";
