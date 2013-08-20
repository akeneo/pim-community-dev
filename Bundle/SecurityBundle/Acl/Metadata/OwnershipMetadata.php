<?php

namespace Oro\Bundle\SecurityBundle\Acl\Metadata;

/**
 * This class represents the entity ownership metadata
 */
class OwnershipMetadata implements \Serializable
{
    const OWNERSHIP_TYPE_NONE = 0;
    const OWNERSHIP_TYPE_ORGANIZATION = 1;
    const OWNERSHIP_TYPE_BUSINESS_UNIT = 2;
    const OWNERSHIP_TYPE_USER = 3;

    /**
     * @var integer
     */
    protected $ownershipType;

    /**
     * @var string
     */
    protected $ownerColumnName;

    /**
     * Constructor
     *
     * @param string $ownershipType Can be one of ORGANIZATION, BUSINESS_UNIT or USER
     * @param string $ownerColumnName
     * @throws \InvalidArgumentException
     */
    public function __construct($ownershipType = '', $ownerColumnName = '')
    {
        switch (strtolower($ownershipType)) {
            case 'organization':
                $this->ownershipType = self::OWNERSHIP_TYPE_ORGANIZATION;
                break;
            case 'business_unit':
                $this->ownershipType = self::OWNERSHIP_TYPE_BUSINESS_UNIT;
                break;
            case 'user':
                $this->ownershipType = self::OWNERSHIP_TYPE_USER;
                break;
            default:
                if (!empty($ownershipType)) {
                    throw new \InvalidArgumentException(sprintf('Unknown ownership type: %s.', $ownershipType));
                }
                $this->ownershipType = self::OWNERSHIP_TYPE_NONE;
                break;
        }
        if ($this->ownershipType !== self::OWNERSHIP_TYPE_NONE && empty($ownerColumnName)) {
            throw new \InvalidArgumentException('The owner column name must not be empty.');
        }
        $this->ownerColumnName = $ownerColumnName;
    }

    /**
     * Indicates whether the entity has an owner
     *
     * @return bool
     */
    public function hasOwner()
    {
        return $this->ownershipType !== self::OWNERSHIP_TYPE_NONE;
    }

    /**
     * Indicates whether the ownership of the entity is Organization
     *
     * @return bool
     */
    public function isOrganizationOwned()
    {
        return $this->ownershipType === self::OWNERSHIP_TYPE_ORGANIZATION;
    }

    /**
     * Indicates whether the ownership of the entity is BusinessUnit
     *
     * @return bool
     */
    public function isBusinessUnitOwned()
    {
        return $this->ownershipType === self::OWNERSHIP_TYPE_BUSINESS_UNIT;
    }

    /**
     * Indicates whether the ownership of the entity is User
     *
     * @return bool
     */
    public function isUserOwned()
    {
        return $this->ownershipType === self::OWNERSHIP_TYPE_USER;
    }

    /**
     * Returns the name of the database column is used to store the entity owner
     *
     * @return string
     */
    public function getOwnerIdColumnName()
    {
        return $this->ownerColumnName;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(
            array(
                $this->ownershipType,
                $this->ownerColumnName,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->ownershipType,
            $this->ownerColumnName,
            ) = unserialize($serialized);
    }
}
