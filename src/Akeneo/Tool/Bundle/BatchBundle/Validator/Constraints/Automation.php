<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Automation extends Constraint
{
    public function validatedBy(): string
    {
        return 'akeneo_job_instance_automation_validator';
    }
}
