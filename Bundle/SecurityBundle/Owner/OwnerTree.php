<?php

namespace Oro\Bundle\SecurityBundle\Owner;

/**
 * This class represents a tree of owners
 */
class OwnerTree
{
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
     * An associative array to store users belong to a business unit
     * key = businessUnitId
     * value = array of userId
     *
     * @var array
     */
    protected $businessUnitUserIds;

    /**
     * An associative array to store business units belong to an organization
     * key = organizationId
     * value = array of businessUnitId
     *
     * @var array
     */
    protected $organizationBusinessUnitIds;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->clear();
    }

    /**
     * Gets the owning organization id for the given user id
     *
     * @param int|string $userId
     * @return int|string|null
     */
    public function getUserOrganizationId($userId)
    {
        return isset($this->userOwningOrganizationId[$userId])
            ? $this->userOwningOrganizationId[$userId]
            : null;
    }

    /**
     * Gets the owning business unit id for the given user id
     *
     * @param int|string $userId
     * @return int|string|null
     */
    public function getUserBusinessUnitId($userId)
    {
        return isset($this->userOwningBusinessUnitId[$userId])
            ? $this->userOwningBusinessUnitId[$userId]
            : null;
    }

    /**
     * Gets all business unit ids for the given user id
     *
     * @param int|string $userId
     * @return array of int|string
     */
    public function getUserBusinessUnitIds($userId)
    {
        return isset($this->userBusinessUnitIds[$userId])
            ? $this->userBusinessUnitIds[$userId]
            : array();
    }

    /**
     * Gets all users ids for the given business unit id
     *
     * @param int|string $businessUnitId
     * @return array of int|string
     */
    public function getBusinessUnitUserIds($businessUnitId)
    {
        return isset($this->businessUnitUserIds[$businessUnitId])
            ? $this->businessUnitUserIds[$businessUnitId]
            : array();
    }

    /**
     * Gets the owning organization id for the given business unit id
     *
     * @param int|string $businessUnitId
     * @return int|string|null
     */
    public function getBusinessUnitOrganizationId($businessUnitId)
    {
        return isset($this->businessUnitOwningOrganizationId[$businessUnitId])
            ? $this->businessUnitOwningOrganizationId[$businessUnitId]
            : null;
    }

    /**
     * Gets all business unit ids for the given organization id
     *
     * @param int|string $organizationId
     * @return array of int|string
     */
    public function getOrganizationBusinessUnitIds($organizationId)
    {
        return isset($this->organizationBusinessUnitIds[$organizationId])
            ? $this->organizationBusinessUnitIds[$organizationId]
            : array();
    }

    /**
     * Gets all subordinate business unit ids for the given business unit id
     *
     * @param int|string $businessUnitId
     * @return array of int|string
     */
    public function getSubordinateBusinessUnitIds($businessUnitId)
    {
        return isset($this->subordinateBusinessUnitIds[$businessUnitId])
            ? $this->subordinateBusinessUnitIds[$businessUnitId]
            : array();
    }

    /**
     * Add the given business unit to the tree
     *
     * @param int|string $businessUnitId
     * @param int|string|null $owningOrganizationId
     */
    public function addBusinessUnit($businessUnitId, $owningOrganizationId)
    {
        $this->businessUnitOwningOrganizationId[$businessUnitId] = $owningOrganizationId;

        if (!isset($this->businessUnitUserIds[$businessUnitId])) {
            $this->businessUnitUserIds[$businessUnitId] = array();
        }

        if ($owningOrganizationId !== null) {
            if (!isset($this->organizationBusinessUnitIds[$owningOrganizationId])) {
                $this->organizationBusinessUnitIds[$owningOrganizationId] = array();
            }
            $this->organizationBusinessUnitIds[$owningOrganizationId][] = $businessUnitId;
        }

        foreach ($this->userOwningBusinessUnitId as $userId => $buId) {
            if ($buId === $businessUnitId) {
                $this->userOwningOrganizationId[$userId] = $owningOrganizationId;
            }
        }
    }

    /**
     * Add a business unit relation to the tree
     *
     * @param int|string $businessUnitId
     * @param int|string|null $parentBusinessUnitId
     */
    public function addBusinessUnitRelation($businessUnitId, $parentBusinessUnitId)
    {
        if ($parentBusinessUnitId !== null) {
            foreach ($this->subordinateBusinessUnitIds as $key => $val) {
                if (in_array($parentBusinessUnitId, $val, true)) {
                    $this->subordinateBusinessUnitIds[$key][] = $businessUnitId;
                }
            }
            if (!isset($this->subordinateBusinessUnitIds[$parentBusinessUnitId])) {
                $this->subordinateBusinessUnitIds[$parentBusinessUnitId] = array();
            }
            $this->subordinateBusinessUnitIds[$parentBusinessUnitId][] = $businessUnitId;
        }

        if (!isset($this->subordinateBusinessUnitIds[$businessUnitId])) {
            $this->subordinateBusinessUnitIds[$businessUnitId] = array();
        }
    }

    /**
     * Add the given user to the tree
     *
     * @param int|string $userId
     * @param int|string|null $owningBusinessUnitId
     */
    public function addUser($userId, $owningBusinessUnitId)
    {
        $this->userOwningBusinessUnitId[$userId] = $owningBusinessUnitId;

        if ($owningBusinessUnitId !== null) {
            if (!isset($this->businessUnitUserIds[$owningBusinessUnitId])) {
                $this->businessUnitUserIds[$owningBusinessUnitId] = array();
            }
            $this->businessUnitUserIds[$owningBusinessUnitId][] = $userId;

            if (isset($this->businessUnitOwningOrganizationId[$owningBusinessUnitId])) {
                $this->userOwningOrganizationId[$userId] =
                    $this->businessUnitOwningOrganizationId[$owningBusinessUnitId];
            }
        }
    }

    /**
     * Add a business unit to the given user
     *
     * @param int|string $userId
     * @param int|string $businessUnitId
     */
    public function addUserBusinessUnit($userId, $businessUnitId)
    {
        if (!isset($this->userBusinessUnitIds[$userId])) {
            $this->userBusinessUnitIds[$userId] = array();
        }
        if ($businessUnitId !== null) {
            $this->userBusinessUnitIds[$userId][] = $businessUnitId;
        }
    }

    /**
     * Removes all elements from the tree
     */
    public function clear()
    {
        $this->userOwningOrganizationId = array();
        $this->businessUnitOwningOrganizationId = array();
        $this->organizationBusinessUnitIds = array();
        $this->userOwningBusinessUnitId = array();
        $this->subordinateBusinessUnitIds = array();
        $this->userBusinessUnitIds = array();
        $this->businessUnitUserIds = array();
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
            && empty($this->organizationBusinessUnitIds)
            && empty($this->userOwningBusinessUnitId)
            && empty($this->subordinateBusinessUnitIds)
            && empty($this->userBusinessUnitIds)
            && empty($this->businessUnitUserIds);
    }
}
