<?php
namespace Oro\Bundle\ProductBundle\Test\Service;

use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\DataModelBundle\Model\Attribute\AttributeTypeString;
use Oro\Bundle\DataModelBundle\Model\Attribute\AttributeTypeInteger;

use Oro\Bundle\DataModelBundle\Tests\KernelAwareTest;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ProductManagerTest extends KernelAwareTest
{

    /**
     * @var FlexibleEntityManager
     */
    protected $manager;

    /**
     * UT set up
     */
    public function setUp()
    {
        parent::setUp();
        $this->manager = $this->container->get('product_manager');
    }

    /**
     * Test related method
     */
    public function testGetNewEntityInstance()
    {
        $newProduct = $this->manager->getNewEntityInstance();
        $this->assertTrue($newProduct instanceof Product);

        $sku = 'my sku '.str_replace('.', '', microtime(true));
        $newProduct->setSku($sku);

        $this->manager->getStorageManager()->persist($newProduct);
        $this->manager->getStorageManager()->flush();
    }

    /**
     * Test related method
     */
    public function testGetNewValueInstance()
    {
        $timestamp = str_replace('.', '', microtime(true));

        // entity
        $newProduct = $this->manager->getNewEntityInstance();
        $this->assertTrue($newProduct instanceof Product);
        $sku = 'my sku '.$timestamp;
        $newProduct->setSku($sku);

        // attribute type
        $attTypeString = new AttributeTypeString();
        $attTypeInteger = new AttributeTypeInteger();

        // attribute name
        $attName = $this->manager->getNewAttributeInstance();
        $attNameCode= 'name'.$timestamp;
        $attName->setCode($attNameCode);
        $attName->setTitle('Name');
        $attName->setAttributeType($attTypeString);
        $attName->setTranslatable(true);
        $this->manager->getStorageManager()->persist($attName);

        // attribute size
        $attSize = $this->manager->getNewAttributeInstance();
        $attSizeCode= 'size'.$timestamp;
        $attSize->setCode($attSizeCode);
        $attSize->setTitle('Size');
        $attSize->setAttributeType($attTypeInteger);
        $this->manager->getStorageManager()->persist($attSize);

        // name value
        $valueName = $this->manager->getNewAttributeValueInstance();
        $valueName->setAttribute($attName);
        $valueName->setData('my name');
        $newProduct->addValue($valueName);

        // size value
        $valueSize = $this->manager->getNewAttributeValueInstance();
        $valueSize->setAttribute($attSize);
        $valueSize->setData(125);
        $newProduct->addValue($valueSize);

        // persist
        $this->manager->getStorageManager()->persist($newProduct);
        $this->manager->getStorageManager()->flush();
    }
}
