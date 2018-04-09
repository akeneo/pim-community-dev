<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Factory\ValueFactory;
use Pim\Component\Catalog\Manager\AttributeValuesResolverInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * When a new product is created, and before it is saved.
 * Add a product value "false" for every boolean attributes of the product's family.
 * For a variant product, only the parent family variant attributes of the related level are taken.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddBooleanValuesToNewProductSubscriber implements EventSubscriberInterface
{
    /** @var AttributeValuesResolverInterface */
    private $valuesResolver;

    /** @var ValueFactory */
    private $productValueFactory;

    /**
     * @param AttributeValuesResolverInterface $valuesResolver
     * @param ValueFactory                     $productValueFactory
     */
    public function __construct(AttributeValuesResolverInterface $valuesResolver, ValueFactory $productValueFactory)
    {
        $this->valuesResolver = $valuesResolver;
        $this->productValueFactory = $productValueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [StorageEvents::PRE_SAVE => 'addBooleansDefaultValues'];
    }

    /**
     * @param GenericEvent $event
     */
    public function addBooleansDefaultValues(GenericEvent $event): void
    {
        $product = $event->getSubject();

        if (!$product instanceof ProductInterface || null !== $product->getId()) {
            return;
        }

        // TODO @merge Use $product->isVariant() to determine if the product is variant (since 2.2).
        $booleanAttributes = $product instanceof VariantProductInterface
            ? $this->getBooleanAttributesFromFamilyVariant($product)
            : $this->getBooleanAttributesFromFamily($product);

        foreach ($booleanAttributes as $attribute) {
            $eligibleValues = $this->valuesResolver->resolveEligibleValues([$attribute]);

            foreach ($eligibleValues as $valueData) {
                $value = $product->getValue($attribute->getCode(), $valueData['locale'], $valueData['scope']);

                if (null === $value) {
                    $value = $this->productValueFactory->create($attribute, $valueData['scope'], $valueData['locale'], false);
                    $product->addValue($value);
                }
            }
        }
    }

    /**
     * @param ProductInterface $product
     *
     * @return \Generator
     */
    private function getBooleanAttributesFromFamily(ProductInterface $product): \Generator
    {
        $family = $product->getFamily();
        $familyAttributes = $family instanceof FamilyInterface ? $family->getAttributes() : [];

        foreach ($familyAttributes as $attribute) {
            if (AttributeTypes::BOOLEAN === $attribute->getType()) {
                yield $attribute;
            }
        }
    }

    /**
     * @param VariantProductInterface $product
     *
     * @return \Generator
     *
     * @todo @merge Replace VariantProductInterface by ProductInterface when merging to 2.2
     */
    private function getBooleanAttributesFromFamilyVariant(VariantProductInterface $product): \Generator
    {
        $parentProduct = $product->getParent();
        if (!$parentProduct instanceof ProductModelInterface) {
            return [];
        }

        $familyVariant = $parentProduct->getFamilyVariant();
        if (!$familyVariant instanceof FamilyVariantInterface) {
            return [];
        }

        $variationLevel = $product->getVariationLevel();
        $variantAttributeSet = $familyVariant->getVariantAttributeSet($variationLevel);
        if (!$variantAttributeSet instanceof VariantAttributeSetInterface) {
            return [];
        }

        $attributes = $variantAttributeSet->getAttributes();
        $axes = $familyVariant->getAxes();

        foreach ($attributes as $attribute) {
            if (AttributeTypes::BOOLEAN === $attribute->getType() && !$axes->contains($attribute)) {
                yield $attribute;
            }
        }
    }
}
