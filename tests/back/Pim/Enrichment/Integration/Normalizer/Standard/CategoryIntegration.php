<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Normalizer\Standard;

use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedCategoryCleaner;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryIntegration extends AbstractStandardNormalizerTestCase
{
    public function testCategoryRoot()
    {
        $expected = [
            'code'   => 'master',
            'parent' => null,
            'updated' => '2016-06-14T13:12:50+02:00',
            'labels' => []
        ];

        $this->assert('master', $expected);
    }

    public function testCategoryWithParent()
    {
        $expected = [
            'code'   => 'categoryA',
            'parent' => 'master',
            'updated' => '2016-06-14T13:12:50+02:00',
            'labels' => [
                'en_US' => 'Category A',
                'fr_FR' => 'Catégorie A'
            ]
        ];

        $this->assert('categoryA', $expected);
    }

    private function assert($identifier, array $expected)
    {
        $repository = $this->get('pim_catalog.repository.category');
        $serializer = $this->get('pim_standard_format_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier($identifier), 'standard');

        NormalizedCategoryCleaner::clean($expected);
        NormalizedCategoryCleaner::clean($result);

        $this->assertSame($expected, $result);
    }
}
