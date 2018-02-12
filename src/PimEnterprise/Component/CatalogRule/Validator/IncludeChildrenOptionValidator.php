<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Validator;

use PimEnterprise\Component\CatalogRule\Model\ProductRemoveActionInterface;
use PimEnterprise\Component\CatalogRule\Validator\Constraint\IncludeChildrenOption;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

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

        if (!$action instanceof ProductRemoveActionInterface) {
            throw new \LogicException(sprintf('Action of type "%s" can not be validated.', gettype($action)));
        }

        $options = $action->getOptions();
        if (!isset($options['include_children'])) {
            return;
        }

        if ('categories' !== $action->getField()) {
            $this->context->buildViolation(
                $constraint->invalidFieldMessage,
                [
                    '%field%' => $action->getField(),
                ]
            )->addViolation();

            return;
        }

        if (!is_bool($options['include_children'])) {
            $this->context->buildViolation(
                $constraint->invalidTypeMessage,
                [
                    '%type%' => gettype($options['include_children']),
                ]
            )->addViolation();
        }
    }
}
