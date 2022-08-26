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

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints\Scheduling;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class SchedulingValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Scheduling) {
            throw new UnexpectedTypeException($constraint, Scheduling::class);
        }

        if (!$value instanceof JobInstance) {
            throw new UnexpectedTypeException($value, JobInstance::class);
        }

        if (JobInstance::TYPE_EXPORT === $value->getType()) {
            return;
        }

        if ($value->isScheduled()
            && NoneStorage::TYPE === $value->getRawParameters()['storage']['type']
        ) {
            $this->context->buildViolation(Scheduling::IMPORT_SHOULD_HAVE_STORAGE)
                ->atPath('[scheduled]')
                ->addViolation();
        }
    }
}
