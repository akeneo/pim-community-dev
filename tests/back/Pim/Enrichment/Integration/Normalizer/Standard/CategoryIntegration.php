<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Normalizer\Standard;

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
            'labels' => []
        ];

        $this->assert('master', $expected);
    }

    public function testCategoryWithParent()
    {
        $expected = [
            'code'   => 'categoryA',
            'parent' => 'master',
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

        $this->assertSame($expected, $result);
    }
}
