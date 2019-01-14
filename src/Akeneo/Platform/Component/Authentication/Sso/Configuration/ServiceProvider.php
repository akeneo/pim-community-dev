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
 * Dynamic configuration for a Service Provider, composed of an entity id, a certificate and a private key.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class ServiceProvider
{
    /** @var EntityId */
    private $entityId;

    /** @var Certificate */
    private $certificate;

    /** @var Certificate */
    private $privateKey;

    public function __construct(
        EntityId $entityId,
        Certificate $certificate,
        Certificate $privateKey
    ) {
        $this->entityId    = $entityId;
        $this->certificate = $certificate;
        $this->privateKey  = $privateKey;
    }

    public static function fromArray(array $content): self
    {
        return new self(
            new EntityId($content['entityId']),
            new Certificate($content['certificate']),
            new Certificate($content['privateKey'])
        );
    }

    public function toArray(): array
    {
        return [
            'entityId'    => (string) $this->entityId,
            'certificate' => (string) $this->certificate,
            'privateKey'  => (string) $this->privateKey,
        ];
    }
}
