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

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation\Automation;

use Symfony\Component\Validator\Constraint;

class CronExpression extends Constraint
{
    public const INVALID_FREQUENCY_OPTION = 'akeneo.job_automation.validation.invalid_frequency_option';
    public const INVALID_WEEK_DAY = 'akeneo.job_automation.validation.invalid_week_day';
    public const INVALID_HOURLY_FREQUENCY = 'akeneo.job_automation.validation.invalid_hourly_frequency';
    public const INVALID_TIME = 'akeneo.job_automation.validation.invalid_time';
}
