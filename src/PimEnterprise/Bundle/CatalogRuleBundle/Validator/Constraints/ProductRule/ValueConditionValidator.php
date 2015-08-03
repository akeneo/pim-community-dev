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

use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates if the field supports the given data
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ValueConditionValidator extends ConstraintValidator
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $factory;

    /**
     * @param ProductQueryBuilderFactoryInterface $factory
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $factory
    ) {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($productCondition, Constraint $constraint)
    {
        try {
            $this->factory->create()->addFilter(
                $productCondition->getField(),
                $productCondition->getOperator(),
                $productCondition->getValue(),
                [
                    'locale' => $productCondition->getLocale(),
                    'scope'  => $productCondition->getScope()
                ]
            );
        } catch (\Exception $e) {
            $this->context->buildViolation(
                $constraint->message,
                [
                    '%message%' => $e->getMessage(),
                ]
            )->addViolation();
        }
    }
}
