<?php

class TgBot extends Bot implements BotInterface
{
    protected string $baseURL;
    private array $data;

    const FORMAT_MARKDOWNV2 = 'MarkdownV2';
    const FORMAT_HTML = 'HTML';
    const FORMAT_DEFAULT = null;

    public function __construct(string $botSecret)
    {
        $this->baseURL = 'https://api.telegram.org/bot' . $botSecret . '/';
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

    public function message($to): self
    {
        $this->data = ['chat_id' => $to->id];

        return $this;
    }

    public function addText(string $message, ...$properties): self
    {
        $this->data['text'] = $message;


        if (!empty($entities)) {
            $this->data['entities'] = json_encode($properties[0]);
        } else if (null !== $properties[1]) {
            $this->data['parse_mode'] = $properties[1];
        }

        return $this;
    }

    public function addKeyboard(array $keyboard, ...$properties): self
    {
        if (!empty($keyboard)) {
            $this->data['reply_markup'] = json_encode([
                'keyboard' => $keyboard,
                'resize_keyboard' => $properties[0],
                'one_time_keyboard' => $properties[1],
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

    public function send(): stdClass
    {
        if (empty($this->data['text'])) {
            throw new \http\Exception\BadMethodCallException('No message to send! Call "addText()" first.');
        }

        return $this->callAPI($this->baseURL . 'sendMessage', $this->data);
    }

    public function answerCallbackQuery(string $queryId): stdClass
    {
        return $this->callAPI($this->baseURL . 'answerCallbackQuery', ['callback_query_id' => $queryId]);
    }

    public function editMessageText(string $chatId, string $messageId, string $text, ?string $format = null): stdClass
    {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
        ];

        if (null !== $format) {
            $data['parse_mode'] = $format;
        }

        return $this->callAPI($this->baseURL . 'editMessageText', $data);
    }

    public function editMessageReplyMarkup(string $chatId, string $messageId, array $keyboard): stdClass
    {
        return $this->callAPI($this->baseURL . 'editMessageReplyMarkup', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
        ]);
    }

    private function callAPI(string $url, array $data): stdClass
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $result = curl_exec($curl);
        curl_close($curl);

        return json_decode($result);
    }
}