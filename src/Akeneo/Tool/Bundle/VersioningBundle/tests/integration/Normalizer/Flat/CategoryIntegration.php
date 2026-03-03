<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\tests\integration\Normalizer\Flat;

use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedCategoryCleaner;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @group ce
 */
class CategoryIntegration extends AbstractFlatNormalizerTestCase
{
    public function testCategoryRoot()
    {
        $expected = [
            'code'   => 'master',
            'parent' => null,
            'updated' => '2016-06-14T13:12:50+02:00',
        ];

        $this->assert('master', $expected);
    }

    public function testCategoryWithParent()
    {
        $expected = [
            'code'        => 'categoryA',
            'parent'      => 'master',
            'updated' => '2016-06-14T13:12:50+02:00',
            'label-en_US' => 'Category A',
            'label-fr_FR' => 'Catégorie A',
        ];

        $this->assert('categoryA', $expected);
    }

    private function assert($identifier, array $expected)
    {
        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier($identifier);
        $flatAttribute = $this->get('pim_versioning.serializer')->normalize($category, 'flat');

        NormalizedCategoryCleaner::clean($flatAttribute);
        NormalizedCategoryCleaner::clean($expected);

        $this->assertSame($expected, $flatAttribute);
    }
}
