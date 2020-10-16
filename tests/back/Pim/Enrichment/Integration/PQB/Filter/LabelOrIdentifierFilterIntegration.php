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
        $this->assert($result, ['model-braided-hat', '1111111240', 'braided-hat-m', 'braided-hat-xxxl']);

        $result = $this->executeFilter([['label_or_identifier', Operators::CONTAINS, 'ha', ['locale' => 'en_US', 'scope' => 'ecommerce']]]);
        $this->assert($result, [
            'model-braided-hat',
            'hades',
            'braided-hat-m',
            'braided-hat-xxxl',
            '1111111234',
            '1111111235',
            '1111111236',
            '1111111237',
            '1111111238',
            '1111111239',
            '1111111240',
            'hades_blue',
            'hades_red',
        ]);
    }

    public function testSearchOnLabelAndCompleteness()
    {
        $result = $this->executeFilter([
            ['label_or_identifier', Operators::CONTAINS, 'hat', ['locale' => 'en_US', 'scope' => 'ecommerce']],
            ['completeness', Operators::AT_LEAST_COMPLETE, null, ['locale' => 'en_US', 'scope' => 'ecommerce']]
        ]);
        $this->assert($result, ['model-braided-hat', 'braided-hat-m', 'braided-hat-xxxl']);
    }
}
