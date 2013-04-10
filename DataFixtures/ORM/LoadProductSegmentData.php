<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Pim\Bundle\ProductBundle\Entity\ProductSegment;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Load data for classification tree
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadProductSegmentData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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

        // get products
        $product1 = $this->getReference('product-sku-1');
        $product2 = $this->getReference('product-sku-2');
        $product3 = $this->getReference('product-sku-3');
        $product4 = $this->getReference('product-sku-4');
        $product5 = $this->getReference('product-sku-5');

        // create trees
        $treeCatalog     = $this->createSegment('Master Catalog');
        $treeCollections = $this->createSegment('Collections');
        $treeColors      = $this->createSegment('Colors');
        $treeSales       = $this->createSegment('Europe Sales Catalog');

        // enrich master catalog with segments
        $nodeBooks = $this->createSegment('Books', $treeCatalog);
        $nodeComputers = $this->createSegment('Computers', $treeCatalog);
        $nodeDesktops = $this->createSegment('Desktops', $nodeComputers);
        $nodeNotebooks = $this->createSegment('Notebooks', $nodeComputers);
        $nodeAccessories = $this->createSegment('Accessories', $nodeComputers);
        $nodeGames = $this->createSegment('Games', $nodeComputers);
        $nodeSoftware = $this->createSegment('Software', $nodeComputers);
        $nodeClothing = $this->createSegment('Apparels & Shoes', $treeCatalog);

        $nodeShirts = $this->createSegment('Shirts', $nodeClothing, array($product5));
        $nodeJeans  = $this->createSegment('Jeans', $nodeClothing, array($product3, $product4));
        $nodeShoes  = $this->createSegment('Shoes', $nodeClothing, array($product1, $product2, $product3));

        $this->manager->flush();

        // translate data
        $locale = 'fr_FR';
        $this->translate($treeCatalog, $locale, 'Catalogue Principal');
        $this->translate($treeCollections, $locale, 'Collections');
        $this->translate($treeColors, $locale, 'Couleurs');
        $this->translate($treeSales, $locale, 'Catalogue des ventes européennes');

        $this->translate($nodeBooks, $locale, 'Livres');
        $this->translate($nodeComputers, $locale, 'Ordinateurs');
        $this->translate($nodeDesktops, $locale, 'Ordinateurs de bureau');
        $this->translate($nodeNotebooks, $locale, 'Ordinateur portable');
        $this->translate($nodeAccessories, $locale, 'Accessoires');
        $this->translate($nodeGames, $locale, 'Jeux');
        $this->translate($nodeSoftware, $locale, 'Logiciels');
        $this->translate($nodeClothing, $locale, 'Habillements & Chaussures');

        $this->translate($nodeShirts, $locale, 'Chemises');
        $this->translate($nodeJeans, $locale, 'Jeans');
        $this->translate($nodeShoes, $locale, 'Chaussures');

        $this->manager->flush();
    }

    /**
     * Translate a segment
     * @param ProductSegment $segment Segment
     * @param string         $locale  Locale used
     * @param string         $title   Title translated in locale value linked
     */
    protected function translate(ProductSegment $segment, $locale, $title)
    {
        $segment->setTranslatableLocale($locale);
        $segment->setTitle($title);
        $this->manager->persist($segment);
    }

    /**
     * Create a Segment entity
     *
     * @param string         $title    Title of the segment
     * @param ProductSegment $parent   Parent segment
     * @param array          $products Products that should be associated to this segment
     *
     * @return ItemSegment
     */
    protected function createSegment($title, $parent = null, $products = array())
    {
        $segment = new ProductSegment();
        $segment->setCode($title);
        $segment->setTitle($title);
        $segment->setParent($parent);

        foreach ($products as $product) {
            $segment->addProduct($product);
        }

        $this->manager->persist($segment);

        return $segment;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 4;
    }
}
