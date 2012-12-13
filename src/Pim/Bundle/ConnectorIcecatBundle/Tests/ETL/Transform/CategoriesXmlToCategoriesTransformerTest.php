<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\Transform;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Transform\CategoriesXmlToCategoriesTransformer;

use Pim\Bundle\CatalogBundle\Tests\KernelAwareTest;
/**
 * Test CategoriesXmlToCategoriesTransformer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CategoriesXmlToCategoriesTransformerTest extends KernelAwareTest
{
    /**
     * Test loading categories
     */
    public function testXmlToCategories()
    {
        $filename = 'categories-list.xml';
        $content = $this->loadFile($filename);

        // call transformer
        $transformer = new CategoriesXmlToCategoriesTransformer($content);
        $categories = $transformer->transform();

        $this->assertCount(18, $categories);
    }

    /**
     * Load a file in SimpleXmlElement
     * @param string $filename
     *
     * @return string
     */
    protected function loadfile($filename)
    {
        $filepath = dirname(__FILE__) .'/../../Files/'. $filename;

        return simplexml_load_file($filepath);
    }
}
