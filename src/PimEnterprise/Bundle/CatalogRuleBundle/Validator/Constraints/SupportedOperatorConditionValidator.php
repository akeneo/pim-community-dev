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

use Pim\Bundle\CatalogBundle\Doctrine\Query\QueryFilterRegistryInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that an operator is supported for a field or an attribute in a condition.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class SupportedOperatorConditionValidator extends ConstraintValidator
{
    /** @var QueryFilterRegistryInterface */
    protected $registry;

    /**
     * @param QueryFilterRegistryInterface $registry
     */
    public function __construct(QueryFilterRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($productCondition, Constraint $constraint)
    {
        /** @var ProductConditionInterface $productCondition */
        $filter = $this->registry->getFilter($productCondition->getField());

        if (null !== $filter && false === $filter->supportsOperator($productCondition->getOperator())) {
            $this->context->addViolation(
                $constraint->message,
                [
                    '%field%' => $productCondition->getField(),
                    '%operator%' => $productCondition->getOperator(),
                ]
            );
        }
    }
}
