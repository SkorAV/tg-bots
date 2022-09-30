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
    $bot = new TgBot($bot_data->bot_secret);
	$bot->addLogger($logger);

    $request = $bot->getUpdate();
    $update = json_decode($request);

    if (!empty($update->message)) {
        switch ($update->message->text) {
            default:
                $bot->message($update->message->from)
                    ->addText($update->message->text)
                    ->send();

                break;
        }
    }
} catch (Throwable $e) {
    $logger->log($e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL);
}

exit;
