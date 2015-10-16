<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Validator\Constraints;

use Pim\Component\Catalog\Updater\Setter\SetterRegistryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that you can add items to a field.
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ExistingSetFieldValidator extends ConstraintValidator
{
    /** @var SetterRegistryInterface */
    protected $setterRegistry;

    /**
     * @param SetterRegistryInterface $setterRegistry
     */
    public function __construct(SetterRegistryInterface $setterRegistry)
    {
        $this->setterRegistry = $setterRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($fieldName, Constraint $constraint)
    {
        if (null === $fieldName) {
            return;
        }

        $setter = $this->setterRegistry->getSetter($fieldName);

        if (null === $setter) {
            $this->context->buildViolation($constraint->message, ['%field%' => $fieldName])
                ->addViolation();
        }
    }
}
