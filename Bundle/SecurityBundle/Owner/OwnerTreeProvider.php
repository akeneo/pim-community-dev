<?php

namespace Oro\Bundle\SecurityBundle\Owner;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\EntityManager;

/**
 * Class OwnerTreeProvider
 * @package Oro\Bundle\SecurityBundle\Owner
 */
class OwnerTreeProvider
{
    const CACHE_NAMESPACE = 'OwnerTree';
    const CACHE_KEY = 'OwnerTreeData';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var OwnerTree
     */
    protected $tree;

    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @param EntityManager $em
     * @param CacheProvider $cache
     */
    public function __construct(EntityManager $em, CacheProvider $cache)
    {
        $this->cache = $cache;
        $this->em = $em;
    }

    /**
     * @return OwnerTree
     */
    public function getTree()
    {
        $this->ensureTreeLoaded();

        return $this->tree;
    }

    /**
     * Clear the owner tree cache
     */
    public function clear()
    {
        $this->cache->deleteAll();
    }

    /**
     * Warmup owner tree cache
     */
    public function warmUpCache()
    {
        $this->ensureTreeLoaded();
    }

    /**
     * Makes sure that tree data
     */
    protected function ensureTreeLoaded()
    {
        if ($this->tree === null) {
            $treeData = null;
            if ($this->cache) {
                $treeData = $this->cache->fetch(self::CACHE_KEY);
            }
            if (!$treeData && $this->checkDatabase()) {
                $treeData = new OwnerTree();
                $this->fillTree($treeData);

                if ($this->cache) {
                    $this->cache->save(self::CACHE_KEY, $treeData);
                }
            }

            $this->tree = $treeData;
        }
    }

    /**
     * @param OwnerTree $tree
     */
    protected function fillTree(OwnerTree $tree)
    {
        $users = $this->em->getRepository('Oro\Bundle\UserBundle\Entity\User')->findAll();
        $businessUnits = $this->em->getRepository('Oro\Bundle\OrganizationBundle\Entity\BusinessUnit')->findAll();

        foreach ($businessUnits as $businessUnit) {
            /** @var \Oro\Bundle\OrganizationBundle\Entity\BusinessUnit $businessUnit */
            $tree->addBusinessUnit($businessUnit->getId(), $businessUnit->getOrganization()->getId());
            if ($businessUnit->getOwner()) {
                $tree->addBusinessUnitRelation($businessUnit->getId(), $businessUnit->getOwner()->getId());
            }
        }

        foreach ($users as $user) {
            /** @var \Oro\Bundle\UserBundle\Entity\User $user */
            $tree->addUser($user->getId(), $user->getOwner()->getId());
            foreach ($user->getBusinessUnits() as $businessUnit) {
                $tree->addUserBusinessUnit($user->getId(), $businessUnit->getId());
            }
        }
    }

    /**
     * Check if user table exists in db
     *
     * @return bool
     */
    protected function checkDatabase()
    {
        $tableName  = $this->em->getClassMetadata('Oro\Bundle\UserBundle\Entity\User')->getTableName();
        $result = false;
        try {
            $conn = $this->em->getConnection();

            if (!$conn->isConnected()) {
                $this->em->getConnection()->connect();
            }

            $result = $conn->isConnected() && (bool)array_intersect(
                array($tableName),
                $this->em->getConnection()->getSchemaManager()->listTableNames()
            );
        } catch (\PDOException $e) {
        }

        return $result;
    }
}
