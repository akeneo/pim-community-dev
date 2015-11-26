<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Validator;

use Pim\Component\Catalog\Updater\Adder\AdderRegistryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that you can add items to a field.
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ExistingAddFieldValidator extends ConstraintValidator
{
    /** @var AdderRegistryInterface */
    protected $adderRegistry;

    /**
     * @param AdderRegistryInterface $adderRegistry
     */
    public function __construct(AdderRegistryInterface $adderRegistry)
    {
        $this->adderRegistry = $adderRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($fieldName, Constraint $constraint)
    {
        if (null === $fieldName) {
            return;
        }

        $adder = $this->adderRegistry->getAdder($fieldName);

        if (null === $adder) {
            $this->context->buildViolation($constraint->message, ['%field%' => $fieldName])
                ->addViolation();
        }
    }
}
