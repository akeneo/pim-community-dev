<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Component\Authentication\Sso\Configuration;

/**
 * Dynamic configuration for a Service Provider, composed of a public and a private certificate.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class ServiceProvider
{
    /** @var Certificate */
    private $publicCertificate;

    /** @var Certificate */
    private $privateCertificate;

    private function __construct(Certificate $publicCertificate, Certificate $privateCertificate)
    {
        $this->publicCertificate = $publicCertificate;
        $this->privateCertificate = $privateCertificate;
    }

    public static function create(Certificate $publicCertificate, Certificate $privateCertificate): self
    {
        return new self($publicCertificate, $privateCertificate);
    }
}
