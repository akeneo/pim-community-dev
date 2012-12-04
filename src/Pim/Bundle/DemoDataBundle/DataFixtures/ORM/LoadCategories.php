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
 * Execute with "php app/console doctrine:fixtures:load"
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

        $computer = $this->createCategory('computers');

        $laptop  = $this->createCategory('laptop', $computer);
        $this->createCategory('notebook', $laptop);

        $this->createCategory('tablet', $computer);
        $this->createCategory('desktop', $computer);
        $this->createCategory('server', $computer);

        $components = $this->createCategory('components');

        $graphics   = $this->createCategory('video cards', $components);
        $this->createCategory('nvidia', $graphics);
        $this->createCategory('ati', $graphics);
        $this->createCategory('waterblocks', $graphics);

        $dataStore = $this->createCategory('data storage', $components);
        $this->createCategory('ssd', $dataStore);
        $this->createCategory('internal hard drive', $dataStore);
        $this->createCategory('external hard drive', $dataStore);
        $this->createCategory('storage media cases', $dataStore);
        $this->createCategory('usb keys', $dataStore);
        $disks = $this->createCategory('Disks', $dataStore);

        $dvd = $this->createCategory('DVD', $disks);
        $cd  = $this->createCategory('CD', $disks);

        $this->manager->flush();
    }

    /**
     * Create a Category entity
     * @param string                                            $title  Title of the category
     * @param \Pim\Bundle\CatalogTaxinomyBundle\Entity\Category $parent parent category
     *
     * @return \Pim\Bundle\CatalogTaxinomyBundle\Entity\Category
     */
    protected function createCategory($title, $parent = null)
    {
        $category = new Category();
        $category->setTitle($title);
        $category->setParent($parent);

        $this->manager->persist($category);

        return $category;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }
}