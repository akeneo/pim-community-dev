<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\RemoveAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\IncludeChildrenOption;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Webmozart\Assert\Assert;

/**
 * Checks the validity of an 'include_children' option
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class IncludeChildrenOptionValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($action, Constraint $constraint)
    {
        if (!$constraint instanceof IncludeChildrenOption) {
            throw new UnexpectedTypeException($constraint, IncludeChildrenOption::class);
        }
        Assert::isInstanceOf($action, RemoveAction::class);

        if (null === $action->includeChildren || !is_bool($action->includeChildren)) {
            return;
        }

        if ('categories' !== $action->field) {
            $this->context->buildViolation(
                $constraint->invalidFieldMessage,
                [
                    '{{ field }}' => $action->field,
                ]
            )->addViolation();
        }
    }
}
