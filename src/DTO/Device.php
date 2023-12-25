<?php

namespace Nelwhix\WhatsappPhpClient\DTO;

class Device
{
    public string $jid;
    public int $registration_id;

    public string $noise_key;
    public string $identity_key;
    public string $signed_pre_key;

    public int $signed_pre_key_id;
    public string $signed_pre_key_sig;

    public string $adv_key;
    public string $adv_details;
    public string $adv_account_sig;
    public string $adv_account_sig_key;

    public string $adv_device_sig;
    public string $platform;
    public string $business_name;
    public string $push_name;

    public function __construct(string $jid, int $registration_id, string $noise_key, string $identity_key, string $signed_pre_key, int $signed_pre_key_id, string $signed_pre_key_sig, string $adv_key, string $adv_details, string $adv_account_sig, string $adv_account_sig_key, string $adv_device_sig, string $platform, string $business_name, string $push_name) {
        $this->jid = $jid;
        $this->registration_id = $registration_id;
        $this->noise_key = $noise_key;
        $this->identity_key = $identity_key;
        $this->signed_pre_key = $signed_pre_key;
        $this->signed_pre_key_id = $signed_pre_key_id;
        $this->signed_pre_key_sig = $signed_pre_key_sig;
        $this->adv_key = $adv_key;
        $this->adv_details = $adv_details;
        $this->adv_account_sig = $adv_account_sig;
        $this->adv_account_sig_key = $adv_account_sig_key;
        $this->adv_device_sig = $adv_device_sig;
        $this->platform = $platform;
        $this->business_name = $business_name;
        $this->push_name = $push_name;
    }
}