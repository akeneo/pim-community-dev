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
 * Dynamic configuration for an Identity Provider, composed of a URL and a public certificate.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class IdentityProvider
{
    /** @var Url */
    private $url;

    /** @var Certificate */
    private $publicCertificate;

    private function __construct(Url $url, Certificate $publicCertificate)
    {
        $this->url = $url;
        $this->publicCertificate = $publicCertificate;
    }

    public static function create(Url $url, Certificate $publicCertificate): self
    {
        return new self($url, $publicCertificate);
    }
}
