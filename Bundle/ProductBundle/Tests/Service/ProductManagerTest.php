<?php
namespace Oro\Bundle\ProductBundle\Test\Service;

use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\FlexibleEntityBundle\Model\Attribute\Type\AbstractAttributeType;

use Oro\Bundle\FlexibleEntityBundle\Tests\KernelAwareTest;

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
    public function testcreateEntity()
    {
        $newProduct = $this->manager->createEntity();
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
        $newProduct = $this->manager->createEntity();
        $this->assertTrue($newProduct instanceof Product);
        $sku = 'my sku '.$timestamp;
        $newProduct->setSku($sku);

        // attribute name
        $attName = $this->manager->createAttribute();
        $attNameCode= 'name'.$timestamp;
        $attName->setCode($attNameCode);
        $attName->setTitle('Name');
        $attName->setBackendModel(AbstractAttributeType::BACKEND_MODEL_ATTRIBUTE_VALUE);
        $attName->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
        $attName->setTranslatable(true);
        $this->manager->getStorageManager()->persist($attName);

        // attribute size
        $attSize = $this->manager->createAttribute();
        $attSizeCode= 'size'.$timestamp;
        $attSize->setCode($attSizeCode);
        $attSize->setTitle('Size');
        $attSize->setBackendModel(AbstractAttributeType::BACKEND_MODEL_ATTRIBUTE_VALUE);
        $attSize->setBackendType(AbstractAttributeType::BACKEND_TYPE_INTEGER);
        $this->manager->getStorageManager()->persist($attSize);

        // name value
        $valueName = $this->manager->createEntityValue();
        $valueName->setAttribute($attName);
        $valueName->setData('my name');
        $newProduct->addValue($valueName);

        // size value
        $valueSize = $this->manager->createEntityValue();
        $valueSize->setAttribute($attSize);
        $valueSize->setData(125);
        $newProduct->addValue($valueSize);

        // persist
        $this->manager->getStorageManager()->persist($newProduct);
        $this->manager->getStorageManager()->flush();
    }
}
