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

namespace Specification\Akeneo\Platform\JobAutomation\Domain\Model;

use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotifyCollection;
use PhpSpec\ObjectBehavior;

class UserToNotifyCollectionSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->beConstructedWith([]);
        $this->beAnInstanceOf(UserToNotifyCollection::class);
    }

    public function it_returns_usernames(): void
    {
        $julia = new UserToNotify('julia', 'julia@akeneo.com');
        $peter = new UserToNotify('peter', 'peter@akeneo.com');
        $admin = new UserToNotify('admin', 'julia@akeneo.com');

        $this->beConstructedWith([$julia, $peter, $admin]);
        $this->getUsernames()->shouldReturn(['julia', 'peter', 'admin']);
    }

    public function it_returns_unique_emails(): void
    {
        $julia = new UserToNotify('julia', 'julia@akeneo.com');
        $peter = new UserToNotify('peter', 'peter@akeneo.com');
        $admin = new UserToNotify('admin', 'julia@akeneo.com');

        $this->beConstructedWith([$julia, $peter, $admin]);
        $this->getUniqueEmails()->shouldReturn(['julia@akeneo.com', 'peter@akeneo.com']);
    }
}
