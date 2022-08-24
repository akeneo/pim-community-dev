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
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ScheduledJobInstanceValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ScheduledJobInstance) {
            throw new UnexpectedTypeException($constraint, ScheduledJobInstance::class);
        }

        if (!$value->isScheduled) {
            $this->context->buildViolation(ScheduledJobInstance::SCHEDULED_SHOULD_BE_ENABLED)->addViolation();
        }

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        if (JobInstance::TYPE_EXPORT === $value->type) {
            return;
        }

        $storage = $value->rawParameters['storage'];

        if (NoneStorage::TYPE === $storage['type']) {
            $this->context->buildViolation(ScheduledJobInstance::IMPORT_SHOULD_HAVE_STORAGE)->addViolation();
        }
    }
}
