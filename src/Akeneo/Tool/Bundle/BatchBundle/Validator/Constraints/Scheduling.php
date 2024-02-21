<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Scheduling extends Constraint
{
    public const IMPORT_SHOULD_HAVE_STORAGE = 'akeneo.job_automation.validation.import_should_have_storage';

    public function validatedBy(): string
    {
        return 'akeneo_job_instance_scheduling_validator';
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
