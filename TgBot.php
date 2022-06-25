<?php

class TgBot
{
    /** @var string */
    protected $base_url;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(string $base_url, ?LoggerInterface $logger)
    {
        $this->base_url = rtrim($base_url, '/') . '/';
        $this->logger = $logger;

    }

    public function getUpdate(): string
    {
        $request = file_get_contents('php://input');
        $this->logger->log($request);

        return $request;
    }

    public function sendText(string $message, stdClass $to, array|false|null $keyboard, bool $removeKeyboard = false): bool
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
        } elseif (false === $keyboard) {
            $data['reply_markup'] = json_encode([
                'remove_keyboard' => true,
            ]);
        }

        return $this->callAPI($this->base_url . 'sendMessage', $data);
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