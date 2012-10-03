<?php
namespace Strixos\CatalogEavBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Execute with "php app/console doctrine:fixtures:load"
 *
 * @author     Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadProducts extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    const ATTRIBUTE_SET_BASE          = 'base';
    const ATTRIBUTE_SET_GROUP_BASE    = 'base';
    const ATTRIBUTE_SET_GROUP_META    = 'meta';
    const ATTRIBUTE_SET_GROUP_PRICING = 'pricing';
    const ATTRIBUTE_SET_GROUP_MEDIA   = 'media';
    const ATTRIBUTE_SET_GROUP_TECHNIC = 'technical';

    const ATTRIBUTE_SET_TSHIRT    = 'tshirt';
    const ATTRIBUTE_GROUP_TSHIRT  = 'tshirt';
    const ATTRIBUTE_TSHIRT_COLOR  = 'tshirt_color';
    const ATTRIBUTE_TSHIRT_SIZE   = 'tshirt_size';

    const ATTRIBUTE_SET_LAPTOP    = 'laptop';
    const ATTRIBUTE_GROUP_LAPTOP  = 'laptop';
    const ATTRIBUTE_LAPTOP_SCREEN = 'laptop_screen_size';
    const ATTRIBUTE_LAPTOP_CPU    = 'laptop_cpu';
    const ATTRIBUTE_LAPTOP_MEMORY = 'laptop_memory';
    const ATTRIBUTE_LAPTOP_HDD    = 'laptop_hdd';

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
        // base set
        $baseSet = $this->_createBaseSet($manager);
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
     * Create base attribute set
     */
    protected function _createBaseSet(ObjectManager $manager)
    {
        // use factory to build entity and manager to persist
        $factory = $this->container->get('strixos_catalog_eav.productfactory');

        // create type
        $type = $factory->buildType(self::ATTRIBUTE_SET_BASE);

        // default attribute code to type
        $attributes = array(
            // base
            self::ATTRIBUTE_SET_GROUP_BASE => array(
                'name',
                'description',
                'short_description',
            ),
        );
        foreach ($attributes as $groupCode => $attributeData) {
            // create group
            $group = $factory->buildGroup($groupCode, $type);
            // create fields
            foreach ($attributeData as $code) {
                $field = $factory->buildField($code);
                $manager->persist($field);
                // add attribute to type
                $type->addField($field);
                // add attribute to group
                $group->addField($field);
            }
            // add group to default type
            $type->addGroup($group);
            // persist group
            $manager->persist($group);
        }
        // persist set
        $manager->persist($type);
        // create product
        $product = $factory->buildEntity($type);

        $product->setName('Dell Xps 15z');
        $product->setDescription('200 GO SATA');

        $manager->persist($product);

        $manager->flush();
    }

}