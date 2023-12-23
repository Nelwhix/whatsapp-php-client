<?php

namespace Nelwhix\WhatsappPhpClient;

// Container is a wrapper for a SQL database that could contain multiple client sessions
use PDO;

class Container
{
    private \PDO $db;
    private $log;
    public $dbErrorHandler;
    private string $dialect;


    public function __construct(string $dialect, string $dsn, ?string $log)
    {
        $this->db = new PDO($dsn);
        $this->log = $log;
        $this->dialect = $dialect;
        $this->upgrade();
    }

    public function upgrade() {
        $version = $this->getVersion();

    }

    public function getVersion(): int {
        $stm = $this->db->query("CREATE TABLE IF NOT EXISTS client_version (VERSION INTEGER)");
        $stm->execute();

        
    }
}