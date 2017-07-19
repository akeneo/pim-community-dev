<?php

namespace Pim\Behat\Context\Storage;

use Behat\Gherkin\Node\TableNode;
use Pim\Behat\Context\PimContext;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnInfoExtractor;

class ProductModelStorage extends PimContext
{
    /**
     * @Then /^there should be the following (root product model|product model):$/
     */
    public function theProductShouldNotHaveTheFollowingValues($identifier, TableNode $table)
    {
        $this->getMainContext()->getSubcontext('hook')->clearUOW();
        $product = $this->getFixturesContext()->getEntity('ProductModel', $identifier);

        foreach ($table->getRowsHash() as $rawCode => $value) {
            $infos = $this->extractColumnInfo($rawCode);

            $attribute     = $infos['attribute'];
            $priceCurrency = isset($infos['price_currency']) ? $infos['price_currency'] : null;
            $productValue  = $product->getValue($attribute->getCode(), $infos['locale_code'], $infos['scope_code']);

            if ('' === $value) {
                assertEmpty((string) $productValue);
            } elseif ('media' === $attribute->getBackendType()) {
                // media filename is auto generated during media handling and cannot be guessed
                // (it contains a timestamp)
                if ('**empty**' === $value) {
                    assertEmpty((string) $productValue);
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
                assertEquals($value, (string) $productValue);
            }
        }
    }

    /**
     * @param string $rawCode
     *
     * @return array|null
     */
    private function extractColumnInfo(string $rawCode): ?array
    {
        return $this->getService('pim_connector.array_converter.flat_to_standard.product.attribute_column_info_extractor')
            ->extractColumnInfo($rawCode);
    }
}
