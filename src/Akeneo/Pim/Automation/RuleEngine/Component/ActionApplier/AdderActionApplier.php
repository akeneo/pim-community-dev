<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier;

use Akeneo\Pim\Automation\RuleEngine\Component\Event\SkippedActionForSubjectEvent;
use Akeneo\Pim\Automation\RuleEngine\Component\Exception\NonApplicableActionException;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductAddActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyAdderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Adder action applier
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AdderActionApplier implements ActionApplierInterface
{
    /** @var PropertyAdderInterface */
    private $propertyAdder;

    /** @var GetAttributes */
    private $getAttributes;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        PropertyAdderInterface $propertyAdder,
        GetAttributes $getAttributes,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->propertyAdder = $propertyAdder;
        $this->getAttributes = $getAttributes;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(ActionInterface $action, array $entitiesWithValues = []): array
    {
        foreach ($entitiesWithValues as $index => $entityWithValues) {
            try {
                $this->actionCanBeAppliedToEntity($entityWithValues, $action);
                $this->addDataOnEntityWithValues($entityWithValues, $action);
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
     * {@inheritdoc}
     */
    public function supports(ActionInterface $action): bool
    {
        return $action instanceof ProductAddActionInterface;
    }

    /**
     * We do not apply the action if field is an attribute and:
     *  - field is "groups" and entity is a product model
     *  - attribute does not belong to the family
     *  - entity is variant (variant product or product model) and attribute is not on the entity's variation level
     */
    private function actionCanBeAppliedToEntity(
        EntityWithFamilyVariantInterface $entity,
        ProductAddActionInterface $action
    ): void {
        $field = $action->getField();
        if (!is_string($field)) {
            return;
        }
        if ('groups' === $field && $entity instanceof ProductModelInterface) {
            throw new NonApplicableActionException(
                'The "groups" property cannot be added to a product model'
            );
        }

        $attribute = $this->getAttributes->forCode($field);
        if (null === $attribute) {
            return;
        }

        $family = $entity->getFamily();
        if (null === $family) {
            return;
        }
        if (!$family->hasAttributeCode($attribute->code())) {
            throw new NonApplicableActionException(
                \sprintf(
                    'The "%s" attribute does not belong to the family of this %s',
                    $attribute->code(),
                    $entity instanceof ProductModelInterface ? 'product model' : 'product'
                )
            );
        }

        $familyVariant = $entity->getFamilyVariant();
        if (null !== $familyVariant &&
            $familyVariant->getLevelForAttributeCode($attribute->code()) !== $entity->getVariationLevel()) {
            throw new NonApplicableActionException(
                \sprintf(
                    'The "%s" property cannot be updated for this %s, as it is not at the same variation level',
                    $attribute->code(),
                    $entity instanceof ProductModelInterface ? 'product model' : 'product'
                )
            );
        }
    }

    /**
     * @param EntityWithValuesInterface $entityWithValues
     * @param ProductAddActionInterface $action
     */
    private function addDataOnEntityWithValues(
        EntityWithValuesInterface $entityWithValues,
        ProductAddActionInterface $action
    ): void {
        $this->propertyAdder->addData(
            $entityWithValues,
            $action->getField(),
            $action->getItems(),
            $action->getOptions()
        );
    }
}
