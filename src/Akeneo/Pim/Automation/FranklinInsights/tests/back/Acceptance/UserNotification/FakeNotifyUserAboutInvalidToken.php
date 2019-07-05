<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Acceptance\UserNotification;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Notifier\InvalidTokenNotifierInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FakeNotifyUserAboutInvalidToken implements InvalidTokenNotifierInterface
{
    public function notify(): void
    {
    }
}
