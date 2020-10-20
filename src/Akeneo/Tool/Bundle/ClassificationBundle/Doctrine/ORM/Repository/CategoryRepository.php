<?php

namespace Akeneo\Tool\Bundle\ClassificationBundle\Doctrine\ORM\Repository;

use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * Category repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryRepository extends NestedTreeRepository implements
    IdentifiableObjectRepositoryInterface,
    CategoryRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCategoriesByIds(array $categoriesIds = [])
    {
        if (empty($categoriesIds)) {
            return new ArrayCollection();
        }

        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('node')
            ->from($config['useObjectClass'], 'node')
            ->where('node.id IN (:categoriesIds)');

        $qb->setParameter('categoriesIds', $categoriesIds);

        $result = $qb->getQuery()->getResult();
        $result = new ArrayCollection($result);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoriesByCodes(array $categoriesCodes = [])
    {
        if (empty($categoriesCodes)) {
            return new ArrayCollection();
        }

        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('node')
            ->from($config['useObjectClass'], 'node')
            ->where('node.code IN (:categoriesCodes)');

        $qb->setParameter('categoriesCodes', $categoriesCodes);

        $result = $qb->getQuery()->getResult();
        $result = new ArrayCollection($result);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getTreeFromParents(array $parentsIds)
    {
        if (count($parentsIds) === 0) {
            return [];
        }

        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('node')
            ->from($config['useObjectClass'], 'node')
            ->where('node.id IN (:parentsIds) OR node.parent IN (:parentsIds)')
            ->orderBy('node.left');

        $qb->setParameter('parentsIds', $parentsIds);

        $nodes = $qb->getQuery()->getResult();

        return $this->buildTreeNode($nodes);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilledTree(CategoryInterface $root, Collection $categories)
    {
        $parentsIds = [];
        foreach ($categories as $category) {
            $categoryParentsIds = [];
            $path = $this->getPath($category);

            if ($path[0]->getId() === $root->getId()) {
                foreach ($path as $pathItem) {
                    $categoryParentsIds[] = $pathItem->getId();
                }
            }
            $parentsIds = array_merge($parentsIds, $categoryParentsIds);
        }
        $parentsIds = array_unique($parentsIds);

        return $this->getTreeFromParents($parentsIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllChildrenIds(CategoryInterface $parent, $includeNode = false)
    {
        $categoryQb = $this->getAllChildrenQueryBuilder($parent, $includeNode);
        $rootAlias = current($categoryQb->getRootAliases());
        $rootEntity = current($categoryQb->getRootEntities());
        $categoryQb->select($rootAlias.'.id');
        $categoryQb->resetDQLPart('from');
        $categoryQb->from($rootEntity, $rootAlias, $rootAlias.'.id');

        return array_keys($categoryQb->getQuery()->execute([], AbstractQuery::HYDRATE_ARRAY));
    }

    /**
     * {@inheritdoc}
     */
    public function getAllChildrenCodes(CategoryInterface $parent, $includeNode = false)
    {
        $categoryQb = $this->getAllChildrenQueryBuilder($parent, $includeNode);
        $rootAlias = current($categoryQb->getRootAliases());
        $rootEntity = current($categoryQb->getRootEntities());
        $categoryQb->select($rootAlias.'.code');
        $categoryQb->resetDQLPart('from');
        $categoryQb->from($rootEntity, $rootAlias, $rootAlias.'.id');

        $categories = $categoryQb->getQuery()->execute(null, AbstractQuery::HYDRATE_SCALAR);
        $codes = [];
        foreach ($categories as $category) {
            $codes[] = $category['code'];
        }

        return $codes;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryIdsByCodes(array $categoriesCodes)
    {
        if (empty($categoriesCodes)) {
            return [];
        }

        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('node.id')
            ->from($config['useObjectClass'], 'node')
            ->where('node.code IN (:categoriesCodes)');

        $qb->setParameter('categoriesCodes', $categoriesCodes);

        $categories = $qb->getQuery()->execute(null, AbstractQuery::HYDRATE_SCALAR);
        $ids = [];
        foreach ($categories as $category) {
            $ids[] = (int) $category['id'];
        }

        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenByParentId($parentId)
    {
        $parent = $this->find($parentId);

        return $this->getChildren($parent, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenGrantedByParentId(CategoryInterface $parent, array $grantedCategoryIds = [])
    {
        return $this->getChildrenQueryBuilder($parent, true)
            ->andWhere('node.id IN (:ids)')
            ->setParameter('ids', $grantedCategoryIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenTreeByParentId($parentId, $selectNodeId = false, array $grantedCategoryIds = [])
    {
        $children = [];

        if ($selectNodeId === false) {
            $parent = $this->find($parentId);
            $children = $this->childrenHierarchy($parent);
        } else {
            $selectNode = $this->find($selectNodeId);
            if ($selectNode != null) {
                $meta = $this->getClassMetadata();
                $config = $this->listener->getConfiguration($this->_em, $meta->name);

                $selectPath = $this->getPath($selectNode);
                $parent = $this->find($parentId);
                $qb = $this->getNodesHierarchyQueryBuilder($parent);

                // Remove the node itself from his ancestor
                array_pop($selectPath);

                $ancestorsIds = [];

                foreach ($selectPath as $ancestor) {
                    $ancestorsIds[] = $ancestor->getId();
                }

                $qb->andWhere(
                    $qb->expr()->in('node.' . $config['parent'], $ancestorsIds)
                );

                if (!empty($grantedCategoryIds)) {
                    $qb->andWhere('node.id IN (:ids)')
                        ->setParameter('ids', $grantedCategoryIds);
                }

                $nodes = $qb->getQuery()->getResult();
                $children = $this->buildTreeNode($nodes);
            }
        }

        return $children;
    }

    /**
     * {@inheritdoc}
     */
    public function buildTreeNode(array $nodes)
    {
        $vectorMap = [];
        $tree = [];
        $childrenIndex = $this->repoUtils->getChildrenIndex();

        foreach ($nodes as $node) {
            if (!isset($vectorMap[$node->getId()])) {
                // Node does not exist, and none of his children has
                // already been in the loop, so we create it.
                $vectorMap[$node->getId()] = [
                    'item'         => $node,
                    $childrenIndex => []
                ];
            } else {
                // Node already existing in the map because a child has been
                // added to his children array. We still need to add the node
                // itself, as only its children property has been created.
                $vectorMap[$node->getId()]['item'] = $node;
            }

            if ($node->getParent() != null) {
                if (!isset($vectorMap[$node->getParent()->getId()])) {
                    // The parent does not exist in the map, create its
                    // children property
                    $vectorMap[$node->getParent()->getId()] = [
                        $childrenIndex => []
                    ];
                }

                $vectorMap[$node->getParent()->getId()][$childrenIndex][] =& $vectorMap[$node->getId()];
            } else {
                $tree[$node->getId()] =& $vectorMap[$node->getId()];
            }
        }

        if (empty($tree)) {
            // No node found with getParent() == null, meaning the absolute tree
            // root was not part of the set. We try to find the lowest level nodes
            // or a node without item part, meaning that it's a referenced parent but without
            // the node present itself in the set
            $nodeIt = 0;
            $foundItemLess = false;
            $nodeIds = array_keys($vectorMap);
            $nodesByLevel = [];

            while ($nodeIt < count($nodeIds) && !$foundItemLess) {
                $nodeId = $nodeIds[$nodeIt];
                $nodeEntry = $vectorMap[$nodeId];

                if (isset($nodeEntry['item'])) {
                    //$nodesByLevel[$nodeEntry['item']->getLevel()][] = $nodeIds[$i];
                } else {
                    $tree =& $vectorMap[$nodeId][$childrenIndex];
                }
                $nodeIt++;
            }
            // $tree still empty there, means we need to pick the lowest level nodes as tree roots
            if (empty($tree)) {
                $lowestLevel = min(array_keys($nodesByLevel));
                foreach ($nodesByLevel[$lowestLevel] as $nodeId) {
                    $tree[$nodeId] =& $vectorMap[$nodeId];
                }
            }
        }

        return $tree;
    }

    /**
     * {@inheritdoc}
     */
    public function getTrees()
    {
        return $this->getChildren(null, true, 'created', 'DESC');
    }

    /**
     * {@inheritdoc}
     */
    public function getGrantedTrees(array $grantedCategoryIds = [])
    {
        $qb = $this->getChildrenQueryBuilder(null, true, 'created', 'DESC');
        $result = $qb
            ->andWhere('node.id IN (:ids)')
            ->setParameter('ids', $grantedCategoryIds)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isAncestor(CategoryInterface $parentNode, CategoryInterface $childNode)
    {
        $sameRoot = $parentNode->getRoot() === $childNode->getRoot();

        $isAncestor = $childNode->getLeft() > $parentNode->getLeft()
                      && $childNode->getRight() < $parentNode->getRight();

        return $sameRoot && $isAncestor;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderedAndSortedByTreeCategories()
    {
        $queryBuilder = $this->createQueryBuilder('c');
        $queryBuilder = $queryBuilder->orderBy('c.root')->addOrderBy('c.left');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Shortcut to get all children query builder
     *
     * @param CategoryInterface $category    the requested node
     * @param bool              $includeNode true to include actual node in query result
     *
     * @return QueryBuilder
     */
    protected function getAllChildrenQueryBuilder(CategoryInterface $category, $includeNode = false)
    {
        return $this->getChildrenQueryBuilder($category, false, null, 'ASC', $includeNode);
    }
}
