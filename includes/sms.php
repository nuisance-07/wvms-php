<?php
/**
 * WVMS — SMS Helper (Mock Africa's Talking API)
 * In production, this would use the Africa's Talking PHP SDK.
 */

function sendSMS($phone, $message) {
    // 1. In a real scenario, initialize Africa's Talking SDK:
    // require 'vendor/autoload.php';
    // use AfricasTalking\SDK\AfricasTalking;
    // $username = 'YOUR_USERNAME';
    // $apiKey   = 'YOUR_API_KEY';
    // $AT       = new AfricasTalking($username, $apiKey);
    // $sms      = $AT->sms();
    // $sms->send(['to' => $phone, 'message' => $message]);

    // 2. Mock Scenario: We log the SMS to a text file for demonstration.
    $logFile = __DIR__ . '/../sms_logs.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] SMS TO: {$phone} | MESSAGE: {$message}\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    return true; // Simulate success
}
