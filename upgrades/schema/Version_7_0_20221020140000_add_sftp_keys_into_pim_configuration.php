<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use phpseclib3\Crypt\RSA;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version_7_0_20221020140000_add_sftp_keys_into_pim_configuration extends AbstractMigration
{
    private ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

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
        $openSSLConfigPath = $this->container->getParameter('openssl_config_path');
        RSA::setOpenSSLConfigPath($openSSLConfigPath);

        $privateKey = RSA::createKey();
        $privateKey = $privateKey->withPadding(RSA::SIGNATURE_PKCS1);
        $publicKey = $privateKey->getPublicKey();

        return [
            'public_key' => $publicKey->toString('OpenSSH'),
            'private_key' => $privateKey->toString('OpenSSH'),
        ];
    }
}
