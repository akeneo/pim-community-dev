<?php
declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\Query\SaveAsymmetricKeysQuery;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use phpseclib\Crypt\RSA;
use phpseclib\File\X509;


/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_6_0_20211214000000_add_openid_keys_into_pim_configuration extends AbstractMigration
{
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

        return AsymmetricKeys::create($x509->saveX509($result), $privateKey);
    }
}
