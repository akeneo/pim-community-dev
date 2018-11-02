<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductAndProductModelQueryBuilderTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SelfAndAncestorFilterIntegration extends AbstractProductAndProductModelQueryBuilderTestCase
{
    public function testOperatorInListForRootProductModel()
    {
        $result = $this->executeFilter([['self_and_ancestor.id', Operators::IN_LIST, ['product_model_48']]]);
        $this->assert(
            $result,
            [
                'model-running-shoes',
                'model-running-shoes-m',
                'model-running-shoes-xxs',
                'model-running-shoes-xxxl',
                'running-shoes-m-antique-white',
                'running-shoes-m-navy-blue',
                'running-shoes-m-crimson-red',
                'running-shoes-xxs-antique-white',
                'running-shoes-xxs-navy-blue',
                'running-shoes-xxs-crimson-red',
                'running-shoes-xxxl-antique-white',
                'running-shoes-xxxl-navy-blue',
                'running-shoes-xxxl-crimson-red',
            ]
        );
    }

    public function testOperatorInListForSubProductModel()
    {
        $result = $this->executeFilter([['self_and_ancestor.id', Operators::IN_LIST, ['product_model_76']]]);
        $this->assert(
            $result,
            [
                'model-running-shoes-xxs',
                'running-shoes-xxs-antique-white',
                'running-shoes-xxs-navy-blue',
                'running-shoes-xxs-crimson-red',
            ]
        );
    }

    public function testOperatorInListForMultipleProductModels()
    {
        $result = $this->executeFilter(
            [['self_and_ancestor.id', Operators::IN_LIST, ['product_model_76', 'product_model_46']]]
        );
        $this->assert(
            $result,
            [
                'model-running-shoes-xxs',
                'running-shoes-xxs-antique-white',
                'running-shoes-xxs-navy-blue',
                'running-shoes-xxs-crimson-red',
                'model-braided-hat',
                'braided-hat-m',
                'braided-hat-xxxl',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
