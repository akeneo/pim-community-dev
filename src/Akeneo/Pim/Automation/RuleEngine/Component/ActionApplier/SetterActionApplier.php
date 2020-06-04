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

use Akeneo\Pim\Automation\RuleEngine\Component\Event\SkippedActionForSubjectEvent;
use Akeneo\Pim\Automation\RuleEngine\Component\Exception\NonApplicableActionException;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSetActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Setter action applier
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class SetterActionApplier implements ActionApplierInterface
{
    /** @var PropertySetterInterface */
    protected $propertySetter;

    /** @var GetAttributes */
    private $getAttributes;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        PropertySetterInterface $propertySetter,
        GetAttributes $getAttributes,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->propertySetter = $propertySetter;
        $this->getAttributes = $getAttributes;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(ActionInterface $action, array $entitiesWithValues = []): array
    {
        foreach ($entitiesWithValues as $index => $entityWithValues) {
            try {
                $this->actionCanBeAppliedToEntity($entityWithValues, $action);
                $this->setDataOnEntityWithValues($entityWithValues, $action);
            } catch (NonApplicableActionException $e) {
                unset($entitiesWithValues[$index]);
                $this->eventDispatcher->dispatch(
                    new SkippedActionForSubjectEvent($action, $entityWithValues, $e->getMessage())
                );
            }
        }

        return $entitiesWithValues;
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
    ): void {
        $field = $action->getField();

        // TODO: Do we really need this check ??
        if ('categories' === $field) {
            $newCategoryCodes = $action->getValue();
            $parent = $entity->getParent();

            if (null !== $parent && !empty(array_diff($parent->getCategoryCodes(), $newCategoryCodes))) {
                throw new NonApplicableActionException(
                    'The ancestor categories are missing'
                );
            }
        }

        $attribute = $this->getAttributes->forCode($field ?? '');
        if (null === $attribute) {
            return;
        }

        $family = $entity->getFamily();
        if (null === $family) {
            return;
        }
        if (!$family->hasAttributeCode($attribute->code())) {
            throw new NonApplicableActionException(
                \sprintf(
                    'The "%s" attribute does not belong to the family of this %s',
                    $attribute->code(),
                    $entity instanceof ProductModelInterface ? 'product model' : 'product'
                )
            );
        }

        $familyVariant = $entity->getFamilyVariant();
        if (null !== $familyVariant &&
            $familyVariant->getLevelForAttributeCode($attribute->code()) !== $entity->getVariationLevel()) {
            throw new NonApplicableActionException(
                \sprintf(
                    'The "%s" property cannot be updated for this %s, as it is not at the same variation level',
                    $attribute->code(),
                    $entity instanceof ProductModelInterface ? 'product model' : 'product'
                )
            );
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
            '' === $action->getValue() ? null : $action->getValue(),
            $action->getOptions()
        );
    }
}
