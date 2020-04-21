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
            if ($this->actionCanBeAppliedToEntity($entityWithValues, $action)) {
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
     * We do not apply the action if:
     * - if field is categories and the new category codes do not include the categories of the entity's parent
     * - or field is an attribute and:
     *   - attribute does not belong to the family
     *   - or entity is variant (variant product or product model) and attribute is not on the entity's variation level
     */
    private function actionCanBeAppliedToEntity(
        EntityWithFamilyVariantInterface $entity,
        ProductSetActionInterface $action
    ): bool {
        $field = $action->getField();

        $attribute = $this->attributeRepository->findOneByIdentifier($field);
        if (null === $attribute) {
            if ('categories' === $field) {
                $newCategoryCodes = $action->getValue();
                $parent = $entity->getParent();

                return (null === $parent || empty(array_diff($parent->getCategoryCodes(), $newCategoryCodes)));
            }

            return true;
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
            '' === $action->getValue() ? null : $action->getValue(),
            $action->getOptions()
        );
    }
}
