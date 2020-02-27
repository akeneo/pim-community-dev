<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierRegistry;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConcatenateActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSource;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ExistingConcatenateFields;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterRegistryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ExistingConcatenateFieldsValidator extends ConstraintValidator
{
    const VALID_TARGET_TYPES = [
        AttributeTypes::TEXT,
        AttributeTypes::TEXTAREA,
    ];

    /** @var ValueStringifierRegistry */
    private $valueStringifierRegistry;

    /** @var SetterRegistryInterface */
    private $setterRegistry;

    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(
        ValueStringifierRegistry $valueStringifierRegistry,
        SetterRegistryInterface $setterRegistry,
        GetAttributes $getAttributes
    ) {
        $this->valueStringifierRegistry = $valueStringifierRegistry;
        $this->setterRegistry = $setterRegistry;
        $this->getAttributes = $getAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($action, Constraint $constraint)
    {
        Assert::isInstanceOf($action, ProductConcatenateActionInterface::class, sprintf(
            'Action of type "%s" cannot be validated.',
            gettype($action)
        ));
        Assert::isInstanceOf($constraint, ExistingConcatenateFields::class, sprintf(
            'Constraint must be an instance of "%s".',
            ExistingConcatenateFields::class
        ));

        /** @var ProductSource $source */
        foreach ($action->getSourceCollection() as $source) {
            $attribute = $this->getAttributes->forCode($source->getField());
            if (null === $attribute) {
                $this->context->buildViolation($constraint->messageAttributeNotFound, ['%field%' => $source->getField()])
                    ->addViolation();
            } elseif (null === $this->valueStringifierRegistry->getStringifier($attribute->type())) {
                $this->context->buildViolation($constraint->messageErrorSource, ['%field%' => $source->getField()])
                    ->addViolation();
            }
        }

        $targetField = $action->getTarget()->getField();
        $targetAttribute = $this->getAttributes->forCode($targetField);
        if (null === $targetAttribute) {
            $this->context->buildViolation($constraint->messageAttributeNotFound, ['%field%' => $targetField])
                ->addViolation();

            return;
        }

        if (!in_array($targetAttribute->type(), static::VALID_TARGET_TYPES)) {
            $this->addViolationForTargetField($targetField, $constraint);

            return;
        }

        $setter = $this->setterRegistry->getSetter($targetField);
        if (null === $setter) {
            $this->addViolationForTargetField($targetField, $constraint);
        }
    }

    private function addViolationForTargetField(string $targetField, Constraint $constraint): void
    {
        $this->context->buildViolation($constraint->messageErrorTarget, ['%field%' => $targetField])
            ->addViolation();
    }
}
