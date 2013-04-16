<?php
namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Pim\Bundle\ProductBundle\Entity\ProductSegmentTranslation;

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
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
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
        $treeCatalog     = $this->createSegment('Master Catalog (default)');
        $treeCollections = $this->createSegment('Collections (default)');
        $treeColors      = $this->createSegment('Colors (default)');
        $treeSales       = $this->createSegment('Europe Sales Catalog (default)');

        // enrich master catalog with segments
        $nodeBooks = $this->createSegment('Books (default)', $treeCatalog);
        $nodeComputers = $this->createSegment('Computers (default)', $treeCatalog);
        $nodeDesktops = $this->createSegment('Desktops (default)', $nodeComputers);
        $nodeNotebooks = $this->createSegment('Notebooks (default)', $nodeComputers);
        $nodeAccessories = $this->createSegment('Accessories (default)', $nodeComputers);
        $nodeGames = $this->createSegment('Games (default)', $nodeComputers);
        $nodeSoftware = $this->createSegment('Software (default)', $nodeComputers);
        $nodeClothing = $this->createSegment('Apparels & Shoes (default)', $treeCatalog);

        $nodeShirts = $this->createSegment('Shirts (default)', $nodeClothing, array($product5));
        $nodeJeans  = $this->createSegment('Jeans (default)', $nodeClothing, array($product3, $product4));
        $nodeShoes  = $this->createSegment('Shoes (default)', $nodeClothing, array($product1, $product2, $product3));

        // translate data in en_US
        $locale = 'en_US';
        $this->translate($treeCatalog, $locale, 'Master Catalog');
        $this->translate($treeCollections, $locale, 'Collections');
        $this->translate($treeColors, $locale, 'Colors');
        $this->translate($treeSales, $locale, 'Europe Sales Catalog');

        $this->translate($nodeBooks, $locale, 'Books');
        $this->translate($nodeComputers, $locale, 'Computers');
        $this->translate($nodeDesktops, $locale, 'Desktops');
        $this->translate($nodeNotebooks, $locale, 'Notebooks');
        $this->translate($nodeAccessories, $locale, 'Accessories');
        $this->translate($nodeGames, $locale, 'Games');
        $this->translate($nodeSoftware, $locale, 'Software');
        $this->translate($nodeClothing, $locale, 'Apparels & Shoes');

        $this->translate($nodeShirts, $locale, 'Shirts');
        $this->translate($nodeJeans, $locale, 'Jeans');
        $this->translate($nodeShoes, $locale, 'Shoes');

        $this->manager->flush();

        // translate data in fr_FR
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
     *
     * @param ProductSegment $segment Segment
     * @param string         $locale  Locale used
     * @param string         $title   Title translated in locale value linked
     */
    protected function translate(ProductSegment $segment, $locale, $title)
    {
        $translation = $this->createTranslation($segment, $locale, $title);
        $segment->addTranslation($translation);

        $this->manager->persist($segment);
    }

    /**
     * Create a translation entity
     *
     * @param AttributeGroup $entity AttributeGroup entity
     * @param string         $locale Locale used
     * @param string         $title  Title translated in locale value linked
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductSegmentTranslation
     */
    protected function createTranslation($entity, $locale, $title)
    {
        $translation = new ProductSegmentTranslation();
        $translation->setContent($title);
        $translation->setField('title');
        $translation->setForeignKey($entity);
        $translation->setLocale($locale);
        $translation->setObjectClass('Pim\Bundle\ProductBundle\Entity\ProductSegment');

        return $translation;
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

        $translation = $this->createTranslation($segment, 'default', $title);
        $segment->addTranslation($translation);

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
