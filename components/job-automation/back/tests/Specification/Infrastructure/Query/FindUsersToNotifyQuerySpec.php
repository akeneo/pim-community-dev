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

namespace Specification\Akeneo\Platform\JobAutomation\Infrastructure\Query;

use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotifyCollection;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersByIdQueryInterface;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersByUserGroupIdQueryInterface;
use PhpSpec\ObjectBehavior;

class FindUsersToNotifyQuerySpec extends ObjectBehavior
{
    public function let(
        FindUsersByIdQueryInterface $findUsersByIdQuery,
        FindUsersByUserGroupIdQueryInterface $findUsersByUserGroupIdQuery,
    ): void {
        $this->beConstructedWith($findUsersByIdQuery, $findUsersByUserGroupIdQuery);
    }

    public function it_does_anything_when_it_receives_empty_arrays(
        FindUsersByIdQueryInterface $findUsersByIdQuery,
        FindUsersByUserGroupIdQueryInterface $findUsersByUserGroupIdQuery,
    ): void {
        $findUsersByIdQuery->execute()->shouldNotBeCalled();
        $findUsersByUserGroupIdQuery->execute()->shouldNotBeCalled();
        $this->byUserIdsAndUserGroupsIds([], [])->shouldBeLike(new UserToNotifyCollection([]));
    }

    public function it_find_users_to_notify(
        FindUsersByIdQueryInterface $findUsersByIdQuery,
        FindUsersByUserGroupIdQueryInterface $findUsersByUserGroupIdQuery,
    ): void {
        $julia = new UserToNotify('julia', 'julia@akeneo.com');
        $peter = new UserToNotify('peter', 'peter@akeneo.com');
        $mary = new UserToNotify('mary', 'mary@akeneo.com');
        $duplicatedMary = new UserToNotify('mary', 'mary@akeneo.com');

        $findUsersByIdQuery->execute([1])->shouldBeCalled()->willReturn([$mary]);
        $findUsersByUserGroupIdQuery->execute([2, 3])->shouldBeCalled()->willReturn([$julia, $peter, $duplicatedMary]);

        $this->byUserIdsAndUserGroupsIds([1], [2, 3])->shouldBeLike(new UserToNotifyCollection([
            $mary,
            $julia,
            $peter,
        ]));
    }
}
