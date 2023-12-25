<?php

namespace Nelwhix\WhatsappPhpClient\utils\keys;

class PreKey
{
    public KeyPair $keyPair;
    public int $keyId;

    public string $signature;

    public function __construct(int $keyId) {
        $this->keyId = $keyId;
        $this->keyPair = new KeyPair();
    }
}