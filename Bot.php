<?php

abstract class Bot
{
    protected bool $debugMode = false;
    protected LoggerInterface $logger;

    public function debugModeEnable(): void
    {
        $this->debugMode = true;
    }

    public function debugModeDisable(): void
    {
        $this->debugMode = false;
    }

    public function addLogger(LoggerInterface $logger): void {
        $this->logger = $logger;
    }
}