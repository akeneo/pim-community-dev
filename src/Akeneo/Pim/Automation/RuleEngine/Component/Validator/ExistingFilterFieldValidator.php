<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that a field can be filtered or not.
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ExistingFilterFieldValidator extends ConstraintValidator
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
        $filter = $this->registry->getFilter($productCondition->getField(), $productCondition->getOperator());

        if (null === $filter) {
            $this->context
                ->buildViolation(
                    $constraint->message,
                    ['%field%' => $productCondition->getField(), '%operator%' => $productCondition->getOperator()]
                )
                ->addViolation();
        }
    }
}
