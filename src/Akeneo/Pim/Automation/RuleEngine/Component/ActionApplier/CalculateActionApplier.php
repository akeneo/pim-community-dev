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
use Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Calculate\UpdateNumericValue;
use Akeneo\Pim\Automation\RuleEngine\Component\Exception\NonApplicableActionException;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\Operand;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\Operation;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCalculateActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Webmozart\Assert\Assert;

class CalculateActionApplier implements ActionApplierInterface
{
    /** @var GetOperandValue */
    private $getOperandValue;

    /** @var UpdateNumericValue */
    private $updateValue;

    public function __construct(GetOperandValue $getOperandValue, UpdateNumericValue $updateValue)
    {
        $this->getOperandValue = $getOperandValue;
        $this->updateValue = $updateValue;
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
                    continue;
                }

                $this->updateValue->forEntity($item, $action->getDestination(), $result);
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
}
