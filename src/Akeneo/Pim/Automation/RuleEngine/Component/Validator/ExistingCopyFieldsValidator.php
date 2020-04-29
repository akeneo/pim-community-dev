<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\CopyAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCopyActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierRegistryInterface;
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
        if (!$action instanceof CopyAction) {
            throw new \LogicException(sprintf('Action of type "%s" can not be validated.', gettype($action)));
        }
        if (!is_string($action->fromField) || !is_string($action->toField)) {
            return;
        }

        $copier = $this->copierRegistry->getCopier($action->fromField, $action->toField);
        if (null === $copier) {
            $this->context->buildViolation(
                $constraint->message,
                ['%fromField%' => $action->fromField, '%toField%' => $action->toField]
            )->addViolation();
        }
    }
}
