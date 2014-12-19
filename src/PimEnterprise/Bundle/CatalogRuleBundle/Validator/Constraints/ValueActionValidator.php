<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
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
    public function validate($productSetAction, Constraint $constraint)
    {
        try {
            $this->factory->setValue(
                [],
                $productSetAction->getField(),
                $productSetAction->getValue(),
                $productSetAction->getLocale(),
                $productSetAction->getScope()
            );
        } catch (\Exception $e) {
            $this->context->addViolation(
                $constraint->message,
                [
                    '%message%' => $e->getMessage(),
                ]
            );
        }
    }
}
