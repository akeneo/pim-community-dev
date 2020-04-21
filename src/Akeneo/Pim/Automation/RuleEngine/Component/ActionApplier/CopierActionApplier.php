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
            if ($this->actionCanBeAppliedToEntity($entityWithValues, $action)) {
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
     * We do not apply the action if field is an attribute and:
     *  - field is not an attribute (currently there are only attribute copiers)
     *  - attribute does not belong to the family
     *  - entity is variant (variant product or product model) and attribute is not on the entity's variation level
     */
    private function actionCanBeAppliedToEntity(
        EntityWithFamilyVariantInterface $entity,
        ProductCopyActionInterface $action
    ): bool {
        $toField = $action->getToField();
        $attribute = $this->attributeRepository->findOneByIdentifier($toField);
        if (null === $attribute) {
            return false;
        }

        $family = $entity->getFamily();
        if (null === $family) {
            return true;
        }
        if (!$family->hasAttributeCode($attribute->getCode())) {
            return false;
        }

        $familyVariant = $entity->getFamilyVariant();
        if (null !== $familyVariant &&
            $familyVariant->getLevelForAttributeCode($attribute->getCode()) !== $entity->getVariationLevel()) {
            return false;
        }

        return true;
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
