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
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyAdderInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;

/**
 * Adder action applier
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AdderActionApplier implements ActionApplierInterface
{
    /** @var PropertyAdderInterface */
    private $propertyAdder;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /**
     * @param PropertyAdderInterface       $propertyAdder
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        PropertyAdderInterface $propertyAdder,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->propertyAdder = $propertyAdder;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(ActionInterface $action, array $entitiesWithValues = []): void
    {
        foreach ($entitiesWithValues as $entityWithValues) {
            if ($entityWithValues instanceof EntityWithFamilyVariantInterface) {
                $this->addDataOnEntityWithFamilyVariant($entityWithValues, $action);
            } else {
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
     * @param EntityWithFamilyVariantInterface $entityWithFamilyVariant
     * @param ProductAddActionInterface        $action
     */
    private function addDataOnEntityWithFamilyVariant(
        EntityWithFamilyVariantInterface $entityWithFamilyVariant,
        ProductAddActionInterface $action
    ): void {
        $field = $action->getField();

        $attribute = $this->attributeRepository->findOneByIdentifier($field);
        if (null === $attribute) {
            $this->addDataOnEntityWithValues($entityWithFamilyVariant, $action);

            return;
        }

        if (null === $entityWithFamilyVariant->getFamily()) {
            $this->addDataOnEntityWithValues($entityWithFamilyVariant, $action);

            return;
        }

        if (!$entityWithFamilyVariant->getFamily()->hasAttributeCode($field)) {
            return;
        }

        if (null === $entityWithFamilyVariant->getFamilyVariant()) {
            $this->addDataOnEntityWithValues($entityWithFamilyVariant, $action);

            return;
        }

        $level = $entityWithFamilyVariant->getFamilyVariant()->getLevelForAttributeCode($field);

        if ($entityWithFamilyVariant->getVariationLevel() === $level) {
            $this->addDataOnEntityWithValues($entityWithFamilyVariant, $action);
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
