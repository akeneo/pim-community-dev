<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProviderDefaultConfiguration as ServiceProviderDefaultConfigurationInterface;
use phpseclib\Crypt\RSA;
use phpseclib\File\X509;

class ServiceProviderDefaultConfiguration implements ServiceProviderDefaultConfigurationInterface
{
    /** @var string */
    private $akeneoPimUrl;

    public function __construct(string $akeneoPimUrl)
    {
        $this->akeneoPimUrl = $akeneoPimUrl;
    }

    public function getServiceProvider(): ServiceProvider
    {
        /*
         * Following algorithm is the implementation documented by the phpseclib library
         * in order to generate self-signed public key and private key.
         * ( see http://phpseclib.sourceforge.net/x509/guide.html#selfsigned )
         */
        $privKey = new RSA();
        $keys = $privKey->createKey();
        $privateKey = $keys['privatekey'];
        $publicKey = $keys['publickey'];
        $privKey->loadKey($privateKey);

        $pubKey = new RSA();
        $pubKey->loadKey($publicKey);
        $pubKey->setPublicKey();

        $subject = new X509();
        $subject->setEndDate('99991231235959Z'); #Value to define unlimited certificate (see https://tools.ietf.org/html/rfc5280#section-4.1.2.5)
        $subject->setDNProp('id-at-organizationName', 'Akeneo');
        $subject->setPublicKey($pubKey);

        $issuer = new X509();
        $issuer->setPrivateKey($privKey);
        $issuer->setDn($subject->getDN());

        $x509 = new X509();
        $result = $x509->sign($issuer, $subject);

        $serviceProviderPublicKey =  $x509->saveX509($result);
        $serviceProviderPrivateKey = $privKey->getPrivateKey();

        return ServiceProvider::fromArray([
            'entityId'    => sprintf('%s/saml/metadata', $this->akeneoPimUrl),
            'certificate' => $serviceProviderPublicKey,
            'privateKey'  => $serviceProviderPrivateKey
        ]);
    }
}
