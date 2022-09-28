<?php

declare(strict_types=1);

/*²
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Domain\Model;

/** @readonly  */
final class DueJobInstance
{
    public function __construct(
        public ScheduledJobInstance $scheduledJobInstance,
        public UserToNotifyCollection $usersToNotify,
    ) {
    }
}
