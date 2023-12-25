<?php

namespace Nelwhix\WhatsappPhpClient;

use Monolog\Logger;
use Nelwhix\WhatsappPhpClient\types\JID;
use Nelwhix\WhatsappPhpClient\utils\keys\KeyPair;

class Device
{
    public JID $id;
    private Logger $log;
    private Container $container;
    private $dbErrorHandler;
    private KeyPair $noiseKey;
    public KeyPair $identityKey;
    private $registrationId;
    private $advSecretKey;
    public $signedPreKey;

    public function __construct($log, $container, $dbErrorHandler, KeyPair $noiseKey, KeyPair $identityKey, $registrationId, $advSecretKey) {
        $this->log = $log;
        $this->container = $container;
        $this->dbErrorHandler = $dbErrorHandler;
        $this->noiseKey = $noiseKey;
        $this->identityKey = $identityKey;
        $this->registrationId = $registrationId;
        $this->advSecretKey = $advSecretKey;
    }
}