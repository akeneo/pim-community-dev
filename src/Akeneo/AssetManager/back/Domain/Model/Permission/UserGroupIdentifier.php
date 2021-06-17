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

namespace Akeneo\AssetManager\Domain\Model\Permission;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class UserGroupIdentifier
{
    private int $identifier;

    private function __construct(int $identifier)
    {
        $this->identifier = $identifier;
    }

    public static function fromInteger(int $identifier): self
    {
        return new self($identifier);
    }

    public function normalize(): int
    {
        return $this->identifier;
    }

    public function equals(UserGroupIdentifier $otherUserGroupIdentifier): bool
    {
        return $this->identifier === $otherUserGroupIdentifier->identifier;
    }
}
