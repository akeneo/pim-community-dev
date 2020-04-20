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
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
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

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /**
     * @param PropertyRemoverInterface     $propertyRemover
     * @param AttributeRepositoryInterface $attributeRepository
     * @param CategoryRepositoryInterface  $categoryRepository
     */
    public function __construct(
        PropertyRemoverInterface $propertyRemover,
        AttributeRepositoryInterface $attributeRepository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->propertyRemover = $propertyRemover;
        $this->attributeRepository = $attributeRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(ActionInterface $action, array $entitiesWithValues = []): void
    {
        foreach ($entitiesWithValues as $entityWithValues) {
            if ($entityWithValues instanceof EntityWithFamilyVariantInterface) {
                $this->removeDataOnEntityWithFamilyVariant($entityWithValues, $action);
            } else {
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
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     * @param ProductRemoveActionInterface     $action
     */
    private function removeDataOnEntityWithFamilyVariant(
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        ProductRemoveActionInterface $action
    ): void {
        $field = $action->getField();

        $attribute = $this->attributeRepository->findOneByIdentifier($field);
        if (null === $attribute) {
            $this->removeDataOnEntityWithValues($entityWithFamilyVariant, $action);

            return;
        }

        // We set again the field to have the correct case of the code.
        $field = $attribute->getCode();
        if (null === $entityWithFamilyVariant->getFamily()) {
            $this->removeDataOnEntityWithValues($entityWithFamilyVariant, $action);

            return;
        }

        if (!$entityWithFamilyVariant->getFamily()->hasAttributeCode($field)) {
            return;
        }

        if (null === $entityWithFamilyVariant->getFamilyVariant()) {
            $this->removeDataOnEntityWithValues($entityWithFamilyVariant, $action);

            return;
        }

        $level = $entityWithFamilyVariant->getFamilyVariant()->getLevelForAttributeCode($field);

        if ($entityWithFamilyVariant->getVariationLevel() === $level) {
            $this->removeDataOnEntityWithValues($entityWithFamilyVariant, $action);
        }
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
