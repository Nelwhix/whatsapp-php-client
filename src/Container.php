<?php

namespace Nelwhix\WhatsappPhpClient;

// Container is a wrapper for a SQL database that could contain multiple client sessions
use PDO;

class Container
{
    private \PDO $db;
    private $log;
    public $dbErrorHandler;


    public function __construct(string $dsn, ?string $log)
    {
        $this->db = new PDO($dsn);
        $this->log = $log;
    }

    public function upgrade() {

    }

    public function getVersion(): int {
        $stm = $this->db->query("CREATE TABLE IF NOT EXISTS client_version (VERSION INTEGER)");
        $stm->execute();

        
    }
}