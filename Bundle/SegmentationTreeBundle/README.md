SegmentationTreeBundle
======================

Allow to organize items in hierarchical segments  (Replace ClassificationTree)

Install
=======

To install for dev :

```bash
$ php composer.phar update --dev
```

To use as dependency, use composer and add bundle in your AppKernel :

```yaml
    "require": {
        [...]
        "oro/SegmentationTreeBundle": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:laboro/SegmentationTreeBundle.git",
            "branch": "master"
        }
    ]
```


Classes / Concepts
==================

(dependencies : just doctrine entities but manager is agnostic)



Example of usage
================

Define segmentation tree manager as service
-------------------------------------------
In your services.yml file, define your service like this :
```yaml
services:
    segmentation_tree_manager:
        class:     %oro_segmentation_tree.segment_manager.class%
        arguments: [@doctrine.orm.entity_manager, %segment_class%]
```

%segment_class% must be replace by the fully-qualified class name (FQCN).


Implement segmentation tree with simple doctrine entity
-------------------------------------------------------
To implementation segmentation tree, you just have to override AbstractSegment class by MyClassSegment and define OneToMany or ManyToMany association mapping.
Here an example to link with an Item entity (ManyToMany association) :

```php
# ItemSegment.php
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A concrete Segment class allowing to organize
 * a simple Item class into trees
 *
 * @ORM\Entity(repositoryClass="Oro\Bundle\SegmentationTreeBundle\Entity\Repository\SegmentRepository")
 * @ORM\Table(name="item_segment")
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

```

Translate segments
------------------
To translate node, we used doctrine extension so the example below is based on (assuming that the extension is already installed and configured).


```php
#ItemSegment.php

// ...
use Gedmo\Translatable\Translatable;

/**
 * A concrete Segment class allowing to organize
 * a simple Item class into trees
 *
 * @ORM\Entity(repositoryClass="Oro\Bundle\SegmentationTreeBundle\Entity\Repository\SegmentRepository")
 * @ORM\Table(name="item_segment")
 * @Gedmo\Tree(type="nested")
 * @Gedmo\TranslationEntity(class="ItemSegmentTranslation.php")
 */
class ItemSegment extends AbstractSegment implements Translatable
{
    // ... list of already defined property (see example above)

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=64)
     * @Gedmo\Translatable
     */
    protected $title;

    /**
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     *
     * @var string $locale
     *
     * @Gedmo\Locale
     */
    protected $locale;
    
    // ... list of already defined method (see example above)

    /**
     * Define locale used by entity
     *
     * @param string $locale
     *
     * @return AbstractSegment
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
}
```
