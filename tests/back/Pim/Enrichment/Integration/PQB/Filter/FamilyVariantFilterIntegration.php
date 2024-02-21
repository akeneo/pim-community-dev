<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductAndProductModelQueryBuilderTestCase;

class FamilyVariantFilterIntegration extends AbstractProductAndProductModelQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    public function testSearch()
    {
        $result = $this->executeFilter([['family_variant', Operators::IS_EMPTY, null]]);
        $this->assert($result, [
            '1111111171',
            '1111111172',
            '1111111240',
            '1111111292',
            '1111111304',
            'watch',
        ]);

        $result = $this->executeFilter([
            ['family_variant', Operators::IS_NOT_EMPTY, null],
        ]);
        $this->assert($result, [
            'amor',
            'aphrodite',
            'apollon',
            'ares',
            'artemis',
            'athena',
            'aurora',
            'bacchus',
            'brogueshoe',
            'brooksblue',
            'brookspink',
            'caelus',
            'climbingshoe',
            'converseblack',
            'conversered',
            'derby',
            'diana',
            'dionysos',
            'dressshoe',
            'elegance',
            'eris',
            'galesh',
            'hades',
            'hefaistos',
            'hera',
            'hermes',
            'hestia',
            'jack',
            'jellyshoes',
            'juno',
            'minerva',
            'moccasin',
            'model-biker-jacket',
            'model-braided-hat',
            'model-running-shoes',
            'model-tshirt-divided',
            'model-tshirt-unique-color-kurt',
            'model-tshirt-unique-size',
            'plain',
            'portunus',
            'poseidon',
            'quirinus',
            'securitas',
            'stilleto',
            'stock',
            'terminus',
            'venus',
            'vulcanus',
            'zeus',
        ]);
    }
}
