<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps;

use Akeneo\Connectivity\Connection\Domain\Apps\AsymmetricKeysGeneratorInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use phpseclib\Crypt\RSA;
use phpseclib\File\X509;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AsymmetricKeysGenerator implements AsymmetricKeysGeneratorInterface
{
    public function generate(): AsymmetricKeys
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
