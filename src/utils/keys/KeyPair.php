<?php

namespace Nelwhix\WhatsappPhpClient\utils\keys;

use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;
use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use ParagonIE\EasyECC\EasyECC;

class KeyPair
{
    public EasyECC  $ecc;
    public PublicKeyInterface $publicKey;

    public PrivateKeyInterface $privateKey;

    public function __construct()
    {
        $ecc = new EasyECC();
        $privateKey = $ecc->generatePrivateKey();
        $publicKey = $privateKey->getPublicKey();

        $this->ecc = $ecc;
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
    }


    public function createSignedPreKey(int $keyId) {
        $newKey = new PreKey($keyId);
        $newKey->signature = $this->sign($newKey->keyPair);
        return $newKey;
    }

    public function sign(KeyPair $keyPair): string {
       return $this->ecc->sign($keyPair->publicKey, $this->privateKey);
    }
}