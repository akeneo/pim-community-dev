<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use phpseclib3\Crypt\RSA;
use phpseclib3\File\X509;
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
        /** @var RSA\PrivateKey $privateKey */
        $privateKey = RSA::createKey();
        $privateKey = $privateKey->withPadding(RSA::SIGNATURE_PKCS1);
        $publicKey = $privateKey->getPublicKey();

        $subject = new X509();
        $subject->setEndDate('99991231235959Z');
        $subject->setDNProp('id-at-organizationName', 'Akeneo');
        $subject->setPublicKey($publicKey);

        $issuer = new X509();
        $issuer->setPrivateKey($privateKey);
        $issuer->setDn($subject->getDN());

        $x509 = new X509();
        $x509->makeCA();
        $result = $x509->sign($issuer, $subject);

        return [
            'public_key' => $x509->saveX509($result),
            'private_key' => $privateKey,
        ];
    }
}
