<?php
declare(strict_types=1);

namespace Pim\Behat\Context\Storage;

use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\FamilyVariantRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnInfoExtractor;

class ProductModelStorage extends RawMinkContext
{
    /** @var array */
    private $productModelFields = ['code', 'parent', 'categories', 'family_variant'];

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
        foreach ($properties->getHash() as $rawProductModel) {
            $productModel = $this->productModelRepository->findOneByIdentifier($rawProductModel['code']);

            if (null === $productModel) {
                throw new \Exception(
                    sprintf('The model with the code "%s" does not exist', $rawProductModel['code'])
                );
            }

            foreach ($rawProductModel as $propertyName => $value) {
                if (in_array($propertyName, $this->productModelFields)) {
                    $this->checkProductModelField($productModel, $propertyName, $value);
                } else {
                    $this->checkProductModelValue($productModel, $propertyName, $value);
                }
            }
        }
    }

    /**
     * @Given the product model :identifier should not have the following values :attributesCode
     */
    public function theProductShouldNotHaveTheFollowingValues($code, $attributesCodes)
    {
        $attributesCodes = explode(',', $attributesCodes);
        $attributesCodes = array_map('trim', $attributesCodes);

        $productModel = $this->productModelRepository->findOneByIdentifier($code);

        if (null === $productModel) {
            throw new \Exception(
                sprintf('The model with the identifier "%s" does not exist', $code)
            );
        }
        foreach ($attributesCodes as $propertyName) {
            $infos = $this->attributeColumnInfoExtractor->extractColumnInfo($propertyName);
            /** @var AttributeInterface $attribute */
            $attribute = $infos['attribute'];
            $productValue = $productModel->getValue($attribute->getCode(), $infos['locale_code'], $infos['scope_code']);

            if (null !== $productValue) {
                throw new \Exception(
                    sprintf('The value "%s" for product model "%s" exists', $attribute->getCode(), $code)
                );
            }
        }
    }

    /**
     * @param ProductModelInterface $productModel
     * @param string                $propertyName
     * @param mixed                 $value
     *
     * @throws \PHPUnit_Framework_Exception
     */
    private function checkProductModelField(ProductModelInterface $productModel, string $propertyName, $value): void
    {
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
    }

    /**
     * @param ProductModelInterface $productModel
     * @param string                $propertyName
     * @param mixed                 $value
     *
     * @throws \PHPUnit_Framework_Exception
     */
    private function checkProductModelValue(ProductModelInterface $productModel, string $propertyName, $value): void
    {
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
