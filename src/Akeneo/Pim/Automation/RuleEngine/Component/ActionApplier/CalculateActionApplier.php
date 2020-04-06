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

use Akeneo\Pim\Automation\RuleEngine\Component\Exception\NonApplicableActionException;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\Operand;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\Operation;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCalculateActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductTarget;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyAdderInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Webmozart\Assert\Assert;

class CalculateActionApplier implements ActionApplierInterface
{
    /** @var PropertySetterInterface */
    private $propertySetter;

    /** @var PropertyAdderInterface */
    private $propertyAdder;

    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(
        PropertySetterInterface $propertySetter,
        PropertyAdderInterface $propertyAdder,
        GetAttributes $getAttributes
    ) {
        $this->propertySetter = $propertySetter;
        $this->propertyAdder = $propertyAdder;
        $this->getAttributes = $getAttributes;
    }

    public function applyAction(ActionInterface $action, array $items = [])
    {
        Assert::isInstanceOf($action, ProductCalculateActionInterface::class);
        foreach ($items as $item) {
            if ($this->actionCanBeAppliedToEntity($item, $action)) {
                try {
                    $result = $this->calculateDataForEntity($item, $action);
                } catch (NonApplicableActionException $e) {
                    // TODO RUL-90 throw exception when the runner will be executed in a job.
                    return;
                }

                $this->updateEntity($item, $action->getDestination(), $result);
            }
        }
    }

    public function supports(ActionInterface $action)
    {
        return $action instanceof ProductCalculateActionInterface;
    }

    /**
     * We do not apply the action if:
     *  - entity has no family
     *  - destination attribute does not belong to the family
     *  - entity is variant (variant product or product model) and destination attribute is not on the entity's variation level
     */
    private function actionCanBeAppliedToEntity(
        EntityWithFamilyVariantInterface $entity,
        ProductCalculateActionInterface $action
    ): bool {
        $destination = $action->getDestination()->getField();

        $family = $entity->getFamily();
        if (null === $family || !$family->hasAttributeCode($destination)) {
            return false;
        }

        $familyVariant = $entity->getFamilyVariant();
        if (null !== $familyVariant && $familyVariant->getLevelForAttributeCode($destination) !== $entity->getVariationLevel()) {
            return false;
        }

        return true;
    }

    private function calculateDataForEntity(EntityWithValuesInterface $entity, ProductCalculateActionInterface $action): float
    {
        $value = $this->getOperandValue($entity, $action->getSource());

        foreach ($action->getOperationList() as $operation) {
            $value = $this->applyOperation(
                $operation->getOperator(),
                $value,
                $this->getOperandValue($entity, $operation->getOperand())
            );
        }

        return $value;
    }

    private function applyOperation(string $operator, float $firstOperand, float $secondOperand): float
    {
        if (Operation::DIVIDE === $operator && 0.0 === $secondOperand) {
            throw new NonApplicableActionException('Cannot apply operation: division by zero');
        }

        switch ($operator) {
            case Operation::MULTIPLY:
                return $firstOperand * $secondOperand;
            case Operation::DIVIDE:
                return $firstOperand / $secondOperand;
            case Operation::ADD:
                return $firstOperand + $secondOperand;
            case Operation::SUBSCTRACT:
                return $firstOperand - $secondOperand;
            default:
                throw new \LogicException('Operator not supported');
        }
    }

    private function getOperandValue(EntityWithValuesInterface $entity, Operand $operand): float
    {
        if (null !== $operand->getConstantValue()) {
            return $operand->getConstantValue();
        }

        $value = $entity->getValue($operand->getAttributeCode(), $operand->getLocaleCode(), $operand->getChannelCode());
        // TODO RUL-59 : get value from metric values
        if ($value instanceof ScalarValue && is_numeric($value->getData())) {
            return (float) $value->getData();
        } elseif ($value instanceof PriceCollectionValueInterface) {
            Assert::notNull($operand->getCurrencyCode());
            $price = $value->getPrice($operand->getCurrencyCode());
            if (null !== $price) {
                return $price->getData();
            }
        }

        throw new NonApplicableActionException(
            sprintf(
                'The entity has no value for %s-%s-%s%s',
                $operand->getAttributeCode(),
                $operand->getChannelCode() ?: '<all_channels>',
                $operand->getLocaleCode() ?: '<all_locales>',
                $operand->getCurrencyCode() ? sprintf(' (%s)', $operand->getCurrencyCode()) : ''
            )
        );
    }

    private function updateEntity(EntityWithValuesInterface $entity, ProductTarget $destination, float $data): void
    {
        $targetAttribute = $this->getAttributes->forCode($destination->getField());
        Assert::isInstanceOf($targetAttribute, Attribute::class);

        if (AttributeTypes::PRICE_COLLECTION === $targetAttribute->type()) {
            Assert::string($destination->getCurrency());
            $this->propertyAdder->addData(
                $entity,
                $destination->getField(),
                [
                    [
                        'amount' => $data,
                        'currency' => $destination->getCurrency(),
                    ],
                ],
                [
                    'scope' => $destination->getScope(),
                    'locale' => $destination->getLocale(),
                ]
            );
        } elseif (AttributeTypes::NUMBER === $targetAttribute->type()) {
            $this->propertySetter->setData(
                $entity,
                $destination->getField(),
                $data,
                [
                    'scope' => $destination->getScope(),
                    'locale' => $destination->getLocale(),
                ]
            );
        } else {
            // TODO RUL-59: Use metric as destination
            throw new \InvalidArgumentException('Invalid destination attribute type');
        }
    }
}
