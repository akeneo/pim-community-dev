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

use Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints\Automation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class AutomationValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Automation) {
            throw new UnexpectedTypeException($constraint, Automation::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'cron_expression' => new CronExpression(),
                'running_user_groups' => [
                    new All(new Type('string')),
                    new Type('array'),
                ],
                'notification_user_groups' => [
                    new All(new Type('string')),
                    new Type('array'),
                ],
                'notification_users' => [
                    new All(new Type('string')),
                    new Type('array'),
                ],
                'setup_date' => new Optional([new NotBlank(), new DateTime(['format' => DATE_ATOM])]),
                'last_execution_date' => new Optional([new DateTime(['format' => DATE_ATOM])]),
            ],
        ]));
    }
}
