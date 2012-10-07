<?php
namespace Akeneo\CatalogBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Akeneo\CatalogBundle\Model\ProductType;
use Akeneo\CatalogBundle\Entity\Field;

/**
 * Load product and types
 *
 * Execute with "php app/console doctrine:fixtures:load"
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadProducts extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    const TYPE_BASE          = 'base';
    const TYPE_GROUP_INFO    = 'general';
    const TYPE_GROUP_MEDIA   = 'media';
    const TYPE_GROUP_SEO     = 'seo';
    const TYPE_GROUP_TECHNIC = 'technical';

    const TYPE_TSHIRT        = 'tshirt';
    const TYPE_GROUP_TSHIRT  = 'tshirt';
    const FIELD_TSHIRT_COLOR = 'tshirt_color';
    const FIELD_TSHIRT_SIZE  = 'tshirt_size';

    const TYPE_LAPTOP         = 'laptop';
    const TYPE_GROUP_LAPTOP   = 'laptop';
    const FIELD_LAPTOP_SCREEN = 'laptop_screen_size';
    const FIELD_LAPTOP_CPU    = 'laptop_cpu';
    const FIELD_LAPTOP_MEMORY = 'laptop_memory';
    const FIELD_LAPTOP_HDD    = 'laptop_hdd';

    /**
    * @var ContainerInterface
    */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $base = $this->_createBaseType($manager);
        $this->_createProducts($manager);
    }

    /**
     * Executing order
     * @see Doctrine\Common\DataFixtures.OrderedFixtureInterface::getOrder()
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * Create base product type
     */
    protected function _createBaseType(ObjectManager $manager)
    {
        // create type
        $type = $this->container->get('akeneo.catalog.model_producttype');
        $type->create(self::TYPE_BASE);
        // add info fields
        $fields = array('sku', 'name', 'short_description', 'description', 'color');
        foreach ($fields as $fieldCode) {
            if (!$type->getField($fieldCode)) {
                $type->addField($fieldCode, Field::TYPE_TEXT, self::TYPE_GROUP_INFO);
            }
        }
        // add media fields
        $fields = array('image', 'thumbnail');
        foreach ($fields as $fieldCode) {
            if (!$type->getField($fieldCode)) {
                $type->addField($fieldCode, Field::TYPE_TEXT, self::TYPE_GROUP_MEDIA);
            }
        }
        // add others empty groups
        $type->addGroup(self::TYPE_GROUP_SEO);
        $type->addGroup(self::TYPE_GROUP_TECHNIC);

        // persist type
        $type->persist();
        $type->flush();
    }

    /**
     * Create products
     */
    protected function _createProducts(ObjectManager $manager)
    {
        // get base type
        $type = $this->container->get('akeneo.catalog.model_producttype');
        $type->find(self::TYPE_BASE);
        // create product
        $product = $type->newProductInstance();
        // set values
        //        $product->setValue('sku', 'mon sku 1');
        $product->setName('mon name 1');
        $product->setColor('Green');
        // save
        $product->persist();
        $product->flush();
        // translate value
        $product->setValue('color', 'Vert', 'fr_FR');
        $product->persistAndFlush();
    }

}