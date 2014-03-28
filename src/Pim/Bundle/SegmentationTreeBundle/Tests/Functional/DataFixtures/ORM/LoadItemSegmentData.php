<?php
namespace Pim\Bundle\SegmentationTreeBundle\Tests\Functional\DataFixtures\ORM;

use Pim\Bundle\SegmentationTreeBundle\Tests\Functional\Entity\Item;
use Pim\Bundle\SegmentationTreeBundle\Tests\Functional\Entity\ItemSegment;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Load tests items and items segment
 *
 */
class LoadItemSegmentData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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

        $item1 = $this->createItem('My item 1', 'A nice item (1)');
        $item2 = $this->createItem('My item 2', 'A nice item (2)');
        $item3 = $this->createItem('My item 3', 'A nice item (3)');
        $item4 = $this->createItem('My item 4', 'A nice item (4)');
        $item5 = $this->createItem('My item 5', 'A nice item (5)');

        $treeRoot1 = $this->createSegment('Tree One');

        $items1 = array($item1, $item2, $item3);
        $this->createSegment('Segment One', $treeRoot1, $items1);

        $treeRoot2 = $this->createSegment('Tree Two');
        $segment2 = $this->createSegment('Segment Two', $treeRoot2);

        $items2 = array($item3, $item4, $item5);
        $this->createSegment('Segment Three', $segment2, $items2);

        $segment4 = $this->createSegment('Segment Four', $segment2);
        $this->createSegment('Segment Five', $segment4);
        $this->createSegment('Segment Six', $segment4);

        $this->manager->flush();
    }

    /**
     * Create a Segment entity
     *
     * @param string      $code   Code of the segment
     * @param ItemSegment $parent Parent segment
     * @param array       $items  Items that should be associated to this segment
     *
     * @return ItemSegment
     */
    protected function createSegment($code, $parent = null, $items = array())
    {
        $segment = new ItemSegment();
        $segment->setCode($code);
        $segment->setParent($parent);

        foreach ($items as $item) {
            $segment->addItem($item);
        }

        $this->manager->persist($segment);

        return $segment;
    }

    /**
     * Create a Item entity
     * @param string $name        Name of the item
     * @param string $description Description of the item
     *
     * @return Item
     */
    protected function createItem($name, $description)
    {
        $item= new Item();
        $item->setName($name);
        $item->setDescription($description);

        $this->manager->persist($item);

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
