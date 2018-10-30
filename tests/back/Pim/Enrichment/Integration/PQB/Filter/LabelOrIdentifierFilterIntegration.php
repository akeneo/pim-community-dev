<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductAndProductModelQueryBuilderTestCase;

/**
 * @author    Julien Sanchez <julien@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LabelOrIdentifierFilterIntegration extends AbstractProductAndProductModelQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * We search labels and identifiers both on products and product models and
     * check that we get both in the same result
     */
    public function testSearch()
    {
        $result = $this->executeFilter([['label_or_identifier', Operators::CONTAINS, 'hat', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['model-braided-hat', '1111111240']);

        $result = $this->executeFilter([['label_or_identifier', Operators::CONTAINS, 'ha', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, ['model-braided-hat', 'hades', '1111111240']);
    }

    public function testSearchOnLabelAndCompleteness()
    {
        $result = $this->executeFilter([
            ['label_or_identifier', Operators::CONTAINS, 'hat', ['locale' => 'en_US', 'scope' => 'ecommerce']],
            ['completeness', Operators::AT_LEAST_COMPLETE, null, ['locale' => 'en_US', 'scope' => 'ecommerce']]
        ]);
        $this->assert($result, ['model-braided-hat']);
    }
}
