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

use Cron\CronExpression as WrappedCronExpression;

class CronExpression
{
    private WrappedCronExpression $wrappedCronExpression;

    public function __construct(string $cronExpression)
    {
        $this->wrappedCronExpression = new WrappedCronExpression($cronExpression);
    }

    public function isDue(): bool
    {
        return $this->wrappedCronExpression->isDue();
    }

    public function getPreviousRunDate(): \DateTime
    {
        return $this->wrappedCronExpression->getPreviousRunDate();
    }
}
