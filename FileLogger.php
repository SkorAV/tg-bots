<?php

class FileLogger implements LoggerInterface
{
    /** @var string */
    private $fileName;

    public function __construct(string $fileName) {
        if (!file_exists($fileName)) {
            if (!touch($fileName)) {
                throw new RuntimeException('Cannot create the log file.');
            }
        }

        if (!is_writable($fileName)) {
            throw new RuntimeException('Cannot write to the log file.');
        }

        $this->fileName = $fileName;
    }

    public function log($message)
    {
        file_put_contents($this->fileName, '[' .date('Y-m-d H:i:s') . '] ' . $message,FILE_APPEND);
    }
}