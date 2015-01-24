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

use Pim\Bundle\CatalogBundle\Query\Filter\FilterRegistryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that a field exists or not.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ExistingFieldValidator extends ConstraintValidator
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
    public function validate($fieldName, Constraint $constraint)
    {
        if (null === $fieldName) {
            return;
        }

        $filter = $this->registry->getFilter($fieldName);

        if (null === $filter) {
            $this->context->addViolation($constraint->message, ['%field%' => $fieldName]);
        }
    }
}
