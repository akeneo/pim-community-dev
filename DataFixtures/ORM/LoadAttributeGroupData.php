<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Load fixtures for attribute groups
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadAttributeGroupData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * Object manager
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $manager;

    /**
     * count groups created to order them
     * @staticvar integer
     */
    static protected $order = 0;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Get group manager
     * @return \Oro\Bundle\FlexibleEntityBundle\Manager\SimpleManager
     */
    protected function getGroupManager()
    {
        return $this->container->get('attribute_group_manager');
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        // create group
        $group = $this->createGroup('General');
        $this->getGroupManager()->getStorageManager()->persist($group);

        // link attributes with group
        $attribute = $this->getReference('product-attribute.name');
        $attribute->setGroup($group);
        $this->getProductManager()->getStorageManager()->persist($attribute);

        $attribute = $this->getReference('product-attribute.shortDescription');
        $attribute->setGroup($group);
        $this->getProductManager()->getStorageManager()->persist($attribute);

        $attribute = $this->getReference('product-attribute.longDescription');
        $attribute->setGroup($group);
        $this->getProductManager()->getStorageManager()->persist($attribute);


        $group = $this->createGroup('SEO');
        $this->getGroupManager()->getStorageManager()->persist($group);

        $group = $this->createGroup('Marketing');
        $this->getGroupManager()->getStorageManager()->persist($group);

        $attribute = $this->getReference('product-attribute.price');
        $attribute->setGroup($group);
        $this->getProductManager()->getStorageManager()->persist($attribute);


        // create group and link attribute
        $group = $this->createGroup('Sizes');
        $this->getGroupManager()->getStorageManager()->persist($group);

        $attribute = $this->getReference('product-attribute.size');
        $attribute->setGroup($group);
        $this->getProductManager()->getStorageManager()->persist($attribute);

        // create group and link attribute
        $group = $this->createGroup('Colors');
        $this->getGroupManager()->getStorageManager()->persist($group);

        $attribute = $this->getReference('product-attribute.color');
        $attribute->setGroup($group);
        $this->getProductManager()->getStorageManager()->persist($attribute);

        // flush
        $this->getGroupManager()->getStorageManager()->flush();
    }

    /**
     * Get product manager
     * @return FlexibleManager
     */
    protected function getProductManager()
    {
        return $this->container->get('product_manager');
    }

    /**
     * Create a group
     * @param string $name
     *
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeGroup
     */
    protected function createGroup($name)
    {
        $group = $this->getGroupManager()->createEntity();
        $group->setName($name);
        $group->setSortOrder(++self::$order);

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 5;
    }
}
