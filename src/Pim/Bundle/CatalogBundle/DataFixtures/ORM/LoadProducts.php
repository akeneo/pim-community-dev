<?php
namespace Pim\Bundle\CatalogBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\ProductSet;
use Pim\Bundle\CatalogBundle\Model\BaseFieldFactory;

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
        return true;
        $base = $this->_createBaseType($manager);
        $this->_createProducts($manager);
    }

    /**
     * Executing order
     * @see Doctrine\Common\DataFixtures.OrderedFixtureInterface::getOrder()
     */
    public function getOrder()
    {
        return 3;
    }

    /**
     * Create base product type
     */
    protected function _createBaseType(ObjectManager $manager)
    {
        // create type
        $type = $this->container->get('akeneo.catalog.model_productset_doctrine');
        $type->create(self::TYPE_BASE);
        // add info fields
        $fields = array(
            'sku'                => BaseFieldFactory::FIELD_STRING,
            'name'               => BaseFieldFactory::FIELD_STRING,
            'short_description'  => BaseFieldFactory::FIELD_TEXT,
            'description'        => BaseFieldFactory::FIELD_TEXT,
            'color'              => BaseFieldFactory::FIELD_SELECT
        );
        foreach ($fields as $fieldCode => $fieldType) {
            if (!$type->getField($fieldCode)) {
                $type->addAttribute($fieldCode, $fieldType, self::TYPE_GROUP_INFO);
            }
        }
        // add media fields
        $fields = array('image', 'thumbnail');
        foreach ($fields as $fieldCode) {
            if (!$type->getField($fieldCode)) {
                $type->addAttribute($fieldCode, BaseFieldFactory::FIELD_IMAGE, self::TYPE_GROUP_MEDIA);
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
        $type = $this->container->get('akeneo.catalog.model_productset_doctrine');
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
        // translate value in FR
        $product->switchLocale('fr_FR');
        $product->setValue('color', 'Vert');
        $product->persist();
        $product->flush();
        // translate value in DE
        $product->switchLocale('de_DE');
        $product->setValue('color', 'Grün');
        $product->persist();
        $product->flush();
    }

}