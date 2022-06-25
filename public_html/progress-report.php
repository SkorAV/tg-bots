<?php
require_once __DIR__ . '/autoloader.php';

$logger = new FileLogger(__DIR__ . '/../tmp/progress-report.log');

try {
    $db = DBAccess::connect('localhost', 'u374561970_andrii', '#Jx+u52wR*9', 'u374561970_servicepages');
    $bot_data = $db->select('
        SELECT
            c.value
        FROM config c
        WHERE c.name = ?;',
        ['s' => 'progress_report_bot']
    )->fetch_row();
    $bot_data = json_decode($bot_data[0]);
    $bot = new TgBot($bot_data->bot_secret, $logger);

    $request = $bot->getUpdate();
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
                $known_users_updated = true;

                break;
            default:
                $bot->sendText($update->message->text, $update->message->from, null);

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
