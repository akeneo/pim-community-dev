<?php
namespace Pim\Bundle\SegmentationTreeBundle\Tests\Functional\Entity;

use Pim\Bundle\SegmentationTreeBundle\Entity\AbstractSegment;
use Pim\Bundle\SegmentationTreeBundle\Tests\Functional\Entity\Item;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A concrete Segment class allowing to organize
 * a simple Item class into trees
 *
 *
 * @ORM\Entity(repositoryClass="Pim\Bundle\SegmentationTreeBundle\Entity\Repository\SegmentRepository")
 * @ORM\Table(name="oro_segmentationtree_tests_itemsegment")
 * @Gedmo\Tree(type="nested")
 */
class ItemSegment extends AbstractSegment
{
    /**
     * @var ItemSegment $parent
     *
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="ItemSegment", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="ItemSegment", mappedBy="parent", cascade={"persist"})
     * @ORM\OrderBy({"left" = "ASC"})
     */
    protected $children;

    /**
     * @ORM\ManyToMany(targetEntity="Item")
     * @ORM\JoinTable(
     *     name="oro_segmentationtree_tests_segments_items",
     *     joinColumns={@ORM\JoinColumn(name="segment_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="item_id", referencedColumnName="id")}
     * )
     **/
    protected $items;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->items = new ArrayCollection();
    }

    /**
     * Add item to this segment node
     *
     * @param Item $item
     *
     * @return ItemSegment
     */
    public function addItem(Item $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Remove item from this segment node
     *
     * @param Item $item
     *
     * @return ItemSegment
     */
    public function removeItem(Item $item)
    {
        $this->items->removeElement($item);

        return $this;
    }

    /**
     * Get items from this segment node
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItems()
    {
        return $this->items;
    }
}
