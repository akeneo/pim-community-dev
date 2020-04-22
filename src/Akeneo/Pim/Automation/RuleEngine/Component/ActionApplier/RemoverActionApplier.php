<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier;

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductRemoveActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyRemoverInterface;

/**
 * Remove action interface used in product rules.
 * A remove action value is used to remove a product property.
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class RemoverActionApplier implements ActionApplierInterface
{
    /** @var PropertyRemoverInterface */
    private $propertyRemover;

    /** @var GetAttributes */
    private $getAttributes;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    public function __construct(
        PropertyRemoverInterface $propertyRemover,
        GetAttributes $getAttributes,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->propertyRemover = $propertyRemover;
        $this->getAttributes = $getAttributes;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(ActionInterface $action, array $entitiesWithValues = []): void
    {
        foreach ($entitiesWithValues as $entityWithValues) {
            if ($this->actionCanBeAppliedToEntity($entityWithValues, $action)) {
                $this->removeDataOnEntityWithValues($entityWithValues, $action);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ActionInterface $action): bool
    {
        return $action instanceof ProductRemoveActionInterface;
    }

    /**
     * We do not apply the action if field is an attribute and:
     *  - attribute does not belong to the family
     *  - entity is variant (variant product or product model) and attribute is not on the entity's variation level
     */
    private function actionCanBeAppliedToEntity(
        EntityWithFamilyVariantInterface $entity,
        ProductRemoveActionInterface $action
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
     * @param EntityWithValuesInterface    $entityWithValues
     * @param ProductRemoveActionInterface $action
     */
    private function removeDataOnEntityWithValues(
        EntityWithValuesInterface $entityWithValues,
        ProductRemoveActionInterface $action
    ): void {
        $this->propertyRemover->removeData(
            $entityWithValues,
            $action->getField(),
            $this->getImpactedItems($action),
            $action->getOptions()
        );
    }

    /**
     * Get all items impacted by the action.
     * Practically, add children categories codes if "field" = "categories" and "include_children" option is true
     *
     * @param ProductRemoveActionInterface $action
     *
     * @return array
     */
    private function getImpactedItems(ProductRemoveActionInterface $action): array
    {
        $items = $action->getItems();
        if (!is_array($items)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $action->getField(),
                __CLASS__,
                $items
            );
        }

        $options = $action->getOptions();

        if (true === ($options['include_children'] ?? false)) {
            $categories = $this->categoryRepository->getCategoriesByCodes($items);
            foreach ($categories as $category) {
                $items = array_merge($items, $this->categoryRepository->getAllChildrenCodes($category));
            }
        }

        return array_unique($items);
    }
}
