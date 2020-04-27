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

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductAddActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyAdderInterface;

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

    public function __construct(PropertyAdderInterface $propertyAdder, GetAttributes $getAttributes)
    {
        $this->propertyAdder = $propertyAdder;
        $this->getAttributes = $getAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(ActionInterface $action, array $entitiesWithValues = []): void
    {
        foreach ($entitiesWithValues as $entityWithValues) {
            if ($this->actionCanBeAppliedToEntity($entityWithValues, $action)) {
                $this->addDataOnEntityWithValues($entityWithValues, $action);
            }
        }
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
     *  - attribute does not belong to the family
     *  - entity is variant (variant product or product model) and attribute is not on the entity's variation level
     */
    private function actionCanBeAppliedToEntity(
        EntityWithFamilyVariantInterface $entity,
        ProductAddActionInterface $action
    ): bool {
        $field = $action->getField();
        // TODO: RUL-170: remove "?? ''" in the next line
        $attribute = $this->getAttributes->forCode($field ?? '');
        if (null === $attribute) {
            return true;
        }

        $family = $entity->getFamily();
        if (null === $family) {
            return true;
        }
        if (!$family->hasAttributeCode($attribute->code())) {
            return false;
        }

        $familyVariant = $entity->getFamilyVariant();
        if (null !== $familyVariant &&
            $familyVariant->getLevelForAttributeCode($attribute->code()) !== $entity->getVariationLevel()) {
            return false;
        }

        return true;
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
