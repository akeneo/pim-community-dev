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

use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Calculate\GetOperandValue;
use Akeneo\Pim\Automation\RuleEngine\Component\Exception\NonApplicableActionException;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\Operand;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\Operation;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCalculateActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductTarget;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

class CalculateActionApplier implements ActionApplierInterface
{
    /** @var GetAttributes */
    private $getAttributes;

    /** @var GetOperandValue */
    private $getOperandValue;

    /** @var NormalizerInterface */
    private $priceNormalizer;

    /** @var PropertySetterInterface */
    private $propertySetter;

    public function __construct(
        GetAttributes $getAttributes,
        GetOperandValue $getOperandValue,
        NormalizerInterface $priceNormalizer,
        PropertySetterInterface $propertySetter
    ) {
        $this->getAttributes = $getAttributes;
        $this->getOperandValue = $getOperandValue;
        $this->priceNormalizer = $priceNormalizer;
        $this->propertySetter = $propertySetter;
    }

    public function applyAction(ActionInterface $action, array $items = [])
    {
        Assert::isInstanceOf($action, ProductCalculateActionInterface::class);
        foreach ($items as $item) {
            if ($this->actionCanBeAppliedToEntity($item, $action)) {
                try {
                    $result = $this->calculateDataForEntity($item, $action);
                    $data = $this->getStandardData($item, $action->getDestination(), $result);
                } catch (NonApplicableActionException $e) {
                    // TODO RUL-90 throw exception when the runner will be executed in a job.
                    continue;
                }

                $this->propertySetter->setData(
                    $item,
                    $action->getDestination()->getField(),
                    $data,
                    [
                        'scope' => $action->getDestination()->getScope(),
                        'locale' => $action->getDestination()->getLocale(),
                    ]
                );
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
        $destination = $this->getAttributes->forCode($action->getDestination()->getField());
        Assert::isInstanceOf($destination, Attribute::class);

        $family = $entity->getFamily();
        if (null === $family || !$family->hasAttributeCode($destination->code())) {
            return false;
        }

        $familyVariant = $entity->getFamilyVariant();
        if (null !== $familyVariant && $familyVariant->getLevelForAttributeCode($destination->code()) !== $entity->getVariationLevel()) {
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
            case Operation::SUBTRACT:
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

        $data = $this->getOperandValue->fromEntity($entity, $operand);
        if (null !== $data) {
            return $data;
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

    private function getStandardData(EntityWithValuesInterface $entity, ProductTarget $destination, float $data)
    {
        $attribute = $this->getAttributes->forCode($destination->getField());
        $formattedData = null;

        switch ($attribute->type()) {
            case AttributeTypes::NUMBER:
                $formattedData = $data;
                break;
            case AttributeTypes::METRIC:
                $unit = $destination->getUnit() ?? $attribute->defaultMetricUnit();
                $formattedData = [
                    'amount' => $data,
                    'unit' => $unit,
                ];
                break;
            case AttributeTypes::PRICE_COLLECTION:
                $formattedData = $this->getPriceCollectionData($entity, $destination, $data);
                break;
            default:
                throw new \InvalidArgumentException('Unsupported destination type');
        }

        return $formattedData;
    }

    /**
     * Gets the new price collection value in standard format
     * Replaces prices with same currency from the former value
     */
    private function getPriceCollectionData(
        EntityWithValuesInterface $entity,
        ProductTarget $destination,
        float $amount
    ): array {
        Assert::string($destination->getCurrency());
        $standardizedPrices = [
            [
                'amount' => $amount,
                'currency' => $destination->getCurrency(),
            ]
        ];

        $previousValue = $entity->getValue(
            $destination->getField(),
            $destination->getLocale(),
            $destination->getScope()
        );
        if (null === $previousValue) {
            return $standardizedPrices;
        }

        foreach ($previousValue->getData() as $previousPrice) {
            if ($previousPrice->getCurrency() !== $destination->getCurrency()) {
                $standardizedPrices[] = $this->priceNormalizer->normalize($previousPrice, 'standard');
            }
        }

        return $standardizedPrices;
    }
}
