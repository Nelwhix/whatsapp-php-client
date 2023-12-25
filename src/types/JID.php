<?php

namespace Nelwhix\WhatsappPhpClient\types;

class JID
{
    public string $user;
    public int $rawAgent;
    public int $device;
    public int $integrator;
    public string $server;
}