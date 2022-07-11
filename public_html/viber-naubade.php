<?php
require_once __DIR__ . '/autoloader.php';

$logger = new FileLogger(__DIR__ . '/../tmp/viber-naubade.log');

try {
    $db = DBAccess::connect('localhost', 'u374561970_andrii', '#Jx+u52wR*9', 'u374561970_servicepages');
    $botData = $db->select('
        SELECT
            c.value
        FROM config c
        WHERE c.name = ?;',
        ['s' => 'naubade_viber']
    )->fetch_row();
    $botData = json_decode($botData[0]);
    $bot = new ViberBot($botData->bot_secret);
    $bot->addLogger($logger);
    $bot->debugModeEnable();

    $request = $bot->getUpdate();
    $update = json_decode($request);
    $knownUsers = json_decode(file_get_contents(__DIR__ . '/../tmp/viber-naubade-users.json'), true);
    $knownUsersUpdated = false;
    $knownUser = false;

    if (!empty($update->event)) {
        if ($update->event == 'subscribed') {
            if (empty($knownUsers[$update->user->id]['name'])) {
                $knownUsers[$update->user->id] = $update->user;
                $knownUsersUpdated = true;
            }

            $message = messages::$welcome_message;
            $bot->message($update->user)
                ->addText($message)
                ->addKeyboard([
                    ['ActionBody' => 'Освітні програми', 'Text' => 'Освітні програми', 'Columns' => 3, 'Rows' => 1],
                    ['ActionBody' => 'Переваги навчання', 'Text' => 'Переваги навчання', 'Columns' => 3, 'Rows' => 1],
                    ['ActionBody' => 'Контакти кафедри', 'Text' => 'Контакти кафедри', 'Columns' => 3, 'Rows' => 1],
                    ['ActionBody' => 'Замовити зворотній зв\'язок', 'Text' => 'Замовити зворотній зв\'язок', 'Columns' => 3, 'Rows' => 1],
                ], true, true)
                ->send();
        }

        if ($update->event == 'unsubscribed') {
            if (!empty($knownUsers[$update->user_id]['name'])) {
                unset($knownUsers[$update->user_id]);
                $knownUsersUpdated = true;
                $logger->log('User unsubscribed: ' . $update->user_id);
            }
        }

        if ($update->event == 'message' && $update->message->type == 'text') {
            $knownUser = !empty($knownUsers[$update->sender->id]['name']);
            $bot->message($update->sender)
                ->addText("Ви написали: " . $update->message->text)
                ->send();
        }
    }

    if ($knownUsersUpdated) {
        file_put_contents(__DIR__ . '/../tmp/viber-naubade-users.json', json_encode($knownUsers));
    }
} catch (Throwable $e) {
    $logger->log($e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
}