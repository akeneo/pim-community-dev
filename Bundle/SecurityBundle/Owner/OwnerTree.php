<?php

namespace Oro\Bundle\SecurityBundle\Owner;

/**
 * This class represents a tree of owners
 */
class OwnerTree
{
    /**
     * @var OwnerTreeEventListener
     */
    protected $eventListener;

    /**
     * An associative array to store owning organization of an user
     * key = userId
     * value = organizationId
     *
     * @var array
     */
    protected $userOwningOrganizationId;

    /**
     * An associative array to store owning organization of a business unit
     * key = businessUnitId
     * value = organizationId
     *
     * @var array
     */
    protected $businessUnitOwningOrganizationId;

    /**
     * An associative array to store owning business unit of an user
     * key = userId
     * value = businessUnitId
     *
     * @var array
     */
    protected $userOwningBusinessUnitId;

    /**
     * An associative array to store business units assigned to an user
     * key = userId
     * value = array of businessUnitId
     *
     * @var array
     */
    protected $userBusinessUnitIds;

    /**
     * An associative array to store subordinate business units
     * key = businessUnitId
     * value = array of businessUnitId
     *
     * @var array
     */
    protected $subordinateBusinessUnitIds;

    /**
     * Constructor
     *
     * @param OwnerTreeEventListener $eventListener
     */
    public function __construct(OwnerTreeEventListener $eventListener = null)
    {
        $this->clear();
        $this->eventListener = $eventListener;
    }

    /**
     * Gets the owning organization id for the given user id
     *
     * @param int|string $userId
     * @return int|string|null
     */
    public function getUserOrganizationId($userId)
    {
        if (isset($this->userOwningOrganizationId[$userId])) {
            return $this->userOwningOrganizationId[$userId];
        }

        return isset($this->userOwningBusinessUnitId[$userId])
            ? $this->loadBusinessUnitAndReturnUserOrganizationId($userId)
            : $this->loadUserAndReturnUserOrganizationId($userId);
    }

    /**
     * Gets the owning business unit id for the given user id
     *
     * @param int|string $userId
     * @return int|string|null
     */
    public function getUserBusinessUnitId($userId)
    {
        if (isset($this->userOwningBusinessUnitId[$userId])) {
            return $this->userOwningBusinessUnitId[$userId];
        }

        return $this->loadUserAndReturnUserBusinessUnitId($userId);
    }

    /**
     * Gets all business unit ids for the given user id
     *
     * @param int|string $userId
     * @return array of int|string
     */
    public function getUserBusinessUnitIds($userId)
    {
        if (isset($this->userBusinessUnitIds[$userId])) {
            return $this->userBusinessUnitIds[$userId];
        }

        return $this->loadUserAndReturnUserBusinessUnitIds($userId);
    }

    /**
     * Gets the owning organization id for the given business unit id
     *
     * @param int|string $businessUnitId
     * @return int|string|null
     */
    public function getBusinessUnitOrganizationId($businessUnitId)
    {
        if (isset($this->businessUnitOwningOrganizationId[$businessUnitId])) {
            return $this->businessUnitOwningOrganizationId[$businessUnitId];
        }

        return $this->loadBusinessUnitAndReturnOrganizationId($businessUnitId);
    }

    /**
     * Gets all subordinate business unit ids for the given business unit id
     *
     * @param int|string $businessUnitId
     * @return array of int|string
     */
    public function getSubordinateBusinessUnitIds($businessUnitId)
    {
        if (isset($this->subordinateBusinessUnitIds[$businessUnitId])) {
            return $this->subordinateBusinessUnitIds[$businessUnitId];
        }

        $organizationId = isset($this->businessUnitOwningOrganizationId[$businessUnitId])
            ? $this->businessUnitOwningOrganizationId[$businessUnitId]
            : $this->loadBusinessUnitAndReturnOrganizationId($businessUnitId);
        if ($organizationId !== null) {
            $this->loadBusinessUnitHierarchy($organizationId);
            if (isset($this->subordinateBusinessUnitIds[$businessUnitId])) {
                return $this->subordinateBusinessUnitIds[$businessUnitId];
            }
        }

        return null;
    }

    /**
     * Add the given business unit to the tree
     *
     * @param int|string $businessUnitId
     * @param int|string|null $owningOrganizationId
     */
    public function addBusinessUnit($businessUnitId, $owningOrganizationId)
    {
        $this->businessUnitOwningOrganizationId[$businessUnitId] = !empty($owningOrganizationId)
            ? $owningOrganizationId
            : null;

        foreach ($this->userOwningBusinessUnitId as $userId => $buId) {
            if ($buId === $businessUnitId) {
                $this->userOwningOrganizationId[$userId] = $owningOrganizationId;
            }
        }
    }

    /**
     * Add the given business unit hierarchy to the tree
     *
     * @param int|string $businessUnitId
     * @param array|null $treeOfChildBusinessUnitIds Example of the tree:
     * <code>
     *     array(
     *         buId_1 => array(
     *             buId_1_1 => array() or null,
     *             buId_1_2 => array(
     *                 buId_1_2_1 => array() or null
     *             ),
     *         buId_2 => array() or null
     *     )
     * </code>
     */
    public function addBusinessUnitHierarchy($businessUnitId, $treeOfChildBusinessUnitIds)
    {
        $this->removeBusinessUnitHierarchy($businessUnitId);

        $this->subordinateBusinessUnitIds[$businessUnitId] = array();
        if (!empty($treeOfChildBusinessUnitIds)) {
            foreach ($treeOfChildBusinessUnitIds as $childBuId => $subTree) {
                $this->subordinateBusinessUnitIds[$businessUnitId][] = $childBuId;
                $this->addBusinessUnitHierarchy($childBuId, $subTree);
                if (isset($this->subordinateBusinessUnitIds[$childBuId])) {
                    foreach ($this->subordinateBusinessUnitIds[$childBuId] as $buId) {
                        $this->subordinateBusinessUnitIds[$businessUnitId][] = $buId;
                    }
                }
            }
        }
    }

    /**
     * Add the given user to the tree
     *
     * @param int|string $userId
     * @param int|string|null $owningOrganizationId
     * @param int|string|null $owningBusinessUnitId
     * @param array|null $userBusinessUnitIds array of int|string
     */
    public function addUser(
        $userId,
        $owningOrganizationId,
        $owningBusinessUnitId,
        array $userBusinessUnitIds = null
    ) {
        $this->removeUser($userId);
        $this->userOwningOrganizationId[$userId] = !empty($owningOrganizationId)
            ? $owningOrganizationId
            : null;
        $this->userOwningBusinessUnitId[$userId] = !empty($owningBusinessUnitId)
            ? $owningBusinessUnitId
            : null;
        $this->userBusinessUnitIds[$userId] = $userBusinessUnitIds !== null
            ? $userBusinessUnitIds
            : array();
    }

    /**
     * Removes the given organization from the tree
     *
     * @param int|string $organizationId
     */
    public function removeOrganization($organizationId)
    {
        foreach ($this->userOwningOrganizationId as $userId => $orgId) {
            if ($organizationId === $orgId) {
                unset($this->userOwningOrganizationId[$userId]);
                $this->removeUser($userId);
            }
        }
        foreach ($this->businessUnitOwningOrganizationId as $businessUnitId => $orgId) {
            if ($organizationId === $orgId) {
                unset($this->businessUnitOwningOrganizationId[$businessUnitId]);
                $this->removeBusinessUnit($businessUnitId);
            }
        }
    }

    /**
     * Removes the given business unit from the tree
     *
     * @param int|string $businessUnitId
     */
    public function removeBusinessUnit($businessUnitId)
    {
        if (isset($this->businessUnitOwningOrganizationId[$businessUnitId])) {
            $this->resetUserOwningOrganizationId($this->businessUnitOwningOrganizationId[$businessUnitId]);
        }
        foreach ($this->userOwningBusinessUnitId as $userId => $buId) {
            if ($businessUnitId === $buId) {
                unset($this->userOwningBusinessUnitId[$userId]);
            }
        }
        foreach ($this->userBusinessUnitIds as $userId => $buIds) {
            foreach ($buIds as $key => $buId) {
                if ($businessUnitId === $buId) {
                    unset($this->userBusinessUnitIds[$userId][$key]);
                }
            }
            if (empty($this->userBusinessUnitIds[$userId])) {
                unset($this->userBusinessUnitIds[$userId]);
            }
        }
        unset($this->businessUnitOwningOrganizationId[$businessUnitId]);
        if (isset($this->subordinateBusinessUnitIds[$businessUnitId])) {
            foreach ($this->subordinateBusinessUnitIds[$businessUnitId] as $buId) {
                $this->removeBusinessUnit($buId);
            }
            unset($this->subordinateBusinessUnitIds[$businessUnitId]);
        }
    }

    /**
     * Sets the organization id to null for all users which belongs to the given organization
     *
     * @param int|string|null $organizationId
     */
    protected function resetUserOwningOrganizationId($organizationId)
    {
        if ($organizationId !== null) {
            foreach ($this->userOwningOrganizationId as $userId => $orgId) {
                if ($organizationId === $orgId) {
                    $this->userOwningOrganizationId[$userId] = null;
                }
            }
        }
    }

    /**
     * Removes the given business unit hierarchy from the tree
     *
     * @param int|string $businessUnitId
     */
    public function removeBusinessUnitHierarchy($businessUnitId)
    {
        if (isset($this->subordinateBusinessUnitIds[$businessUnitId])) {
            foreach ($this->subordinateBusinessUnitIds[$businessUnitId] as $buId) {
                $this->removeBusinessUnitHierarchy($buId);
            }
            unset($this->subordinateBusinessUnitIds[$businessUnitId]);
        }
    }

    /**
     * Removes the given user from the tree
     *
     * @param int|string $userId
     */
    public function removeUser($userId)
    {
        unset($this->userOwningOrganizationId[$userId]);
        unset($this->userOwningBusinessUnitId[$userId]);
        unset($this->userBusinessUnitIds[$userId]);
    }

    /**
     * Removes all items from the tree
     */
    public function clear()
    {
        $this->userOwningOrganizationId = array();
        $this->businessUnitOwningOrganizationId = array();
        $this->userOwningBusinessUnitId = array();
        $this->subordinateBusinessUnitIds = array();
        $this->userBusinessUnitIds = array();
    }

    /**
     * Checks whether the tree is empty (contains no elements).
     *
     * @return boolean true if the tree is empty, false otherwise.
     */
    public function isEmpty()
    {
        return
            empty($this->userOwningOrganizationId)
            && empty($this->businessUnitOwningOrganizationId)
            && empty($this->userOwningBusinessUnitId)
            && empty($this->subordinateBusinessUnitIds)
            && empty($this->userBusinessUnitIds);
    }

    protected function loadUserAndReturnUserOrganizationId($userId)
    {
        if ($this->eventListener !== null) {
            $this->eventListener->loadUser($this, $userId);
            if (isset($this->userOwningBusinessUnitId[$userId])) {
                return $this->loadBusinessUnitAndReturnUserOrganizationId($userId);
            }
        }

        return null;
    }

    protected function loadBusinessUnitAndReturnUserOrganizationId($userId)
    {
        if ($this->eventListener !== null) {
            $buId = $this->userOwningBusinessUnitId[$userId];
            if ($buId !== null) {
                $this->eventListener->loadBusinessUnit($this, $buId);
                if (isset($this->userOwningOrganizationId[$userId])) {
                    return $this->userOwningOrganizationId[$userId];
                }
            }
        }

        return null;
    }

    protected function loadUserAndReturnUserBusinessUnitId($userId)
    {
        if ($this->eventListener !== null) {
            $this->eventListener->loadUser($this, $userId);
            if (isset($this->userOwningBusinessUnitId[$userId])) {
                return $this->userOwningBusinessUnitId[$userId];
            }
        }

        return null;
    }

    protected function loadUserAndReturnUserBusinessUnitIds($userId)
    {
        if ($this->eventListener !== null) {
            $this->eventListener->loadUser($this, $userId);
            if (isset($this->userBusinessUnitIds[$userId])) {
                return $this->userBusinessUnitIds[$userId];
            }
        }

        return array();
    }

    protected function loadBusinessUnitAndReturnOrganizationId($businessUnitId)
    {
        if ($this->eventListener !== null) {
            $this->eventListener->loadBusinessUnit($this, $businessUnitId);
            if (isset($this->businessUnitOwningOrganizationId[$businessUnitId])) {
                return $this->businessUnitOwningOrganizationId[$businessUnitId];
            }
        }

        return null;
    }

    protected function loadBusinessUnitHierarchy($organizationId)
    {
        if ($this->eventListener !== null) {
            $this->eventListener->loadBusinessUnitHierarchy($this, $organizationId);
        }
    }
}
