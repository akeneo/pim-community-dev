<?php

namespace tests\integration\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Test\Integration\TestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryIntegration extends TestCase
{
    protected $purgeDatabaseForEachTest = false;

    public function testCategoryRoot()
    {
        $expected = [
            'code'   => 'master',
            'parent' => null,
        ];

        $this->assert('master', $expected);
    }

    public function testCategoryWithParent()
    {
        $expected = [
            'code'   => 'categoryA',
            'parent' => 'master',
        ];

        $this->assert('categoryA', $expected);
    }

    private function assert($identifier, array $expected)
    {
        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier($identifier);
        $flatAttribute = $this->get('pim_versioning.serializer')->normalize($category, 'flat');

        $this->assertSame($expected, $flatAttribute);
    }
}
