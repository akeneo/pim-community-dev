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

use Pim\Bundle\CatalogBundle\Query\Filter\FilterRegistryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that an operator is supported for a field or an attribute in a condition.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class SupportedOperatorConditionValidator extends ConstraintValidator
{
    /** @var FilterRegistryInterface */
    protected $registry;

    /**
     * @param FilterRegistryInterface $registry
     */
    public function __construct(FilterRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($productCondition, Constraint $constraint)
    {
        $filter = $this->registry->getFilter($productCondition->getField());
        $operator = $productCondition->getOperator();

        if (null !== $filter && !empty($operator) && !$filter->supportsOperator($operator)) {
            $this->context->addViolation(
                $constraint->message,
                [
                    '%field%'    => $productCondition->getField(),
                    '%operator%' => $productCondition->getOperator(),
                ]
            );
        }
    }
}
