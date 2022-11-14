<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps;

use Akeneo\Connectivity\Connection\Domain\Apps\AsymmetricKeysGeneratorInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use phpseclib3\Crypt\RSA;
use phpseclib3\File\X509;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AsymmetricKeysGenerator implements AsymmetricKeysGeneratorInterface
{
    public function __construct(private string $openSSLConfigPath)
    {
    }

    public function generate(): AsymmetricKeys
    {
        /*
         * Following algorithm is the implementation documented by the phpseclib library
         * in order to generate self-signed public key and private key.
         * see http://phpseclib.sourceforge.net/x509/guide.html#selfsigned
         */
        RSA::setOpenSSLConfigPath($this->openSSLConfigPath);
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
