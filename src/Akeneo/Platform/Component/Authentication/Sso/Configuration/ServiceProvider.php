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

    public function toArray(): array
    {
        return [
            'entityId'           => $this->entityId->toString(),
            'publicCertificate'  => $this->publicCertificate->toString(),
            'privateCertificate' => $this->privateCertificate->toString(),
        ];
    }
}
