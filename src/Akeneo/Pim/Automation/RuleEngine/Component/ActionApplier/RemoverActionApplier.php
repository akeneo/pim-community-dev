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

namespace Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier;

use Akeneo\Pim\Automation\RuleEngine\Component\Event\SkippedActionForSubjectEvent;
use Akeneo\Pim\Automation\RuleEngine\Component\Exception\NonApplicableActionException;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductRemoveActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyRemoverInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

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

    /** @var GetAttributes */
    private $getAttributes;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        PropertyRemoverInterface $propertyRemover,
        GetAttributes $getAttributes,
        CategoryRepositoryInterface $categoryRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->propertyRemover = $propertyRemover;
        $this->getAttributes = $getAttributes;
        $this->categoryRepository = $categoryRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(ActionInterface $action, array $entitiesWithValues = []): array
    {
        Assert::implementsInterface($action, ProductRemoveActionInterface::class);
        $impactedItems = $this->getImpactedItems($action);
        foreach ($entitiesWithValues as $index => $entityWithValues) {
            try {
                $this->actionCanBeAppliedToEntity($entityWithValues, $action);
                $this->propertyRemover->removeData(
                    $entityWithValues,
                    $action->getField(),
                    $impactedItems,
                    $action->getOptions()
                );
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
        return $action instanceof ProductRemoveActionInterface;
    }

    /**
     * We do not apply the action if field is an attribute and:
     *  - field is "groups" and entity is a product model
     *  - entity is variant (variant product or product model) and attribute is not on the entity's variation level
     */
    private function actionCanBeAppliedToEntity(
        EntityWithFamilyVariantInterface $entity,
        ProductRemoveActionInterface $action
    ): void {
        $field = $action->getField();
        if (!is_string($field)) {
            return;
        }
        if ('groups' === $field && $entity instanceof ProductModelInterface) {
            throw new NonApplicableActionException(
                'The "groups" property cannot be removed from a product model'
            );
        }

        $attribute = $this->getAttributes->forCode($field);
        if (null === $attribute) {
            return;
        }

        $family = $entity->getFamily();
        if (null === $family || !$family->hasAttributeCode($attribute->code())) {
            return;
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
     * Get all items impacted by the action.
     * Practically, add children categories codes if "field" = "categories" and "include_children" option is true
     *
     * @param ProductRemoveActionInterface $action
     *
     * @return array
     */
    private function getImpactedItems(ProductRemoveActionInterface $action): array
    {
        $items = $action->getItems();
        if (!is_array($items)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $action->getField(),
                __CLASS__,
                $items
            );
        }

        $options = $action->getOptions();

        if (true === ($options['include_children'] ?? false)) {
            $categories = $this->categoryRepository->getCategoriesByCodes($items);
            foreach ($categories as $category) {
                $items = array_merge($items, $this->categoryRepository->getAllChildrenCodes($category));
            }
        }

        return array_unique($items);
    }
}
