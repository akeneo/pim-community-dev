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

use Akeneo\Pim\Automation\RuleEngine\Component\Event\SkippedActionForSubjectEvent;
use Akeneo\Pim\Automation\RuleEngine\Component\Exception\NonApplicableActionException;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCopyActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyCopierInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Copier action applier
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class CopierActionApplier implements ActionApplierInterface
{
    /** @var PropertyCopierInterface */
    protected $propertyCopier;

    /** @var GetAttributes */
    private $getAttributes;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        PropertyCopierInterface $propertyCopier,
        GetAttributes $getAttributes,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->propertyCopier = $propertyCopier;
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
                $this->copyDataOnEntityWithValues($entityWithValues, $action);
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
    public function supports(ActionInterface $action)
    {
        return $action instanceof ProductCopyActionInterface;
    }

    /**
     * We do not apply the action if to_field is an attribute and:
     *  - attribute does not belong to the family
     *  - entity is variant (variant product or product model) and attribute is not on the entity's variation level
     */
    private function actionCanBeAppliedToEntity(
        EntityWithFamilyVariantInterface $entity,
        ProductCopyActionInterface $action
    ): void {
        $toField = $action->getToField();
        // TODO: RUL-170: remove "?? ''" in the next line
        $attribute = $this->getAttributes->forCode($toField ?? '');
        if (null === $attribute) {
            return;
        }

        $family = $entity->getFamily();
        if (null === $family) {
            return;
        }
        if (!$family->hasAttributeCode($attribute->code())) {
            throw new NonApplicableActionException(
                \sprintf('The "%s" attribute does not belong to this entity\'s family', $attribute->code())
            );
        }

        $familyVariant = $entity->getFamilyVariant();
        if (null !== $familyVariant &&
            $familyVariant->getLevelForAttributeCode($attribute->code()) !== $entity->getVariationLevel()) {
            throw new NonApplicableActionException(
                \sprintf(
                    'Cannot set the "%s" property to this entity as it is not in the attribute set',
                    $attribute->code()
                )
            );
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
