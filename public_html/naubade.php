<?php
require_once __DIR__ . '/autoloader.php';

$logger = new FileLogger(__DIR__ . '/../tmp/naubade.log');

try {
    $db = DBAccess::connect('localhost', 'u374561970_andrii', '#Jx+u52wR*9', 'u374561970_servicepages');
    $bot_data = $db->select('
        SELECT
            c.value
        FROM config c
        WHERE c.name = ?;',
        ['s' => 'naubade_bot']
    )->fetch_row();
    $bot_data = json_decode($bot_data[0]);
    $bot = new TgBot($bot_data->bot_secret, $logger);
    $bot->debugModeEnable();

    $request = $bot->getUpdate();
    $update = json_decode($request);
    $known_users = json_decode(file_get_contents(__DIR__ . '/../tmp/naubade-users.json'), true);
    $known_users_updated = false;

    if (!empty($update->message)) {
        switch ($update->message->text) {
            case '/start':
                if (empty($known_users[$update->message->from->id])) {
                    $known_users[$update->message->from->id] = $update->message->from;
                    $known_users_updated = true;
                }

                $message = <<<'TXT'
Якщо ви бажаєте:
- вирішувати складні економічні проблеми;
- удосконалити володіння діловою іноземною мовою;
- розвинути лідерські організаційні якості креативного та нестандарт-ного мислення
Тоді ми чекаємо саме на Вас!

Скористайтесь меню нижче, щоб дізнатися більше.
TXT;
                $bot->message($update->message->from)
                    ->addText($message, [
                        ["type" => "bold", "offset" => 192, "length" => 28],
                        ["type" => "italic", "offset" => 192, "length" => 28]
                    ])
                    ->addKeyboard([
                        [['text' => 'Освітні програми'], ['text' => 'Переваги навчання']],
                        [['text' => 'Контакти кафедри'], ['text' => 'Замовити зворотній зв\'язок']],
                    ], true, true)
                    ->send();

                break;
            case 'Освітні програми':
                $message = <<<'TXT'
КАФЕДРА БІЗНЕС-АНАЛІТИКИ ТА ЦИФРОВОЇ ЕКОНОМІКИ
оголошує набір за спеціальністю
051 «ЕКОНОМІКА» 
на освітньо-професійні програми:
TXT;

                $bot->message($update->message->from)
                    ->addText($message, [["type" => "bold", "offset" => 78, "length" => 15]])
                    ->addInlineKeyboard([
                        [['text' => 'ЕКОНОМІЧНА КІБЕРНЕТИКА', 'callback_data' => 'ec']],
                        [['text' => 'ЦИФРОВА ЕКОНОМІКА', 'callback_data' => 'de']],
                        [['text' => 'МІЖНАРОДНА ЕКОНОМІКА', 'callback_data' => 'ie']],
                    ])
                    ->send();

                break;
            case 'Переваги навчання':
                break;
            case 'Контакти кафедри':
                break;
            case 'Замовити зворотній зв\'язок':
                break;
            case '/stop':
                unset($known_users[$update->message->from->id]);
                $known_users_updated = true;

                break;
            default:
                $bot->message($update->message->from)
                    ->addText($update->message->text)
                    ->send();

                break;
        }
    }

    if ($known_users_updated) {
        file_put_contents(__DIR__ . '/../tmp/naubade-users.json', json_encode($known_users));
    }
} catch (Throwable $e) {
    $logger->log($e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
}

exit;
