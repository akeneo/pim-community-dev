<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProviderDefaultConfiguration as ServiceProviderDefaultConfigurationInterface;
use phpseclib3\Crypt\RSA;
use phpseclib3\File\X509;

class ServiceProviderDefaultConfiguration implements ServiceProviderDefaultConfigurationInterface
{
    public function __construct(private string $akeneoPimUrl)
    {
    }

    public function getServiceProvider(): ServiceProvider
    {
        /*
         * Following algorithm is the implementation documented by the phpseclib library
         * in order to generate self-signed public key and private key.
         * ( see http://phpseclib.sourceforge.net/x509/guide.html#selfsigned )
         */
        RSA::setOpenSSLConfigPath(__DIR__ . '/openssl.cnf');
        /** @var RSA\PrivateKey $privateKey */
        $privateKey = RSA::createKey();
        $privateKey = $privateKey->withPadding(RSA::SIGNATURE_PKCS1);
        $publicKey = $privateKey->getPublicKey();

        $subject = new X509();
        $subject->setEndDate('99991231235959Z'); #Value to define unlimited certificate (see https://tools.ietf.org/html/rfc5280#section-4.1.2.5)
        $subject->setDNProp('id-at-organizationName', 'Akeneo');
        $subject->setPublicKey($publicKey);

        $issuer = new X509();
        $issuer->setPrivateKey($privateKey);
        $issuer->setDn($subject->getDN());

        $x509 = new X509();
        $x509->makeCA();
        $result = $x509->sign($issuer, $subject);

        $serviceProviderPublicKey =  $x509->saveX509($result);
        $serviceProviderPrivateKey = $privateKey;

        return ServiceProvider::fromArray([
            'entityId'    => sprintf('%s/saml/metadata', $this->akeneoPimUrl),
            'certificate' => $serviceProviderPublicKey,
            'privateKey'  => $serviceProviderPrivateKey->toString('PKCS1'),
        ]);
    }
}
