<?php

class TgBot
{
    protected string $baseURL;
    private LoggerInterface $logger;
    private bool $debugMode = false;
    private array $data;

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

    public function message(stdClass $to): self
    {
        $this->data = ['chat_id' => $to->id];

        return $this;
    }

    public function addText(string $message, ?array $entities = null): self
    {
        $this->data['text'] = htmlspecialchars($message);


        if (!empty($entities)) {
            $this->data['entities'] = json_encode($entities);
        }

        return $this;
    }

    public function addKeyboard(array $keyboard, bool $resize = true, bool $oneTime = false): self
    {
        if (!empty($keyboard)) {
            $this->data['reply_markup'] = json_encode([
                'keyboard' => $keyboard,
                'resize_keyboard' => $resize,
                'one_time_keyboard' => $oneTime,
            ]);
        }

        return $this;
    }

    public function addInlineKeyboard(array $keyboard): self
    {
        if (!empty($keyboard)) {
            $this->data['reply_markup'] = json_encode([
                'inline_keyboard' => $keyboard,
            ]);
        }

        return $this;
    }

    public function removeKeyboard(): self
    {
        $this->data['reply_markup'] = json_encode([
            'remove_keyboard' => true,
        ]);

        return $this;
    }

    public function send(): bool
    {
        return $this->callAPI($this->baseURL . 'sendMessage', $this->data);
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