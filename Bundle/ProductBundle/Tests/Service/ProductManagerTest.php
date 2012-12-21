<?php
namespace Oro\Bundle\ProductBundle\Test\Service;

use Oro\Bundle\ProductBundle\Entity\ProductEntity;

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
        $this->assertTrue($newProduct instanceof ProductEntity);

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
        $this->manager->getStorageManager()->persist($attName);

        // attribute size
        $attSize = $this->manager->getNewAttributeInstance();
        $attSizeCode= 'size'.$timestamp;
        $attSize->setCode($attSizeCode);
        $attSize->setTitle('Size');
        $attSize->setType('number');
        $this->manager->getStorageManager()->persist($attSize);

        // translate title in many locales in one time (saved when flush on entity manager)
        $repository = $this->manager->getStorageManager()->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $repository
            ->translate($attSize, 'title', 'de_De', 'title DE')
            ->translate($attSize, 'title', 'it_IT', 'title IT')
            ->translate($attSize, 'title', 'es_ES', 'title ES');

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

        // localized
//        $this->manager->getAttributeValueRepository()->findByCode('name');
//        $valueName

        $valueName->setTranslatableLocale('fr_FR');
        $valueName->setData('mon nom');
        $this->manager->getStorageManager()->persist($valueName);
        $this->manager->getStorageManager()->flush();

    }
}
