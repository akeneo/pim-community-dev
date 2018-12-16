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
 * Dynamic configuration for a Service Provider, composed of an entity id, a public and a private certificate.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class ServiceProvider
{
    /** @var EntityId */
    private $entityId;

    /** @var Certificate */
    private $publicCertificate;

    /** @var Certificate */
    private $privateCertificate;

    public function __construct(
        EntityId $entityId,
        Certificate $publicCertificate,
        Certificate $privateCertificate
    ) {
        $this->entityId = $entityId;
        $this->publicCertificate = $publicCertificate;
        $this->privateCertificate = $privateCertificate;
    }

    public static function fromArray(array $content): self
    {
        return new self(
            new EntityId($content['entityId']),
            new Certificate($content['publicCertificate']),
            new Certificate($content['privateCertificate'])
        );
    }

    public function toArray(): array
    {
        return [
            'entityId'           => (string) $this->entityId,
            'publicCertificate'  => (string) $this->publicCertificate,
            'privateCertificate' => (string) $this->privateCertificate,
        ];
    }
}
