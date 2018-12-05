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
 * Identifies a Service Provider or an Identity Provider. It's usually the URI to the metadata document but any
 * arbitrary unique value can be used.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class EntityId
{
    /** @var string */
    private $entityId;

    private function __construct(string $entityId)
    {
        $this->entityId = $entityId;
    }

    public static function fromString(string $entityId): self
    {
        if (!filter_var($entityId, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException(sprintf('Value must be a valid URL, "%s" given.', $entityId));
        }

        return new self($entityId);
    }

    public function toString(): string
    {
        return $this->entityId;
    }
}
