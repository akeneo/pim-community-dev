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
     * An associative array to store organizations assigned to an user
     * key = userId
     * value = array of organizationId
     *
     * @var array
     */
    protected $userOrganizationIds;

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
     * Gets all organization ids assigned to the given user id
     *
     * @param int|string $userId
     * @return int|string|null
     */
    public function getUserOrganizationIds($userId)
    {
        return isset($this->userOrganizationIds[$userId])
            ? $this->userOrganizationIds[$userId]
            : array();
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
     * Gets all business unit ids assigned to the given user id
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

        if ($owningOrganizationId !== null) {
            if (!isset($this->organizationBusinessUnitIds[$owningOrganizationId])) {
                $this->organizationBusinessUnitIds[$owningOrganizationId] = array();
            }
            $this->organizationBusinessUnitIds[$owningOrganizationId][] = $businessUnitId;
        }

        $this->businessUnitUserIds[$businessUnitId] = array();
        foreach ($this->userOwningBusinessUnitId as $userId => $buId) {
            if ($businessUnitId === $buId) {
                $this->businessUnitUserIds[$businessUnitId][] = $userId;
                $this->userOwningOrganizationId[$userId] = $owningOrganizationId;
                if ($owningOrganizationId !== null) {
                    $this->userOrganizationIds[$userId][] = $owningOrganizationId;
                }
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
            if (isset($this->businessUnitUserIds[$owningBusinessUnitId])) {
                $this->businessUnitUserIds[$owningBusinessUnitId][] = $userId;
            }

            $this->userOrganizationIds[$userId] = array();
            if (isset($this->businessUnitOwningOrganizationId[$owningBusinessUnitId])) {
                $this->userOwningOrganizationId[$userId] =
                    $this->businessUnitOwningOrganizationId[$owningBusinessUnitId];
                $this->userOrganizationIds[$userId][] = $this->businessUnitOwningOrganizationId[$owningBusinessUnitId];
            } else {
                $this->userOwningOrganizationId[$userId] = null;
            }
        } else {
            $this->userOwningOrganizationId[$userId] = null;
            $this->userOrganizationIds[$userId] = array();
        }

        $this->userBusinessUnitIds[$userId] = array();
    }

    /**
     * Add a business unit to the given user
     *
     * @param int|string $userId
     * @param int|string $businessUnitId
     * @throws \LogicException
     */
    public function addUserBusinessUnit($userId, $businessUnitId)
    {
        if (!isset($this->userBusinessUnitIds[$userId])) {
            throw new \LogicException(sprintf('First call addUser for userId: %s.', (string)$userId));
        }
        if ($businessUnitId !== null) {
            $this->userBusinessUnitIds[$userId][] = $businessUnitId;
            if (isset($this->businessUnitOwningOrganizationId[$businessUnitId])) {
                $this->userOrganizationIds[$userId][] = $this->businessUnitOwningOrganizationId[$businessUnitId];
            }
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
        $this->userOrganizationIds = array();
        $this->userBusinessUnitIds = array();
        $this->businessUnitUserIds = array();
    }
}
