<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\CertificateExpirationDate;
use phpseclib\File\X509;

final class CertificateMetadata
{
    /** @var CertificateExpirationDate */
    private $expirationDate;

    public function __construct(string $certificate)
    {
        $x509 = (new X509())->loadX509($certificate);

        if(false === $x509)  {
            throw new \InvalidArgumentException('The certificate is not valid.');
        }

        try {
            $this->expirationDate = new CertificateExpirationDate(
                $x509['tbsCertificate']['validity']['notAfter']['utcTime']
            );
        } catch (\InvalidArgumentException $e) {
            $this->expirationDate = null;
        }
    }

    public function getExpirationDate(): ?CertificateExpirationDate
    {
        return $this->expirationDate;
    }
}
