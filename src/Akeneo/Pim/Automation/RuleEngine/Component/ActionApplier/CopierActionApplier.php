<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier;

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCopyActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyCopierInterface;

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
            if ($entityWithValues instanceof EntityWithFamilyVariantInterface) {
                $this->copyDataOnEntityWithFamilyVariant($entityWithValues, $action);
            } else {
                $this->copyDataOnEntityWithValues($entityWithValues, $action);
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
     * Currently, there are only copiers for values (meaning data linked to an
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

        // We set again the field to have the correct case of the code.
        $toField = $toAttribute->getCode();
        if (null === $entityWithFamilyVariant->getFamily()) {
            $this->copyDataOnEntityWithValues($entityWithFamilyVariant, $action);

            return;
        }

        if (!$entityWithFamilyVariant->getFamily()->hasAttributeCode($toField)) {
            return;
        }

        if (null === $entityWithFamilyVariant->getFamilyVariant()) {
            $this->copyDataOnEntityWithValues($entityWithFamilyVariant, $action);

            return;
        }

        $toLevel = $entityWithFamilyVariant->getFamilyVariant()->getLevelForAttributeCode($toField);

        if ($entityWithFamilyVariant->getVariationLevel() === $toLevel) {
            $this->copyDataOnEntityWithValues($entityWithFamilyVariant, $action);
        }
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
