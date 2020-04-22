<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier;

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductClearActionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyClearerInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ClearerActionApplier implements ActionApplierInterface
{
    /** @var PropertyClearerInterface */
    protected $propertyClearer;

    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(
        PropertyClearerInterface $propertyClearer,
        GetAttributes $getAttributes
    ) {
        $this->propertyClearer = $propertyClearer;
        $this->getAttributes = $getAttributes;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(ActionInterface $action): bool
    {
        return $action instanceof ProductClearActionInterface;
    }

    /**
     * {@inheritDoc}
     */
    public function applyAction(ActionInterface $action, array $entitiesWithValues = []): void
    {
        Assert::isInstanceOf($action, ProductClearActionInterface::class);

        foreach ($entitiesWithValues as $entityWithValues) {
            if ($this->actionCanBeAppliedToEntity($entityWithValues, $action)) {
                $this->clearDataOnEntityWithValues($entityWithValues, $action);
            }
        }
    }

    /**
     * We do not apply the action if field is an attribute and:
     *  - entity is variant (variant product or product model) and attribute is not on the entity's variation level
     */
    private function actionCanBeAppliedToEntity(
        EntityWithFamilyVariantInterface $entity,
        ProductClearActionInterface $action
    ): bool {
        $field = $action->getField();
        $attribute = $this->getAttributes->forCode($field);
        if (null === $attribute) {
            return true;
        }

        $family = $entity->getFamily();
        if (null === $family || !$family->hasAttributeCode($attribute->code())) {
            return true;
        }

        $familyVariant = $entity->getFamilyVariant();
        if (null !== $familyVariant &&
            $familyVariant->getLevelForAttributeCode($attribute->code()) !== $entity->getVariationLevel()) {
            return false;
        }

        return true;
    }

    private function clearDataOnEntityWithValues(
        EntityWithValuesInterface $entityWithValues,
        ProductClearActionInterface $action
    ): void {
        $this->propertyClearer->clear(
            $entityWithValues,
            $action->getField(),
            ['locale' => $action->getLocale(), 'scope'  => $action->getScope()]
        );
    }
}
