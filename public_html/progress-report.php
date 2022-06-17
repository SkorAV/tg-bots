<?php
const BASE_URL = 'https://api.telegram.org/bot5422534673:AAHG5_XH738UwJPYpKhA2on80Bl4dMTqsPw/';

$request = file_get_contents('php://input');
file_put_contents(__DIR__ . '/../tmp/progress-report.log', $request . PHP_EOL, FILE_APPEND);

try {
    $update = json_decode($request, true);
    $known_users = json_decode(file_get_contents(__DIR__ . '/../tmp/progress-report-users.json'), true);
    $known_users_updated = false;

    if (empty($update['message']['text'])) {
        throw new RuntimeException('The update is invalid!');
    }

    switch ($update['message']['text']) {
        case '/start':
            if (!empty($known_users[$update['message']['from']['id']])) {
                send_message(BASE_URL, $update['message']['from'], 'You are already registered here!');

                break;
            }

            $known_users[$update['message']['from']['id']] = $update['message']['from'];
            $known_users_updated = true;
            send_message(BASE_URL, $update['message']['from'], 'Welcome!');

            break;
        case '/stop':
            unset($known_users[$update['message']['from']['id']]);

            break;
    }

    if ($known_users_updated) {
        file_put_contents(__DIR__ . '/../tmp/progress-report-users.json', json_encode($known_users));
    }
} catch (Throwable $e) {
    file_put_contents(__DIR__ . '/../tmp/progress-report.log', '[' .date('Y-m-d H:i:s') . '] ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL, FILE_APPEND);
}

function send_message($url, $user, $message) {
    $curl = curl_init($url . 'sendMessage');
    curl_setopt_array($curl, [
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => [
            'chat_id' => $user['id'],
            'text' => $message,
        ],
    ]);
    curl_exec($curl);
    curl_close($curl);
}

exit;
