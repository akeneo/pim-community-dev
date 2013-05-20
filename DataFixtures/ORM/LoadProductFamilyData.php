<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;

use Pim\Bundle\ProductBundle\Entity\ProductFamily;

use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

/**
 * Load fixtures for Product families
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadProductFamilyData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $attributes = array(
            $this->getReference('product-attribute.name'),
            $this->getReference('product-attribute.manufacturer'),
            $this->getReference('product-attribute.weight'),
            $this->getReference('product-attribute.shortDescription'),
            $this->getReference('product-attribute.color')
        );

        $this->createProductFamily(
            'Mug',
            'A large cup, typically cylindrical and with a handle and used without a saucer.',
            $attributes,
            $manager
        );
    }

    /**
     * Create product family
     * @param string        $name        Product family name
     * @param string        $description Product family description
     * @param array         $attributes  Product family attributes
     * @param ObjectManager $manager     EntityManager
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductFamily
     */
    protected function createProductFamily($name, $description, $attributes, $manager)
    {
        $productFamily = new ProductFamily();

        $productFamily->setLabel($name);
        $productFamily->setDescription($description);

        foreach ($attributes as $attribute) {
            $productFamily->addAttribute($attribute);
        }

        $manager->persist($productFamily);
        $manager->flush();

        return $productFamily;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 6;
    }
}
