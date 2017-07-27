<?php

namespace Pim\Behat\Context\Storage;

use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Pim\Component\Catalog\Repository\FamilyVariantRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnInfoExtractor;

class ProductModelStorage extends RawMinkContext
{
    /** @var array */
    private $productModelFields = ['identifier', 'parent', 'categories', 'family_variant'];

    /** @var AttributeColumnInfoExtractor */
    private $attributeColumnInfoExtractor;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var FamilyVariantRepositoryInterface */
    private $familyVariantRepository;

    /**
     * @param AttributeColumnInfoExtractor     $attributeColumnInfoExtractor
     * @param ProductModelRepositoryInterface  $productModelRepository
     * @param FamilyVariantRepositoryInterface $familyVariantRepository
     */
    public function __construct(
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        ProductModelRepositoryInterface $productModelRepository,
        FamilyVariantRepositoryInterface $familyVariantRepository
    ) {
        $this->attributeColumnInfoExtractor = $attributeColumnInfoExtractor;
        $this->productModelRepository = $productModelRepository;
        $this->familyVariantRepository = $familyVariantRepository;
    }

    /**
     * @Then /^there should be the following (?:|root product model|product model):$/
     */
    public function theProductShouldHaveTheFollowingValues(TableNode $properties)
    {
        foreach ($properties->getHash() as $rawPoductModel) {
            $productModel = $this->productModelRepository->findOneByIdentifier($rawPoductModel['identifier']);

            foreach ($rawPoductModel as $propertyName => $value) {
                if (in_array($propertyName, $this->productModelFields)) {
                    switch ($propertyName) {
                        case 'parent':
                            $actualParent = $productModel->getParent();
                            $expectedParent = $this->productModelRepository->findOneByIdentifier($value);

                            assertSame($actualParent, $expectedParent);
                            break;
                        case 'family_variant':
                            $actualFamilyVariant = $productModel->getFamilyVariant();
                            $expectedFamilyVariant = $this->familyVariantRepository->findOneByIdentifier($value);

                            assertSame($actualFamilyVariant, $expectedFamilyVariant);
                            break;
                        case 'categories':
                            $actualCategoryCodes = $productModel->getCategoryCodes();
                            $expectedCategoryCodes = explode(',', $value);

                            assertSame($actualCategoryCodes, $expectedCategoryCodes);
                            break;
                    }
                } else {
                    $infos = $this->attributeColumnInfoExtractor->extractColumnInfo($propertyName);

                    $attribute = $infos['attribute'];
                    $priceCurrency = isset($infos['price_currency']) ? $infos['price_currency'] : null;
                    $productValue = $productModel->getValue(
                        $attribute->getCode(),
                        $infos['locale_code'],
                        $infos['scope_code']
                    );

                    if ('' === $value) {
                        assertEmpty((string)$productValue);
                    } elseif ('media' === $attribute->getBackendType()) {
                        // media filename is auto generated during media handling and cannot be guessed
                        // (it contains a timestamp)
                        if ('**empty**' === $value) {
                            assertEmpty((string)$productValue);
                        } else {
                            assertTrue(
                                null !== $productValue->getData() &&
                                false !== strpos($productValue->getData()->getOriginalFilename(), $value)
                            );
                        }
                    } elseif ('prices' === $attribute->getBackendType() && null !== $priceCurrency) {
                        // $priceCurrency can be null if we want to test all the currencies at the same time
                        // in this case, it's a simple string comparison
                        // example: 180.00 EUR, 220.00 USD

                        $price = $productValue->getPrice($priceCurrency);
                        assertEquals($value, $price->getData());
                    } elseif ('date' === $attribute->getBackendType()) {
                        assertEquals($value, $productValue->getData()->format('Y-m-d'));
                    } else {
                        assertEquals($value, (string)$productValue);
                    }
                }
            }
        }
    }
}
