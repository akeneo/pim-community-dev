<?php


namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Family;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductSaver;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilder;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindAttributeCodeAsLabelForFamilyIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @throws \Exception
     */
    public function testQueryFetchAttributeCodeAsLabelForFamily()
    {
        $attributeCodeAsLabel = 'sku';
        $familyCode = 'familyCode';
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, ['code' => $familyCode, 'attributes' => [$attributeCodeAsLabel], 'attribute_as_label' => $attributeCodeAsLabel]);
        $this->get('pim_catalog.saver.family')->save($family);

        $this->assertEquals($attributeCodeAsLabel,
            $this->get('pim_catalog.doctrine.query.find_attribute_code_as_label_for_family')->execute($familyCode)
        );
    }
}
