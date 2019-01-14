<?php
declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration;

use phpseclib\File\X509;

class CertificateMetadata
{
    /** @var array */
    private $certificate;

    public function __construct(string $certificate)
    {

        $x509 = (new X509())->loadX509((string) $certificate);

        if(false === $x509)
        {
            throw new \InvalidArgumentException('The certificate is not valid');
        }

        $this->certificate = $x509;
    }

    public function getEndDate(): ?string
    {
        if(!empty($this->certificate['tbsCertificate']['validity']['notAfter']['utcTime']))
        {
            $endDate = $this->certificate['tbsCertificate']['validity']['notAfter']['utcTime'];
            return (new \DateTimeImmutable($endDate))->format('d/m/Y');
        }
    }
}
