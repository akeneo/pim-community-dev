<?php
namespace Oro\Bundle\ProductBundle\Test\Service;

use Oro\Bundle\ProductBundle\Entity\ProductEntity;

use Oro\Bundle\DataModelBundle\Tests\KernelAwareTest;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->assertTrue($newProduct instanceof ProductEntity);

        $sku = 'my sku '.str_replace('.', '', microtime(true));
        $newProduct->setSku($sku);

        $this->manager->getPersistenceManager()->persist($newProduct);
        $this->manager->getPersistenceManager()->flush();
    }

    /**
     * Test related method
     */
    public function testGetNewValueInstance()
    {
        $timestamp = str_replace('.', '', microtime(true));

        // entity
        $newProduct = $this->manager->getNewEntityInstance();
        $this->assertTrue($newProduct instanceof ProductEntity);
        $sku = 'my sku '.$timestamp;
        $newProduct->setSku($sku);

        // attribute name
        $attName = $this->manager->getNewAttributeInstance();
        $attNameCode= 'name'.$timestamp;
        $attName->setCode($attNameCode);
        $attName->setTitle('Name');
        $attName->setType('string');
        $attName->setTranslatable(true);
        $this->manager->getPersistenceManager()->persist($attName);

        // attribute size
        $attSize = $this->manager->getNewAttributeInstance();
        $attSizeCode= 'size'.$timestamp;
        $attSize->setCode($attSizeCode);
        $attSize->setTitle('Size');
        $attSize->setType('number');
        $this->manager->getPersistenceManager()->persist($attSize);

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
        $this->manager->getPersistenceManager()->persist($newProduct);
        $this->manager->getPersistenceManager()->flush();

        // localized
//        $this->manager->getAttributeValueRepository()->findByCode('name');
//        $valueName

        $valueName->setTranslatableLocale('fr_FR');
        $valueName->setData('mon nom');
        $this->manager->getPersistenceManager()->persist($valueName);
        $this->manager->getPersistenceManager()->flush();

    }
}
