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

namespace Akeneo\Platform\JobAutomation\Test\Acceptance\FakeService;

use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotifyCollection;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersToNotifyQueryInterface;

final class FakeFindUsersToNotifyQuery implements FindUsersToNotifyQueryInterface
{

    public function byUserIdsAndUserGroupsIds(array $userIds, array $userGroupIds): UserToNotifyCollection
    {
        return new UserToNotifyCollection([
            new UserToNotify('admin', 'admin@test.com'),
            new UserToNotify('julia', 'julia@test.com')
        ]);
    }
}
