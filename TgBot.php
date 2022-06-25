<?php

class TgBot
{
    protected string $baseURL;
    private LoggerInterface $logger;
    private bool $debugMode = false;

    public function __construct(string $botSecret, ?LoggerInterface $logger)
    {
        $this->baseURL = 'https://api.telegram.org/bot' . $botSecret . '/';
        $this->logger = $logger;

    }

    public function getUpdate(): string
    {
        $request = file_get_contents('php://input');

        if ($this->debugMode)
        {
            $this->logger->log($request);
        }

        return $request;
    }

    public function sendText(string $message, stdClass $to, ?array $keyboard): bool
    {
        $data = [
            'text' => htmlspecialchars($message),
            'chat_id' => $to->id,
        ];

        if (is_array($keyboard)) {
            $data['reply_markup'] = json_encode([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]);
        } elseif (null === $keyboard) {
            $data['reply_markup'] = json_encode([
                'remove_keyboard' => true,
            ]);
        }

        return $this->callAPI($this->baseURL . 'sendMessage', $data);
    }

    private function callAPI(string $url, array $data): bool
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data,
        ]);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }
}