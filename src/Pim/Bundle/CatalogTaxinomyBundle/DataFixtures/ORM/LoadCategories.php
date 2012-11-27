<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\DataFixtures\ORM;

use Pim\Bundle\CatalogTaxinomyBundle\Entity\Category;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
/**
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadCategories extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    protected $container;

    protected $manager;

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
        $this->manager = $manager;

        $food = new Category();
        $food->setTitle('food');

        $fruits = new Category();
        $fruits->setTitle('fruits');
        $fruits->setParent($food);


        $vegetables = new Category();
        $vegetables->setTitle('vegetables');
        $vegetables->setParent($food);

        $carrots = new Category();
        $carrots->setTitle('carrots');
        $carrots->setParent($vegetables);


//         $food->setLeft(1);
//         $food->setRight(8);

//         $fruits->setLeft(2);
//         $fruits->setRight(3);

//         $vegetables->setLeft(4);
//         $vegetables->setRight(7);

//         $carrots->setLeft(5);
//         $carrots->setRight(6);



        $this->manager->persist($food);
        $this->manager->persist($fruits);
        $this->manager->persist($vegetables);
        $this->manager->persist($carrots);

        $this->manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }
}