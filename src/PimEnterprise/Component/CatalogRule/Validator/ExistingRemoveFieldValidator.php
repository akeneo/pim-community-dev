<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Validator;

use Pim\Component\Catalog\Updater\Remover\RemoverRegistryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates if you can remove items from a field.
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class ExistingRemoveFieldValidator extends ConstraintValidator
{
    /** @var RemoverRegistryInterface */
    protected $removerRegistry;

    /**
     * @param RemoverRegistryInterface $removerRegistry
     */
    public function __construct(RemoverRegistryInterface $removerRegistry)
    {
        $this->removerRegistry = $removerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($fieldName, Constraint $constraint)
    {
        if (null === $fieldName) {
            return;
        }

        $remover = $this->removerRegistry->getRemover($fieldName);

        if (null === $remover) {
            $this->context->buildViolation($constraint->message, ['%field%' => $fieldName])
                ->addViolation();
        }
    }
}
