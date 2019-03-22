<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Bundle\Sql\GetFamilyAttributeCodes;
use Akeneo\Pim\Enrichment\Bundle\Sql\GetVariantAttributeSetAttributeCodes;
use Akeneo\Pim\Enrichment\Bundle\Sql\GetVariantAttributeSetAxesCodes;
use Akeneo\Pim\Enrichment\Bundle\Sql\LruArrayAttributeRepository;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
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

    /** @var LruArrayAttributeRepository */
    private $attributeRepository;

    /** @var GetFamilyAttributeCodes */
    private $getFamilyAttributeCodes;

    /** @var GetVariantAttributeSetAttributeCodes */
    private $getVariantAttributeSetAttributeCodes;

    /** @var GetVariantAttributeSetAxesCodes */
    private $getVariantAttributeSetAxesCodes;

    /**
     * @param AttributeValuesResolverInterface $valuesResolver
     * @param ValueFactory $productValueFactory
     * @param LruArrayAttributeRepository $attributeRepository
     * @param GetFamilyAttributeCodes $getFamilyAttributeCodes
     * @param GetVariantAttributeSetAttributeCodes $getVariantAttributeSetAttributeCodes
     * @param GetVariantAttributeSetAxesCodes $getVariantAttributeSetAxesCodes
     */
    public function __construct(
        AttributeValuesResolverInterface $valuesResolver,
        ValueFactory $productValueFactory,
        LruArrayAttributeRepository $attributeRepository,
        GetFamilyAttributeCodes $getFamilyAttributeCodes,
        GetVariantAttributeSetAttributeCodes $getVariantAttributeSetAttributeCodes,
        GetVariantAttributeSetAxesCodes $getVariantAttributeSetAxesCodes
    ) {
        $this->valuesResolver = $valuesResolver;
        $this->productValueFactory = $productValueFactory;
        $this->attributeRepository = $attributeRepository;
        $this->getFamilyAttributeCodes = $getFamilyAttributeCodes;
        $this->getVariantAttributeSetAttributeCodes = $getVariantAttributeSetAttributeCodes;
        $this->getVariantAttributeSetAxesCodes = $getVariantAttributeSetAxesCodes;
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

        $booleanAttributes = $product->isVariant()
            ? $this->getBooleanAttributesFromFamilyVariant($product)
            : $this->getBooleanAttributesFromFamily($product);

        foreach ($booleanAttributes as $attribute) {
            $eligibleValues = $this->valuesResolver->resolveEligibleValues([$attribute]);

            foreach ($eligibleValues as $valueData) {
                $value = $product->getValue($attribute->getCode(), $valueData['locale'], $valueData['scope']);

                if (null === $value) {
                    $value = $this->productValueFactory->create(
                        $attribute,
                        $valueData['scope'],
                        $valueData['locale'],
                        false
                    );
                    $product->addValue($value);
                }
            }
        }
    }

    /**
     * @param ProductInterface $product
     *
     * @return iterable
     */
    private function getBooleanAttributesFromFamily(ProductInterface $product): iterable
    {
        $family = $product->getFamily();
        if ($family instanceof FamilyInterface) {
            $familyAttributes = $this->attributeRepository->findSeveralByIdentifiers(
                $this->getFamilyAttributeCodes->execute($family->getCode())
            );
            foreach ($familyAttributes as $attribute) {
                if (AttributeTypes::BOOLEAN === $attribute->getType()) {
                    yield $attribute;
                }
            }
        }

        return [];
    }

    /**
     * @param ProductInterface $product
     *
     * @return iterable
     */
    private function getBooleanAttributesFromFamilyVariant(ProductInterface $product): iterable
    {
        $parentProduct = $product->getParent();
        if (!$parentProduct instanceof ProductModelInterface) {
            return [];
        }

        $familyVariant = $parentProduct->getFamilyVariant();
        if (!$familyVariant instanceof FamilyVariantInterface) {
            return [];
        }

        $familyVariantCode = $familyVariant->getCode();
        $level = $product->getVariationLevel();
        $variantProductAttributeCodes = array_diff(
            $this->getVariantAttributeSetAttributeCodes->execute($familyVariantCode, $level),
            $this->getVariantAttributeSetAxesCodes->execute($familyVariantCode, $level)
        );

        $attributes = $this->attributeRepository->findSeveralByIdentifiers($variantProductAttributeCodes);

        foreach ($attributes as $attribute) {
            if (AttributeTypes::BOOLEAN === $attribute->getType()) {
                yield $attribute;
            }
        }
    }
}
