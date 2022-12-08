<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Domain\Model;

use Webmozart\Assert\Assert;

class UserToNotifyCollection
{
    /**
     * @param array<UserToNotify> $usersToNotify
     */
    public function __construct(
        private readonly array $usersToNotify,
    ) {
        Assert::allIsInstanceOf($usersToNotify, UserToNotify::class);
    }

    /**
     * @return array<string>
     */
    public function getUsernames(): array
    {
        return array_map(static fn (UserToNotify $userToNotify) => $userToNotify->getUsername(), $this->usersToNotify);
    }

    /**
     * @return array<string>
     */
    public function getUniqueEmails(): array
    {
        return array_unique(array_map(static fn (UserToNotify $userToNotify) => $userToNotify->getEmail(), $this->usersToNotify));
    }
}
