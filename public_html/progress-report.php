<?php
require_once __DIR__ . '/autoloader.php';

$logger = new FileLogger(__DIR__ . '/../tmp/progress-report.log');
$bot = new TgBot('https://api.telegram.org/bot5422534673:AAHG5_XH738UwJPYpKhA2on80Bl4dMTqsPw/', $logger);

$request = $bot->getUpdate();

try {
    $update = json_decode($request);
    $known_users = json_decode(file_get_contents(__DIR__ . '/../tmp/progress-report-users.json'), true);
    $known_users_updated = false;

    if (!empty($update->message)) {
        switch ($update->message->text) {
            case '/start':
                if (!empty($known_users[$update->message->from->id])) {
                    $bot->sendText('You are already registered here!', $update->message->from, null);

                    break;
                }

                $known_users[$update->message->from->id] = $update->message->from;
                $known_users_updated = true;
                $bot->sendText("Welcome!\nPlease, choose an action from the menu.", $update->message->from, [
                    [['text' => 'Hello!'], ['text' => 'Bye!'], ['text'=> 'Nice to meet you.']],
                    [['text' => 'Bonjour!'], ['text' => 'Au revoir!'], ['text' => 'Enchante.']],
                ]);

                break;
            case '/stop':
                unset($known_users[$update->message->from->id]);

                break;
        }
    }

    if ($known_users_updated) {
        file_put_contents(__DIR__ . '/../tmp/progress-report-users.json', json_encode($known_users));
    }
} catch (Throwable $e) {
    $logger->log($e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
}

exit;
