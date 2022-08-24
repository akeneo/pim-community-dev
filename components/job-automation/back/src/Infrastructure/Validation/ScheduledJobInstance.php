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

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

final class ScheduledJobInstance extends Constraint
{
    public const SCHEDULED_SHOULD_BE_ENABLED = 'akeneo.job_automation.validation.scheduled_should_be_enabled';
    public const IMPORT_SHOULD_HAVE_STORAGE = 'akeneo.job_automation.validation.import_should_have_storage';
}
