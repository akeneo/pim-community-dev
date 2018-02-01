<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface;
use Pim\Component\Catalog\Manager\AttributeValuesResolverInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Set product values to "false" by default for every boolean attributes in the
 * product's family or the variant product corresponding attribute set.
 *
 * This workaround is due to the UI that does not manage null values for boolean
 * attributes, only false or true. It avoids to automatically submit boolean
 * attributes belonging to the product's family in a proposal, even if those
 * boolean attributes were not modified by the user.
 *
 * FIXME - PIM-6056: To remove when the UI will manage null values in boolean attributes
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AddBooleanValuesSubscriber implements EventSubscriberInterface
{
    /** @var AttributeValuesResolverInterface */
    private $valuesResolver;

    /** @var EntityWithValuesBuilderInterface */
    private $entityWithValuesBuilder;

    /**
     * @param AttributeValuesResolverInterface $valuesResolver
     * @param EntityWithValuesBuilderInterface $entityWithValuesBuilder
     */
    public function __construct(
        AttributeValuesResolverInterface $valuesResolver,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder
    ) {
        $this->valuesResolver = $valuesResolver;
        $this->entityWithValuesBuilder = $entityWithValuesBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'addBooleanToProduct',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function addBooleanToProduct(GenericEvent $event): void
    {
        $product = $event->getSubject();

        if (!$product instanceof ProductInterface || null !== $product->getId()) {
            return;
        }

        $attributes = $this->getAttributes($product);

        if (empty($attributes)) {
            return;
        }

        foreach ($attributes as $attribute) {
            if (AttributeTypes::BOOLEAN === $attribute->getType()) {
                $requiredValues = $this->valuesResolver->resolveEligibleValues([$attribute]);

                foreach ($requiredValues as $value) {
                    $originalValue = $product->getValue($attribute->getCode(), $value['locale'], $value['scope']);

                    if (null === $originalValue || null === $originalValue->getData()) {
                        $this->entityWithValuesBuilder->addOrReplaceValue(
                            $product,
                            $attribute,
                            $value['locale'],
                            $value['scope'],
                            false
                        );
                    }
                }
            }
        }
    }

    /**
     * @param ProductInterface $product
     *
     * @return AttributeInterface[]
     */
    private function getAttributes(ProductInterface $product): array
    {
        if (!$product instanceof VariantProductInterface) {
            $family = $product->getFamily();
            if (null === $family) {
                return [];
            }

            return $family->getAttributes()->toArray();
        }

        $level = $product->getVariationLevel();
        $attributeSet = $product->getFamilyVariant()->getVariantAttributeSet($level);

        return $attributeSet->getAttributes()->toArray();
    }
}
