<?php

interface BotInterface
{
    public function getUpdate(): string;

    public function message($to): self;

    public function addText(string $message, ...$properties): self;

    public function addKeyboard(array $keyboard, ...$properties): self;

    public function send(): ?stdClass;
}