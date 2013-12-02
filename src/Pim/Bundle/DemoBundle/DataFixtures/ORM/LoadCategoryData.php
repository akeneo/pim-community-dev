<?php

namespace Pim\Bundle\DemoBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Pim\Bundle\CatalogBundle\Entity\CategoryTranslation;
use Pim\Bundle\CatalogBundle\Entity\Category;

/**
 * Load data for category tree
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoadCategoryData extends AbstractDemoFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        if ($this->isEnabled() === false) {
            return;
        }

        $this->manager = $manager;

        // get products
        $product1 = $this->getReference('product.sku-001');
        $product2 = $this->getReference('product.sku-002');
        $product3 = $this->getReference('product.sku-003');
        $product4 = $this->getReference('product.sku-004');
        $product5 = $this->getReference('product.sku-005');

        // create trees
        $treeCatalog     = $this->getCategory('default');
        $treeCollections = $this->createCategory('Collections');
        $treeColors      = $this->createCategory('Colors');
        $treeSales       = $this->createCategory('Europe Sales Catalog');

        // enrich master catalog with categories
        $nodeBooks       = $this->createCategory('Books', $treeCatalog);
        $nodeComputers   = $this->createCategory('Computers', $treeCatalog);
        $nodeDesktops    = $this->createCategory('Desktops', $nodeComputers);
        $nodeNotebooks   = $this->createCategory('Notebooks', $nodeComputers);
        $nodeAccessories = $this->createCategory('Accessories', $nodeComputers);
        $nodeGames       = $this->createCategory('Games', $nodeComputers);
        $nodeSoftware    = $this->createCategory('Software', $nodeComputers);
        $nodeClothing    = $this->createCategory('Apparels & Shoes', $treeCatalog);

        $nodeShirts = $this->createCategory('Shirts', $nodeClothing, array($product5));
        $nodeJeans  = $this->createCategory('Jeans', $nodeClothing, array($product3, $product4));
        $nodeShoes  = $this->createCategory('Shoes', $nodeClothing, array($product1, $product2, $product3));
        $nodeFShoes  = $this->createCategory('Shoes Male', $nodeShoes, array($product3));
        $nodeMShoes  = $this->createCategory('Shoes Female', $nodeShoes, array($product2));

        $nodeClothingEu  = $this->createCategory('Apparels & Shoes (eu sales)', $treeSales);
        $nodeShirtsEu = $this->createCategory('Shirts (eu sales)', $nodeClothingEu, array($product5));
        $nodeJeansEu  = $this->createCategory('Jeans (eu sales)', $nodeClothingEu, array($product3, $product4));
        $nodeShoesEu  = $this->createCategory('Shoes (eu sales)', $nodeClothingEu, array($product1));

        // translate data in en_US
        $locale = $this->getReference('locale.en_US');
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
        $this->translate($nodeFShoes, $locale, 'Shoes Male');
        $this->translate($nodeMShoes, $locale, 'Shoes Female');

        $this->translate($nodeClothingEu, $locale, 'Apparels & Shoes in Europe Sales');
        $this->translate($nodeShirtsEu, $locale, 'Shirts');
        $this->translate($nodeJeansEu, $locale, 'Jeans');
        $this->translate($nodeShoesEu, $locale, 'Shoes');

        // translate data in fr_FR
        $locale = $this->getReference('locale.fr_FR');
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

        $this->translate($nodeClothingEu, $locale, 'Apparels & Shoes in Europe Sales in fr');
        $this->translate($nodeShirtsEu, $locale, 'Shirts in fr');
        $this->translate($nodeJeansEu, $locale, 'Jeans in fr');
        $this->translate($nodeShoesEu, $locale, 'Shoes in fr');

        $this->manager->flush();
    }

    /**
     * Translate a category
     *
     * @param Category $category Category
     * @param string   $locale   Locale used
     * @param string   $label    Label translated in locale value linked
     */
    protected function translate(Category $category, $locale, $label)
    {
        $translation = $this->createTranslation($category, $locale, $label);
        $category->addTranslation($translation);

        $this->manager->persist($category);
    }

    /**
     * Create a translation entity
     *
     * @param AttributeGroup $entity AttributeGroup entity
     * @param string         $locale Locale used
     * @param string         $label  Label translated in locale value linked
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\CategoryTranslation
     */
    protected function createTranslation($entity, $locale, $label)
    {
        $translation = new CategoryTranslation();
        $translation->setForeignKey($entity);
        $translation->setLocale($locale);
        $translation->setLabel($label);

        return $translation;
    }

    /**
     * Get category by code
     *
     * @param string $code
     *
     * @return Category
     */
    public function getCategory($code)
    {
        return $this->manager->getRepository('PimCatalogBundle:Category')->findOneByCode($code);
    }

    /**
     * Create a Category entity
     *
     * @param string   $code     Code of the category
     * @param Category $parent   Parent category
     * @param array    $products Products that should be associated to this category
     *
     * @return Category
     */
    protected function createCategory($code, $parent = null, $products = array())
    {
        $category = new Category();
        $category->setCode($this->prepareCode($code));
        $category->setParent($parent);

        foreach ($products as $product) {
            $category->addProduct($product);
        }

        $this->manager->persist($category);

        return $category;
    }

    /**
     * Convert human readable name into a valid entity code
     * @param string $code
     *
     * @return string
     */
    protected function prepareCode($code)
    {
        $code = str_replace(' ', '_', strtolower($code));

        return preg_replace('/__+/', '_', preg_replace('/[^a-zA-Z0-9_]/', '', $code));
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 150;
    }
}
