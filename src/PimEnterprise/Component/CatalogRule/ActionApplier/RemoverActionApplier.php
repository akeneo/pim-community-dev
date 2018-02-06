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

namespace PimEnterprise\Component\CatalogRule\ActionApplier;

use Akeneo\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Component\StorageUtils\Updater\PropertyRemoverInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductRemoveActionInterface;

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

    /**
     * @param PropertyRemoverInterface     $propertyRemover
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        PropertyRemoverInterface $propertyRemover,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->propertyRemover = $propertyRemover;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(ActionInterface $action, array $entitiesWithValues = []): void
    {
        foreach ($entitiesWithValues as $entityWithValues) {
            if (!$entityWithValues instanceof EntityWithFamilyVariantInterface) {
                $this->removeDataOnEntityWithValues($entityWithValues, $action);
            } else {
                $this->removeDataOnEntityWithFamilyVariant($entityWithValues, $action);
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

        $level = $this->getActionFieldLevel($field, $entityWithFamilyVariant->getFamilyVariant());

        if ($entityWithFamilyVariant->getVariationLevel() === $level) {
            $this->removeDataOnEntityWithValues($entityWithFamilyVariant, $action);
        }
    }

    /**
     * @param string                 $actionField
     * @param FamilyVariantInterface $familyVariant
     *
     * @return int
     */
    private function getActionFieldLevel(
        string $actionField,
        FamilyVariantInterface $familyVariant
    ): int {
        $level = 0;
        $attributeSets = $familyVariant->getVariantAttributeSets();

        foreach ($attributeSets as $attributeSet) {
            $hasAttribute = false;

            foreach ($attributeSet->getAttributes() as $attribute) {
                if ($attribute->getCode() === $actionField) {
                    $hasAttribute = true;
                    break;
                }
            }

            if ($hasAttribute) {
                $level = $attributeSet->getLevel();
                break;
            }
        }

        return $level;
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
            $action->getItems(),
            $action->getOptions()
        );
    }
}
