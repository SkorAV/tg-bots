<?php

class ViberBot extends Bot implements BotInterface
{
    private static string $baseURL = 'https://chatapi.viber.com/pa/';
    private string $botSecret;
    private array $data;

    public function __construct(string $botSecret)
    {
        $this->botSecret = $botSecret;
    }

    public function getUpdate(): string
    {
        $request = file_get_contents('php://input');

        if ($this->debugMode) {
            $this->logger->log($request);
        }

        return $request;
    }

    public function message($to): self
    {
        $this->data = [
            'receiver' => $to->id,
            'sender' => [
                'name' => 'Кафедра бізнес-аналітики',
            ],
        ];

        return $this;
    }

    public function addText(string $message, ...$properties): self
    {
        $this->data['type'] = 'text';
        $this->data['text'] = $message;

        return $this;
    }

    public function addKeyboard(array $keyboard, ...$properties): self
    {
        $this->data['keyboard'] = [
            "Type" => "keyboard",
            "DefaultHeight" => !$properties[0],
            "Buttons" => $keyboard,
        ];

        return $this;
    }

    public function send(): ?stdClass
    {
        return $this->callAPI('send_message', json_encode($this->data));
    }

    private function callAPI(string $url, array|string $data): ?stdClass
    {
        $curl = curl_init(self::$baseURL . $url);
        curl_setopt_array($curl, [
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'X-Viber-Auth-Token: ' . $this->botSecret,
                'Content-Type: application/json',
            ],
        ]);

        $result = curl_exec($curl);

        if ($this->debugMode) {
            $this->logger->log('Result:' . $result);
        }

        curl_close($curl);

        return json_decode($result);
    }
}