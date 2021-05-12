<?php

namespace Akeneo\Tool\Component\Api\tests\integration\Normalizer;

use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedCategoryCleaner;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryIntegration extends AbstractNormalizerTestCase
{
    public function testCategoryRoot()
    {
        $expected = [
            'code'   => 'master',
            'parent' => null,
            'updated' => '2016-06-14T13:12:50+02:00',
            'labels' => new \StdClass(),
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
        $serializer = $this->get('pim_external_api_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier($identifier), 'external_api');

        NormalizedCategoryCleaner::clean($expected);
        NormalizedCategoryCleaner::clean($result);

        $this->assertEquals($expected, $result);
    }
}
