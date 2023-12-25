<?php

namespace Nelwhix\WhatsappPhpClient;

use GuzzleHttp\Client;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;


class WhatsappClient
{
    public Client $client;
    public Device $store;
    public ?Logger $log;
    public string $uniqueId;

    public function __construct(Device $device, ?Logger $log) {
        $uniqueIdPrefix = random_bytes(2);
        $this->client = new Client();
        $this->store = $device;
        $this->log = $log;

        if($this->log === null) {
            $this->log = new Logger('client_logger');
            $this->log->pushHandler(new StreamHandler('php://stdout', Level::Debug));
        }

        $this->uniqueId = sprintf("%d.%d-", $uniqueIdPrefix[0], $uniqueIdPrefix[1]);
    }

    public function addEventHandler(callable $eventHandler) {
        
    }
}