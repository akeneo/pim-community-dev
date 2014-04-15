<?php
namespace Pim\Bundle\SegmentationTreeBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * Service class to manage segments node and tree
 *
 *
 */
class SegmentManager
{
    /**
     * Storage manager
     *
     * @var ObjectManager
     */
    protected $storageManager;

    /**
     * Class name for managed segment
     *
     * @var string
     */
    protected $segmentName;

    /**
     * Constructor
     *
     * @param ObjectManager $storageManager Storage manager
     * @param String        $segmentName    Segment class name
     */
    public function __construct(ObjectManager $storageManager, $segmentName)
    {
        $this->storageManager = $storageManager;
        $this->segmentName = $segmentName;
    }

    /**
     * Return storage manager
     *
     * @return ObjectManager
     */
    public function getStorageManager()
    {
        return $this->storageManager;
    }

    /**
     * Get a new segment instance
     *
     * @return CategoryInterface
     *
     */
    public function getSegmentInstance()
    {
        $segmentClassName = $this->getSegmentName();

        return new $segmentClassName;
    }

    /**
     * Return segment class name (mainly used in Doctrine context)
     *
     * @return String segment class name
     */
    public function getSegmentName()
    {
        return $this->segmentName;
    }

    /**
     * Return the entity repository reponsible for the segment
     *
     * @return CategoryRepository
     */
    public function getEntityRepository()
    {
        return $this->getStorageManager()->getRepository($this->getSegmentName());
    }


    /**
     * Get all direct children for a parent segment id.
     * If the $selectNodeId is provided, all the children
     * level needed to provides the selectNode are returned
     *
     * @param integer $parentId
     * @param integer $selectNodeId
     *
     * @return ArrayCollection
     */
    public function getChildren($parentId, $selectNodeId = false)
    {
        $children = array();

        $entityRepository = $this->getEntityRepository();

        if ($selectNodeId === false) {
            $children = $entityRepository->getChildrenByParentId($parentId);
        } else {
            $children = $entityRepository->getChildrenTreeByParentId($parentId, $selectNodeId);
        }

        return $children;


    }

    /**
     * Search segments by criterias
     * @param integer $treeRootId Tree root id
     * @param array   $criterias  criterias for search query
     *
     * @return ArrayCollection
     */
    public function search($treeRootId, $criterias)
    {
        return $this->getEntityRepository()->search($treeRootId, $criterias);
    }

    /**
     * Remove a segment by its id
     *
     * @param integer $segmentId Id of segment to remove
     */
    public function removeById($segmentId)
    {
        $repo = $this->getEntityRepository();
        $segment = $repo->find($segmentId);

        $this->remove($segment);
    }

    /**
     * Remove a segment object
     *
     * @param CategoryInterface $segment
     */
    public function remove(CategoryInterface $segment)
    {
        $this->getStorageManager()->remove($segment);
    }

    /**
     * Rename a segment
     *
     * @param integer $segmentId Segment id
     * @param string  $code      New code for segment
     */
    public function rename($segmentId, $code)
    {
        $repo = $this->getEntityRepository();
        $segment = $repo->find($segmentId);

        $segment->setCode($code);

        $this->getStorageManager()->persist($segment);
    }


    /**
     * Move a segment to another parent
     *
     * @param integer $segmentId     Segment to move
     * @param integer $parentId      Parent segment where to move
     * @param integer $prevSiblingId Position the node after the passed
     * sibling. If no sibling is provided, the node became the first child node
     */
    public function move($segmentId, $parentId, $prevSiblingId)
    {
        $repo = $this->getEntityRepository();
        $segment = $repo->find($segmentId);
        $parent = $repo->find($parentId);
        $prevSibling = null;

        $segment->setParent($parent);

        if (!empty($prevSiblingId)) {
            $prevSibling = $repo->find($prevSiblingId);
        }

        if (is_object($prevSibling)) {
            $repo->persistAsNextSiblingOf($segment, $prevSibling);
        } else {
            $repo->persistAsFirstChildOf($segment, $parent);
        }
    }

    /**
     * Recursive copy
     * @param CategoryInterface $segment Segment to be copied
     * @param CategoryInterface $parent  Parent segment
     *
     * @return CategoryInterface
     * FIXME: copy relationship states as well and all attributes
     */
    public function copyNode(CategoryInterface $segment, $parent)
    {
        $newSegment = $this->getSegmentInstance();
        $newSegment->setCode($segment->getCode());
        $newSegment->setParent($parent);

        // copy children by recursion
        foreach ($segment->getChildren() as $child) {
            $newChild = $this->copyNode($child, $newSegment);
            $newSegment->addChild($newChild);

            $this->getStorageManager()->persist($newSegment);
        }

        return $newSegment;
    }

    /**
     * Get all tree root. They are nodes without a parent node
     *
     * @return ArrayCollection The root nodes
     */
    public function getTrees()
    {
        $entityRepository = $this->getEntityRepository();

        return $entityRepository->getChildrenByParentId(null);

    }

    /**
     * Get all segments of a tree by its root
     *
     * @param CategoryInterface $treeRoot Tree root node
     *
     * @return ArrayCollection The tree's nodes
     */
    public function getTreeSegments(CategoryInterface $treeRoot)
    {
        $repo = $this->getEntityRepository();
        $treeRootId = $treeRoot->getId();

        return $repo->findBy(array('root' => $treeRootId));
    }

    /**
     * Create a new tree by creating a its root node
     *
     * @param string $code
     *
     * @return AbsractSegment
     */
    public function createTree($code)
    {
        $rootSegment = $this->getSegmentInstance();
        $rootSegment->setParent(null);
        $rootSegment->setCode($code);
        $this->getStorageManager()->persist($rootSegment);

        return $rootSegment;
    }

    /**
     * Remove a new tree by its root segment
     *
     * @param CategoryInterface $rootSegment
     */
    public function removeTree(CategoryInterface $rootSegment)
    {
        $rootSegment->setParent(null);
        $this->getStorageManager()->remove($rootSegment);
    }

    /**
     * Remove a new tree by its root node id
     *
     * @param int $rootSegmentId
     */
    public function removeTreeById($rootSegmentId)
    {
        $repo = $this->getEntityRepository();
        $rootSegment = $repo->find($rootSegmentId);

        $this->removeTree($rootSegment);

    }

    /**
     * Check is a parent node is an ancestor of a child node
     *
     * @param Segment $parentNode
     * @param Segment $childNode
     */
    public function isAncestor(CategoryInterface $parentNode, CategoryInterface $childNode)
    {
        $childPath = $this->getEntityRepository()->getPath($childNode);
        //Removing last part of the path as it's the node itself
        //which cannot be is own ancestor
        array_pop($childPath);
        $i = 0;
        $parentFound = false;

        while ($i < count($childPath) && (!$parentFound)) {
            $parentFound = ($childPath[$i]->getId() === $parentNode->getId());
            $i++;
        }

        return $parentFound;
    }
}
