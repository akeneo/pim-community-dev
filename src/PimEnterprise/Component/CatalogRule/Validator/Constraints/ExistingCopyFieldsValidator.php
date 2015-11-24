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

use Pim\Component\Catalog\Updater\Copier\CopierRegistryInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductCopyActionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates that you can copy data from a field to an other field.
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ExistingCopyFieldsValidator extends ConstraintValidator
{
    /** @var CopierRegistryInterface */
    protected $copierRegistry;

    /**
     * @param CopierRegistryInterface $copierRegistry
     */
    public function __construct(CopierRegistryInterface $copierRegistry)
    {
        $this->copierRegistry = $copierRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($action, Constraint $constraint)
    {
        if (!($action instanceof ProductCopyValueActionInterface || $action instanceof ProductCopyActionInterface)) {
            throw new \LogicException(sprintf('Action of type "%s" can not be validated.', gettype($action)));
        }

        $copier = $this->copierRegistry->getCopier($action->getFromField(), $action->getToField());
        if (null === $copier) {
            $this->context->buildViolation(
                $constraint->message,
                ['%fromField%' => $action->getFromField(), '%toField%' => $action->getToField()]
            )->addViolation();
        }
    }
}
