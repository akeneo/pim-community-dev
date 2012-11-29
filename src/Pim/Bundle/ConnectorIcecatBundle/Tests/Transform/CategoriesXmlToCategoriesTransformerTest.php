<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\Transform;

use Pim\Bundle\ConnectorIcecatBundle\Transform\CategoriesXmlToCategoriesTransformer;

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
        $categories = $this->loadFile($filename);
        $this->assertCount(18, $categories);
    }

    /**
     * Load a file in SimpleXmlElement
     * @param string $filename
     *
     * @return \SimpleXMLElement
     */
    protected function loadfile($filename)
    {
        $filepath = dirname(__FILE__) .'/../Files/'. $filename;
        $content = simplexml_load_file($filepath);

        // call transformer
        $transformer = new CategoriesXmlToCategoriesTransformer($content);

        return $transformer->transform();
    }
}
