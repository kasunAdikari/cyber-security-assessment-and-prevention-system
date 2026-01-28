<?php


// Your OpenAI API key
$apiKey = 'YOUR_OPENAI_API_KEY';

// API endpoint for chat completions
$url = 'https://api.openai.com/v1/chat/completions';

// User message (you can make this dynamic, e.g., from POST data)
$userMessage = isset($_POST['message']) ? $_POST['message'] : 'Hello, what is GPT-3.5-turbo?';

// Prepare the messages array for the chat API
$messages = [
    [
        'role' => 'user',
        'content' => $userMessage
    ]
];

// Data payload for the API
$data = [
    'model' => 'gpt-3.5-turbo',
    'messages' => $messages,
    'max_tokens' => 150,  // Limit response length
    'temperature' => 0.7  // Creativity level (0-1)
];

// Headers for the request
$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
];

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_error($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Decode the JSON response
$result = json_decode($response, true);

// Check for API errors
if (isset($result['error'])) {
    echo 'API Error: ' . $result['error']['message'];
    exit;
}

// Display the response
if (isset($result['choices'][0]['message']['content'])) {
    echo $result['choices'][0]['message']['content'] . "\n";
} else {
    echo 'No response received.';
}

// Optional: Simple HTML form for web usage
if (php_sapi_name() !== 'cli') {
    echo '<form method="post"><input type="text" name="message" placeholder="Enter your message"><button type="submit">Send</button></form>';
}
?>