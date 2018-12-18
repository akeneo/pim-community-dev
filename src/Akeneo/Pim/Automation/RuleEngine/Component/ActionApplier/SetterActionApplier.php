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

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSetActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;

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

        if ('categories' === $field) {
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

        if (null === $entityWithFamilyVariant->getFamily()) {
            $this->setDataOnEntityWithValues($entityWithFamilyVariant, $action);

            return;
        }

        if (!$entityWithFamilyVariant->getFamily()->hasAttributeCode($field)) {
            return;
        }

        if (null === $entityWithFamilyVariant->getFamilyVariant()) {
            $this->setDataOnEntityWithValues($entityWithFamilyVariant, $action);

            return;
        }

        $level = $entityWithFamilyVariant->getFamilyVariant()->getLevelForAttributeCode($field);

        if ($entityWithFamilyVariant->getVariationLevel() === $level) {
            $this->setDataOnEntityWithValues($entityWithFamilyVariant, $action);
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
