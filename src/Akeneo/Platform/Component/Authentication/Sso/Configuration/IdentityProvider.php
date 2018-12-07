<?php

declare(strict_types=1);

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
            new EntityId($content['entityId']),
            new Url($content['url']),
            new Certificate($content['publicCertificate'])
        );
    }

    public function toArray(): array
    {
        return [
            'entityId'          => (string) $this->entityId,
            'url'               => (string) $this->url,
            'publicCertificate' => (string) $this->publicCertificate,
        ];
    }
}
