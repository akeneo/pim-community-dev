<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use phpseclib\Crypt\RSA;
use phpseclib\File\X509;

class Version_7_0_20221020140000_add_sftp_keys_into_pim_configuration extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $query = <<<SQL
            INSERT INTO pim_configuration (`code`,`values`)
            VALUES (:code, :asymmetricKeys)
            ON DUPLICATE KEY UPDATE `values`= :asymmetricKeys
            SQL;

        $asymmetricKeys = $this->generate();

        $this->connection->executeQuery($query, [
            'code' => 'SFTP_ASYMMETRIC_KEYS',
            'asymmetricKeys' => $asymmetricKeys,
        ], [
            'code' => Types::STRING,
            'asymmetricKeys' => Types::JSON,
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function generate(): array
    {
        $privKey = new RSA();
        $keys = $privKey->createKey();
        $privateKey = $keys['privatekey'];
        $publicKey = $keys['publickey'];

        $pubKey = new RSA();
        $pubKey->loadKey($publicKey);
        $pubKey->setPublicKey();

        $subject = new X509();
        $subject->setEndDate('99991231235959Z');
        $subject->setDNProp('id-at-organizationName', 'Akeneo');
        $subject->setPublicKey($pubKey);

        $issuer = new X509();
        $issuer->setPrivateKey($privKey);
        $issuer->setDn($subject->getDN());

        $x509 = new X509();
        $result = $x509->sign($issuer, $subject);

        return [
            'public_key' => $x509->saveX509($result),
            'private_key' => $privateKey,
        ];
    }
}
