<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\SaveAsymmetricKeysQuery;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use phpseclib3\Crypt\RSA;
use phpseclib3\File\X509;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_6_0_20211214000000_add_openid_keys_into_pim_configuration extends AbstractMigration implements ContainerAwareInterface
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

        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $this->connection->executeQuery($query, [
            'code' => SaveAsymmetricKeysQuery::OPTION_CODE,
            'asymmetricKeys' => array_merge(
                $asymmetricKeys->normalize(),
                ['updated_at' => $now->format(\DateTimeInterface::ATOM)]
            ),
        ], [
            'code' => Types::STRING,
            'asymmetricKeys' => Types::JSON,
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function generate(): AsymmetricKeys
    {
        /*
         * Following algorithm is the implementation documented by the phpseclib library
         * in order to generate self-signed public key and private key.
         * see http://phpseclib.sourceforge.net/x509/guide.html#selfsigned
         */
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

        return AsymmetricKeys::create($x509->saveX509($result), $privateKey->toString('PKCS1'));
    }
}
