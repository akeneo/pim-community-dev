<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule;

use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates if the set action field supports the given data
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ValueActionValidator extends ConstraintValidator
{
    /** @var ProductUpdaterInterface */
    protected $factory;

    /**
     * @param ProductUpdaterInterface $factory
     */
    public function __construct(
        ProductUpdaterInterface $factory
    ) {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($action, Constraint $constraint)
    {
        if ($action instanceof ProductSetValueActionInterface) {
            $this->validateSetValue($action, $constraint);
        } elseif ($action instanceof ProductCopyValueActionInterface) {
            $this->validateCopyValue($action, $constraint);
        } else {
            throw new \LogicException(sprintf('Action of type "%s" can not be validated.', gettype($action)));
        }
    }

    /**
     * @param ProductSetValueActionInterface $action
     * @param Constraint                     $constraint
     */
    protected function validateSetValue(ProductSetValueActionInterface $action, Constraint $constraint)
    {
        try {
            $this->factory->setValue(
                [],
                $action->getField(),
                $action->getValue(),
                $action->getLocale(),
                $action->getScope()
            );
        } catch (\Exception $e) {
            $this->context->addViolation(
                $constraint->message,
                [ '%message%' => $e->getMessage() ]
            );
        }
    }

    /**
     * @param ProductCopyValueActionInterface $action
     * @param Constraint                      $constraint
     */
    protected function validateCopyValue(ProductCopyValueActionInterface $action, Constraint $constraint)
    {
        try {
            $this->factory->copyValue(
                [],
                $action->getFromField(),
                $action->getToField(),
                $action->getFromLocale(),
                $action->getToLocale(),
                $action->getFromScope(),
                $action->getToScope()
            );
        } catch (\Exception $e) {
            $this->context->addViolation(
                $constraint->message,
                [ '%message%' => $e->getMessage() ]
            );
        }
    }
}
