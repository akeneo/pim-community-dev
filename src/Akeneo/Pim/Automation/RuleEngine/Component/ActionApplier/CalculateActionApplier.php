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
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;

class CalculateActionApplier implements ActionApplierInterface
{
    /** @var PropertySetterInterface */
    private $propertySetter;

    public function __construct(PropertySetterInterface $propertySetter)
    {
        $this->propertySetter = $propertySetter;
    }

    public function applyAction(ActionInterface $action, array $items = [])
    {
        if (!$this->supports($action)) {
            throw new \LogicException(
                sprintf('Action must be an instance of %s.', ProductCalculateActionInterface::class)
            );
        }

        foreach ($items as $item) {
            if ($this->actionCanBeAppliedToEntity($item, $action)) {
                try {
                    $result = $this->calculateDataForEntity($item, $action);
                } catch (NonApplicableActionException $e) {
                    // TODO RUL-90 throw exception when the runner will be executed in a job.
                    return;
                }

                // TODO RUL-59 / RUL-60: format data for metric and price collection values
                $destination = $action->getDestination();
                $this->propertySetter->setData(
                    $item,
                    $destination->getField(),
                    $result,
                    [
                        'scope' => $destination->getScope(),
                        'locale' => $destination->getLocale(),
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
     *  - destination is not part of the family
     *  - entity is variant (variant product or product model) and destination is not on the entity's variation level
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
            $secondOperand = $this->getOperandValue($entity, $operation->getOperand());
            if (null === $value || null === $secondOperand) {
                // TODO: better error message
                throw new NonApplicableActionException('Cannot apply operation: null argument');
            }
            $value = $this->applyOperation($operation->getOperator(), $value, $secondOperand);
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

    private function getOperandValue(EntityWithValuesInterface $entity, Operand $operand): ?float
    {
        if (null !== $operand->getConstantValue()) {
            return $operand->getConstantValue();
        }

        $value = $entity->getValue($operand->getAttributeCode(), $operand->getLocaleCode(), $operand->getChannelCode());
        // TODO RUL-59 / RUL-60: get value from metric and price collection values
        if ($value instanceof ScalarValue && is_numeric($value->getData())) {
            return (float) $value->getData();
        }

        return null;
    }
}
