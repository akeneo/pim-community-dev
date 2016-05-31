<?php

namespace Pim\Behat\Context\Storage;

use Behat\Gherkin\Node\TableNode;
use Pim\Behat\Context\PimContext;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnInfoExtractor;

class ProductStorage extends PimContext
{
    /**
     * @param string    $identifier
     * @param TableNode $table
     *
     * @throws \Exception
     *
     * @Given /^the product "([^"]*)" should not have the following values?:$/
     */
    public function theProductShouldNotHaveTheFollowingValues($identifier, TableNode $table)
    {
        $this->getMainContext()->getSubcontext('hook')->clearUOW();
        $product = $this->getFixturesContext()->getEntity('Product', $identifier);

        foreach ($table->getRowsHash() as $rawCode => $value) {
            $infos = $this->getFieldExtractor()->extractColumnInfo($rawCode);

            $attribute     = $infos['attribute'];
            $attributeCode = $attribute->getCode();
            $localeCode    = $infos['locale_code'];
            $scopeCode     = $infos['scope_code'];
            $productValue  = $product->getValue($attributeCode, $localeCode, $scopeCode);

            if (null !== $productValue) {
                throw new \Exception(sprintf('Product value for product "%s" exists', $identifier));
            }
        }
    }

    /**
     * @return AttributeColumnInfoExtractor
     */
    private function getFieldExtractor()
    {
        return $this->getService('pim_connector.array_converter.flat.product.attribute_column_info_extractor');
    }
}
