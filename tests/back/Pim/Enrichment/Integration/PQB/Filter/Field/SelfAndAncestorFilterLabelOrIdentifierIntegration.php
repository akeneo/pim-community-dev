<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductAndProductModelQueryBuilderTestCase;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SelfAndAncestorFilterLabelOrIdentifierIntegration extends AbstractProductAndProductModelQueryBuilderTestCase
{
    public function testQueryProductsContainingLabel()
    {
        $results = $this->executeFilter([['self_and_ancestor.label_or_identifier', Operators::CONTAINS, 'di']]);
        $this->assert(
            $results,
            [
                '1111111114',
                '1111111115',
                '1111111116',
                '1111111117',
                '1111111118',
                '1111111205',
                '1111111206',
                '1111111207',
                '1111111208',
                '1111111209',
                '1111111210',
                '1111111211',
                '1111111212',
                '1111111213',
                '1111111214',
                '1111111215',
                '1111111216',
                'aphrodite',
                'diana',
                'diana_pink',
                'diana_red',
                'dionysos',
                'model-tshirt-divided',
                'model-tshirt-divided-battleship-grey',
                'model-tshirt-divided-crimson-red',
                'model-tshirt-divided-navy-blue',
                'tshirt-divided-navy-blue-xxs',
                'tshirt-divided-navy-blue-m',
                'tshirt-divided-navy-blue-l',
                'tshirt-divided-navy-blue-xxxl',
                'tshirt-divided-crimson-red-xxs',
                'tshirt-divided-crimson-red-m',
                'tshirt-divided-crimson-red-l',
                'tshirt-divided-crimson-red-xxxl',
                'tshirt-divided-battleship-grey-xxs',
                'tshirt-divided-battleship-grey-m',
                'tshirt-divided-battleship-grey-l',
                'tshirt-divided-battleship-grey-xxxl',
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
