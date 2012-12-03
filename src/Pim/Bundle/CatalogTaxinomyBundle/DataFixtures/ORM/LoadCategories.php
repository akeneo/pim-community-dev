<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\DataFixtures\ORM;

use Pim\Bundle\CatalogTaxinomyBundle\Entity\Category;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
/**
 * Load category fixtures
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO : Must call the command "ImportCategoriesCommand"
 */
class LoadCategories extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ObjectManager
     */
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

//         $food = new Category();
//         $food->setTitle('food');
//         $food->setType('drive');

//         $fruits = new Category();
//         $fruits->setTitle('fruits');
//         $fruits->setType('folder');
//         $fruits->setParent($food);

//         $vegetables = new Category();
//         $vegetables->setTitle('vegetables');
//         $vegetables->setType('folder');
//         $vegetables->setParent($food);

//         $carrots = new Category();
//         $carrots->setTitle('carrots');
//         $carrots->setType('default');
//         $carrots->setParent($vegetables);

// //         $this->manager->persist($root);
//         $this->manager->persist($food);
//         $this->manager->persist($fruits);
//         $this->manager->persist($vegetables);
//         $this->manager->persist($carrots);

//         $this->manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }
}