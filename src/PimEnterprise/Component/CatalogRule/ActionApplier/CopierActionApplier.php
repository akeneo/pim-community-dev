<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\ActionApplier;

use Akeneo\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Component\StorageUtils\Updater\PropertyCopierInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductCopyActionInterface;

/**
 * Copier action applier
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class CopierActionApplier implements ActionApplierInterface
{
    /** @var PropertyCopierInterface */
    protected $propertyCopier;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /**
     * @param PropertyCopierInterface      $propertyCopier
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        PropertyCopierInterface $propertyCopier,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->propertyCopier = $propertyCopier;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(ActionInterface $action, array $entitiesWithValues = [])
    {
        foreach ($entitiesWithValues as $entityWithValues) {
            if (!$entityWithValues instanceof EntityWithFamilyVariantInterface) {
                $this->copyDataOnEntityWithValues($entityWithValues, $action);
            } else {
                $this->copyDataOnEntityWithFamilyVariant($entityWithValues, $action);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ActionInterface $action)
    {
        return $action instanceof ProductCopyActionInterface;
    }

    /**
     * Currently, there is only copiers for values (meaning data linked to an
     * attribute). So if the fields passed to the copier are not attributes,
     * there is nothing to copy.
     *
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     * @param ProductCopyActionInterface       $action
     */
    private function copyDataOnEntityWithFamilyVariant(
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        ProductCopyActionInterface $action
    ): void {
        $toField = $action->getToField();

        $toAttribute = $this->attributeRepository->findOneByIdentifier($toField);
        if (null === $toAttribute) {
            return;
        }

        $toLevel = $this->getActionFieldLevel($toField, $entityWithFamilyVariant->getFamilyVariant());

        if ($entityWithFamilyVariant->getVariationLevel() === $toLevel) {
            $this->copyDataOnEntityWithValues($entityWithFamilyVariant, $action);
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
     * @param EntityWithValuesInterface  $entityWithValues
     * @param ProductCopyActionInterface $action
     */
    private function copyDataOnEntityWithValues(
        EntityWithValuesInterface $entityWithValues,
        ProductCopyActionInterface $action
    ): void {
        $this->propertyCopier->copyData(
            $entityWithValues,
            $entityWithValues,
            $action->getFromField(),
            $action->getToField(),
            $action->getOptions()
        );
    }
}
