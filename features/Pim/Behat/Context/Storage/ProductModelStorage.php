<?php

namespace Pim\Behat\Context\Storage;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnInfoExtractor;

class ProductModelStorage extends RawMinkContext
{
    /** @var AttributeColumnInfoExtractor */
    private $attributeColumnInfoExtractor;

    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /**
     * @param AttributeColumnInfoExtractor          $attributeColumnInfoExtractor
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     */
    public function __construct(
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        IdentifiableObjectRepositoryInterface $productModelRepository
    ) {
        $this->attributeColumnInfoExtractor = $attributeColumnInfoExtractor;
        $this->productModelRepository = $productModelRepository;
    }

    /**
     * @Then /^there should be the following (?:|root product model|product model):$/
     */
    public function theProductShouldNotHaveTheFollowingValues(TableNode $properties)
    {
        foreach ($properties->getHash() as $rawCode => $value) {
            $product = $this->productModelRepository->findOneByIdentifier($value['identifier']);
            $infos = $this->attributeColumnInfoExtractor->extractColumnInfo($rawCode);

            $attribute = $infos['attribute'];
            $priceCurrency = isset($infos['price_currency']) ? $infos['price_currency'] : null;
            $productValue = $product->getValue($attribute->getCode(), $infos['locale_code'], $infos['scope_code']);

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
