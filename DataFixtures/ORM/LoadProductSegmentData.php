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

        // create products
        $product1 = $this->getReference('product-sku-1');
        $product2 = $this->getReference('product-sku-2');
        $product3 = $this->getReference('product-sku-3');
        $product4 = $this->getReference('product-sku-4');
        $product5 = $this->getReference('product-sku-5');

        // create trees and segments linked
        $treeRoot1 = $this->createSegment('Tree One');

        $products1 = array($product1, $product2, $product3);
        $segment1 = $this->createSegment('Segment One', $treeRoot1, $products1);

        $treeRoot2 = $this->createSegment('Tree Two');
        $segment2 = $this->createSegment('Segment Two', $treeRoot2);

        $products2 = array($product3, $product4, $product5);
        $segment3 = $this->createSegment('Segment Three', $segment2, $products2);

        $segment4 = $this->createSegment('Segment Four', $segment2);
        $segment5 = $this->createSegment('Segment Five', $segment4);
        $segment6 = $this->createSegment('Segment Six', $segment4);

        $this->manager->flush();

        // translate trees and segments
        $locale = 'fr_FR';
        $this->translate($treeRoot1, $locale, 'Arbre un');
        $this->translate($segment1, $locale, 'Segment un');

        $this->translate($treeRoot2, $locale, 'Arbre deux');
        $this->translate($segment2, $locale, 'Segment deux');

        $this->translate($segment3, $locale, 'Segment trois');
        $this->translate($segment4, $locale, 'Segment quatre');
        $this->translate($segment5, $locale, 'Segment cinq');
        $this->translate($segment6, $locale, 'Segment six');

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
