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

namespace PimEnterprise\Component\CatalogRule\ActionApplier;

use Akeneo\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Normalizer\Standard\Product\PropertiesNormalizer;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductSetActionInterface;

/**
 * Setter action applier
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class SetterActionApplier implements ActionApplierInterface
{
    /** @var PropertySetterInterface */
    protected $propertySetter;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /**
     * @param PropertySetterInterface      $propertySetter
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->propertySetter = $propertySetter;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(ActionInterface $action, array $entitiesWithValues = []): void
    {
        foreach ($entitiesWithValues as $entityWithValues) {
            if ($entityWithValues instanceof EntityWithFamilyVariantInterface) {
                $this->setDataOnEntityWithFamilyVariant($entityWithValues, $action);
            } else {
                $this->setDataOnEntityWithValues($entityWithValues, $action);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ActionInterface $action): bool
    {
        return $action instanceof ProductSetActionInterface;
    }

    /**
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     * @param ProductSetActionInterface        $action
     */
    private function setDataOnEntityWithFamilyVariant(
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        ProductSetActionInterface $action
    ): void {
        $field = $action->getField();

        if (PropertiesNormalizer::FIELD_CATEGORIES === $field) {
            $newCategoryCodes = $action->getValue();
            $parent = $entityWithFamilyVariant->getParent();

            if (null === $parent || empty(array_diff($parent->getCategoryCodes(), $newCategoryCodes))) {
                $this->setDataOnEntityWithValues($entityWithFamilyVariant, $action);
            }

            return;
        }

        $attribute = $this->attributeRepository->findOneByIdentifier($field);
        if (null === $attribute) {
            $this->setDataOnEntityWithValues($entityWithFamilyVariant, $action);

            return;
        }

        if ($entityWithFamilyVariant->getFamily()->hasAttributeCode($field)) {
            $level = $entityWithFamilyVariant->getFamilyVariant()->getLevelForAttributeCode($field);

            if ($entityWithFamilyVariant->getVariationLevel() === $level) {
                $this->setDataOnEntityWithValues($entityWithFamilyVariant, $action);
            }
        }
    }

    /**
     * @param EntityWithValuesInterface $entityWithValues
     * @param ProductSetActionInterface $action
     */
    private function setDataOnEntityWithValues(
        EntityWithValuesInterface $entityWithValues,
        ProductSetActionInterface $action
    ): void {
        $this->propertySetter->setData(
            $entityWithValues,
            $action->getField(),
            $action->getValue(),
            $action->getOptions()
        );
    }
}
