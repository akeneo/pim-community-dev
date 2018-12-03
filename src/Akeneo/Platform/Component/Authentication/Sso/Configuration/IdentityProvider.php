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
 * Dynamic configuration for an Identity Provider, composed of an entity id, a URL and a public certificate.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class IdentityProvider
{
    /** @var EntityId */
    private $entityId;

    /** @var Url */
    private $url;

    /** @var Certificate */
    private $publicCertificate;

    public function __construct(
        EntityId $entityId,
        Url $url,
        Certificate $publicCertificate
    ) {
        $this->entityId = $entityId;
        $this->url = $url;
        $this->publicCertificate = $publicCertificate;
    }

    public static function fromArray(array $content): self
    {
        return new self(
            EntityId::fromString($content['entityId']),
            Url::fromString($content['url']),
            Certificate::fromString($content['publicCertificate'])
        );
    }

    public function toArray(): array
    {
        return [
            'entityId'          => $this->entityId->toString(),
            'url'               => $this->url->toString(),
            'publicCertificate' => $this->publicCertificate->toString(),
        ];
    }
}
