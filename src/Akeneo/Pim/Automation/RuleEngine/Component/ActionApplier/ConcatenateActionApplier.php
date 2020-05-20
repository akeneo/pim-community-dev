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

namespace Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier;

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate\ValueStringifierRegistry;
use Akeneo\Pim\Automation\RuleEngine\Component\Event\SkippedActionForSubjectEvent;
use Akeneo\Pim\Automation\RuleEngine\Component\Exception\NonApplicableActionException;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConcatenateActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSource;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ConcatenateActionApplier implements ActionApplierInterface
{
    /**
     * Temporary separator. Should be customizable in RUL-26.
     */
    const SEPARATOR = ' ';

    /** @var PropertySetterInterface */
    private $propertySetter;

    /** @var ValueStringifierRegistry */
    private $valueStringifierRegistry;

    /** @var GetAttributes */
    private $getAttributes;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        PropertySetterInterface $propertySetter,
        ValueStringifierRegistry $valueStringifierRegistry,
        GetAttributes $getAttributes,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->propertySetter = $propertySetter;
        $this->valueStringifierRegistry = $valueStringifierRegistry;
        $this->getAttributes = $getAttributes;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function supports(ActionInterface $action): bool
    {
        return $action instanceof ProductConcatenateActionInterface;
    }

    public function applyAction(ActionInterface $action, array $entitiesWithValues = []): array
    {
        if (!$this->supports($action)) {
            throw new \LogicException(
                sprintf('Action must be an instance of %s.', ProductConcatenateActionInterface::class)
            );
        }

        foreach ($entitiesWithValues as $index => $entityWithValues) {
            try {
                $this->actionCanBeAppliedToEntity($entityWithValues, $action);
                $this->concatenateDataOnEntityWithValues($entityWithValues, $action);
            } catch (NonApplicableActionException $e) {
                unset($entitiesWithValues[$index]);
                $this->eventDispatcher->dispatch(
                    new SkippedActionForSubjectEvent($action, $entityWithValues, $e->getMessage())
                );
            }
        }

        return $entitiesWithValues;
    }

    /**
     * We do not apply the action if:
     *  - target attribute does not belong to the family
     *  - entity is variant (variant product or product model) and attribute is not on the entity's variation level
     */
    private function actionCanBeAppliedToEntity(
        EntityWithFamilyVariantInterface $entity,
        ProductConcatenateActionInterface $action
    ): void {
        $field = $action->getTarget()->getField();
        $attribute = $this->getAttributes->forCode($field);
        Assert::isInstanceOf($attribute, Attribute::class);

        $family = $entity->getFamily();
        if (null === $family) {
            return;
        }
        if (!$family->hasAttributeCode($attribute->code())) {
            throw new NonApplicableActionException(
                \sprintf('The "%s" attribute does not belong to the entity\'s family', $attribute->code())
            );
        }

        $familyVariant = $entity->getFamilyVariant();
        if (null !== $familyVariant &&
            $familyVariant->getLevelForAttributeCode($attribute->code()) !== $entity->getVariationLevel()) {
            throw new NonApplicableActionException(
                \sprintf(
                    'Cannot set the "%s" property to this entity as it is not in the attribute set',
                    $attribute->code()
                )
            );
        }
    }

    private function concatenateDataOnEntityWithValues(
        EntityWithFamilyVariantInterface $entity,
        ProductConcatenateActionInterface $action
    ): void {
        $stringValues = [];

        /** @var ProductSource $source */
        foreach ($action->getSourceCollection() as $source) {
            $field = $source->getField();
            $attribute = $this->getAttributes->forCode($field);
            Assert::isInstanceOf(
                $attribute,
                Attribute::class,
                sprintf('Attribute with code "%s" was not found', $field)
            );
            $attributeCode = $attribute->code();

            $value = $entity->getValue($attributeCode, $source->getLocale(), $source->getScope());
            if (null === $value) {
                throw new NonApplicableActionException(
                    sprintf('The value for the "%s" attribute is empty for the entity.', $attributeCode)
                );
            }

            $stringifier = $this->getStringifier($attributeCode, $attribute->type());
            $stringValue = $stringifier->stringify(
                $value,
                array_merge(
                    $source->getOptions(),
                    ['target_attribute_code' => $action->getTarget()->getField()]
                )
            );
            if ('' === $stringValue) {
                throw new NonApplicableActionException(
                    sprintf('The value for the "%s" attribute is empty for the entity.', $attributeCode)
                );
            }

            $stringValues[] = $stringValue;
        }

        $data = implode(static::SEPARATOR, $stringValues);
        $target = $action->getTarget();

        $this->propertySetter->setData(
            $entity,
            $target->getField(),
            $data,
            ['locale' => $target->getLocale(), 'scope' => $target->getScope()]
        );
    }

    private function getStringifier(string $attributeCode, string $attributeType): ValueStringifierInterface
    {
        $stringifier = $this->valueStringifierRegistry->getStringifier($attributeType);
        if (null === $stringifier) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Stringifier was not found for the "%s" attribute code of type "%s".',
                    $attributeCode,
                    $attributeType
                )
            );
        }

        return $stringifier;
    }
}
