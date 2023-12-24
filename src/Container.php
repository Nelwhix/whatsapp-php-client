<?php

namespace Nelwhix\WhatsappPhpClient;

// Container is a wrapper for a SQL database that could contain multiple client sessions
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use PDO;
use Nelwhix\WhatsappPhpClient\Utils\Utils;
use Monolog\Logger;

class Container
{
    private \PDO $db;
    private ?Logger $log;
    public $dbErrorHandler;
    private string $dialect;

    private static $upgrades = [];

    public function __construct(string $dialect, string $dsn, ?Logger $log = null)
    {
        $this->db = new PDO($dsn);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        if ($dialect === "sqlite") {
            $this->db->exec('PRAGMA foreign_keys = ON;');
        }

        $this->dialect = $dialect;
        $this->log = $log;

        if($this->log === null) {
            $this->log = new Logger('whatsapp_php_logger');
            $this->log->pushHandler(new StreamHandler('php://stdout', Level::Debug));
        }

        self::$upgrades = [
            [$this, 'upgradeV1'],
            [$this, 'upgradeV2'],
            [$this, 'upgradeV3'],
            [$this, 'upgradeV4'],
            [$this, 'upgradeV5'],
        ];

        $this->upgrade();
    }

    // Upgrade upgrades the database from the current to the latest version available.
    public function upgrade() {
        if ($this->dialect === "sqlite") {
            $stm = $this->db->query("PRAGMA foreign_keys");
            $stm->execute();

            $result = $stm->fetch();

            if ($result['foreign_keys'] === 0) {
                throw new \Exception("foreign keys are not enabled");
            }
        }
        $version = $this->getVersion();

        try {
            for($i = $version; $i < count(self::$upgrades); $i++) {
                $this->db->beginTransaction();
                $migrateFunc = self::$upgrades[$i];
                $this->log->info("Upgrading database to v" . ($i + 1));
                $migrateFunc();

                $this->setVersion($i + 1);

                $this->db->commit();
            }

        } catch (\PDOException $e) {
            $this->log->error($e->getLine() . " " . $e->getMessage());
            $this->db->rollBack();
        }


    }

    public function getVersion(): int {
        $stm = $this->db->query("CREATE TABLE IF NOT EXISTS client_version (VERSION INTEGER)");
        $stm->execute();

        $stm = $this->db->query("SELECT version FROM client_version LIMIT 1");
        $stm->execute();
        $version = $stm->fetch();

        if ($version) {
            return $version['VERSION'];
        }

        return 0;
    }

    public function setVersion(int $version) {
        $stm = $this->db->prepare("DELETE FROM client_version");
        $stm->execute();

        $stm = $this->db->prepare("INSERT INTO client_version (version) VALUES (:version)");
        $stm->bindParam(':version', $version, PDO::PARAM_INT);
        $stm->execute();
        $hello = "how far";
    }

    public function upgradeV1() {
        $this->db->exec("CREATE TABLE client_device (
            jid TEXT PRIMARY KEY,

		registration_id BIGINT NOT NULL CHECK ( registration_id >= 0 AND registration_id < 4294967296 ),

		noise_key    bytea NOT NULL CHECK ( length(noise_key) = 32 ),
		identity_key bytea NOT NULL CHECK ( length(identity_key) = 32 ),

		signed_pre_key     bytea   NOT NULL CHECK ( length(signed_pre_key) = 32 ),
		signed_pre_key_id  INTEGER NOT NULL CHECK ( signed_pre_key_id >= 0 AND signed_pre_key_id < 16777216 ),
		signed_pre_key_sig bytea   NOT NULL CHECK ( length(signed_pre_key_sig) = 64 ),

		adv_key         bytea NOT NULL,
		adv_details     bytea NOT NULL,
		adv_account_sig bytea NOT NULL CHECK ( length(adv_account_sig) = 64 ),
		adv_device_sig  bytea NOT NULL CHECK ( length(adv_device_sig) = 64 ),

		platform      TEXT NOT NULL DEFAULT '',
		business_name TEXT NOT NULL DEFAULT '',
		push_name     TEXT NOT NULL DEFAULT ''
	)");

        $this->db->exec("CREATE TABLE client_identity_keys (
		our_jid  TEXT,
		their_id TEXT,
		identity bytea NOT NULL CHECK ( length(identity) = 32 ),

		PRIMARY KEY (our_jid, their_id),
		FOREIGN KEY (our_jid) REFERENCES client_device(jid) ON DELETE CASCADE ON UPDATE CASCADE
	)");

        $this->db->exec("CREATE TABLE client_pre_keys (
		jid      TEXT,
		key_id   INTEGER          CHECK ( key_id >= 0 AND key_id < 16777216 ),
		key      bytea   NOT NULL CHECK ( length(key) = 32 ),
		uploaded BOOLEAN NOT NULL,

		PRIMARY KEY (jid, key_id),
		FOREIGN KEY (jid) REFERENCES client_device(jid) ON DELETE CASCADE ON UPDATE CASCADE
	)");

        $this->db->exec("CREATE TABLE client_sessions (
		our_jid  TEXT,
		their_id TEXT,
		session  bytea,

		PRIMARY KEY (our_jid, their_id),
		FOREIGN KEY (our_jid) REFERENCES client_device(jid) ON DELETE CASCADE ON UPDATE CASCADE
	)");

        $this->db->exec("CREATE TABLE client_sender_keys (
            our_jid    TEXT,
		chat_id    TEXT,
		sender_id  TEXT,
		sender_key bytea NOT NULL,

		PRIMARY KEY (our_jid, chat_id, sender_id),
		FOREIGN KEY (our_jid) REFERENCES client_device(jid) ON DELETE CASCADE ON UPDATE CASCADE
	)");

        $this->db->exec("CREATE TABLE client_app_state_sync_keys (
		jid         TEXT,
		key_id      bytea,
		key_data    bytea  NOT NULL,
		timestamp   BIGINT NOT NULL,
		fingerprint bytea  NOT NULL,

		PRIMARY KEY (jid, key_id),
		FOREIGN KEY (jid) REFERENCES client_device(jid) ON DELETE CASCADE ON UPDATE CASCADE
	)");

        $this->db->exec("CREATE TABLE client_app_state_version (
		jid     TEXT,
		name    TEXT,
		version BIGINT NOT NULL,
		hash    bytea  NOT NULL CHECK ( length(hash) = 128 ),

		PRIMARY KEY (jid, name),
		FOREIGN KEY (jid) REFERENCES client_device(jid) ON DELETE CASCADE ON UPDATE CASCADE
	)");

        $this->db->exec("CREATE TABLE client_app_state_mutation_macs (
		jid       TEXT,
		name      TEXT,
		version   BIGINT,
		index_mac bytea          CHECK ( length(index_mac) = 32 ),
		value_mac bytea NOT NULL CHECK ( length(value_mac) = 32 ),

		PRIMARY KEY (jid, name, version, index_mac),
		FOREIGN KEY (jid, name) REFERENCES client_app_state_version(jid, name) ON DELETE CASCADE ON UPDATE CASCADE
	)");

        $this->db->exec("CREATE TABLE client_contacts (
		our_jid       TEXT,
		their_jid     TEXT,
		first_name    TEXT,
		full_name     TEXT,
		push_name     TEXT,
		business_name TEXT,

		PRIMARY KEY (our_jid, their_jid),
		FOREIGN KEY (our_jid) REFERENCES client_device(jid) ON DELETE CASCADE ON UPDATE CASCADE
	)");

        $this->db->exec("CREATE TABLE client_chat_settings (
		our_jid       TEXT,
		chat_jid      TEXT,
		muted_until   BIGINT  NOT NULL DEFAULT 0,
		pinned        BOOLEAN NOT NULL DEFAULT false,
		archived      BOOLEAN NOT NULL DEFAULT false,

		PRIMARY KEY (our_jid, chat_jid),
		FOREIGN KEY (our_jid) REFERENCES client_device(jid) ON DELETE CASCADE ON UPDATE CASCADE
	)");
    }

    const fillSigKeySQLite = <<<SQL
        UPDATE client_device SET adv_account_sig_key=(
	SELECT identity
	FROM client_identity_keys
	WHERE our_jid=client_device.jid
	  AND their_id=substr(client_device.jid, 0, instr(client_device.jid, '.')) || ':0'
)
SQL;

    public function upgradeV2() {
        $this->db->exec("ALTER TABLE client_device ADD COLUMN adv_account_sig_key bytea CHECK ( length(adv_account_sig_key) = 32 )");

        if ($this->dialect === "sqlite") {
            $this->db->exec(self::fillSigKeySQLite);
        }
    }

    public function upgradeV3() {
        $this->db->exec("CREATE TABLE client_message_secrets (
		our_jid    TEXT,
		chat_jid   TEXT,
		sender_jid TEXT,
		message_id TEXT,
		key        bytea NOT NULL,

		PRIMARY KEY (our_jid, chat_jid, sender_jid, message_id),
		FOREIGN KEY (our_jid) REFERENCES client_device(jid) ON DELETE CASCADE ON UPDATE CASCADE
	)");
    }

    public function upgradeV4() {
        $this->db->exec("CREATE TABLE client_privacy_tokens (
		our_jid   TEXT,
		their_jid TEXT,
		token     bytea  NOT NULL,
		timestamp BIGINT NOT NULL,
		PRIMARY KEY (our_jid, their_jid)
	)");
    }

    public function upgradeV5() {
        $this->db->exec("UPDATE client_device SET jid=REPLACE(jid, '.0', '')");
    }

    public function get

    public function getFirstDevice() {
        $devices = $this->getAllDevices();

        if (count($devices) === 0) {
            return $this->getNewDevice();
        }

        return $devices[0];
    }
}