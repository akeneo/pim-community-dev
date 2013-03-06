<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Pim\Bundle\ProductBundle\Entity\AttributeGroup;

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
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        // create group
        $group = $this->createGroup('General');
        $manager->persist($group);

        // link attributes with group
        $attribute = $this->getReference('product-attribute.name');
        $attribute->setGroup($group);
        $manager->persist($attribute);

        $attribute = $this->getReference('product-attribute.shortDescription');
        $attribute->setGroup($group);
        $manager->persist($attribute);

        $attribute = $this->getReference('product-attribute.longDescription');
        $attribute->setGroup($group);
        $manager->persist($attribute);


        $group = $this->createGroup('SEO');
        $manager->persist($group);

        $group = $this->createGroup('Marketing');
        $manager->persist($group);

        $attribute = $this->getReference('product-attribute.price');
        $attribute->setGroup($group);
        $manager->persist($attribute);


        // create group and link attribute
        $group = $this->createGroup('Sizes');
        $manager->persist($group);

        $attribute = $this->getReference('product-attribute.size');
        $attribute->setGroup($group);
        $manager->persist($attribute);

        // create group and link attribute
        $group = $this->createGroup('Colors');
        $manager->persist($group);

        $attribute = $this->getReference('product-attribute.color');
        $attribute->setGroup($group);
        $manager->persist($attribute);

        // flush
        $manager->flush();
    }

    /**
     * Create a group
     * @param string $name
     *
     * @return \Pim\Bundle\ProductBundle\Entity\AttributeGroup
     */
    protected function createGroup($name)
    {
        $group = new AttributeGroup();
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
