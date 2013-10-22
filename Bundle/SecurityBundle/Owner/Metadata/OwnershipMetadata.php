<?php

namespace Oro\Bundle\SecurityBundle\Owner\Metadata;

/**
 * This class represents the entity ownership metadata
 */
class OwnershipMetadata implements \Serializable
{
    const OWNER_TYPE_NONE = 0;
    const OWNER_TYPE_ORGANIZATION = 1;
    const OWNER_TYPE_BUSINESS_UNIT = 2;
    const OWNER_TYPE_USER = 3;

    /**
     * @var integer
     */
    protected $ownerType;

    /**
     * @var string
     */
    protected $ownerFieldName;

    /**
     * @var string
     */
    protected $ownerColumnName;

    /**
     * Constructor
     *
     * @param string $ownerType Can be one of ORGANIZATION, BUSINESS_UNIT or USER
     * @param string $ownerFieldName
     * @param string $ownerColumnName
     * @throws \InvalidArgumentException
     */
    public function __construct($ownerType = '', $ownerFieldName = '', $ownerColumnName = '')
    {
        switch ($ownerType) {
            case 'ORGANIZATION':
                $this->ownerType = self::OWNER_TYPE_ORGANIZATION;
                break;
            case 'BUSINESS_UNIT':
                $this->ownerType = self::OWNER_TYPE_BUSINESS_UNIT;
                break;
            case 'USER':
                $this->ownerType = self::OWNER_TYPE_USER;
                break;
            case 'NONE':
                $this->ownerType = self::OWNER_TYPE_NONE;
                break;
            default:
                if (!empty($ownerType)) {
                    throw new \InvalidArgumentException(sprintf('Unknown owner type: %s.', $ownerType));
                }
                $this->ownerType = self::OWNER_TYPE_NONE;
                break;
        }
        if ($this->ownerType !== self::OWNER_TYPE_NONE && empty($ownerFieldName)) {
            throw new \InvalidArgumentException('The owner field name must not be empty.');
        }
        $this->ownerFieldName = $ownerFieldName;
        if ($this->ownerType !== self::OWNER_TYPE_NONE && empty($ownerColumnName)) {
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
        return $this->ownerType !== self::OWNER_TYPE_NONE;
    }

    /**
     * Indicates whether the ownership of the entity is Organization
     *
     * @return bool
     */
    public function isOrganizationOwned()
    {
        return $this->ownerType === self::OWNER_TYPE_ORGANIZATION;
    }

    /**
     * Indicates whether the ownership of the entity is BusinessUnit
     *
     * @return bool
     */
    public function isBusinessUnitOwned()
    {
        return $this->ownerType === self::OWNER_TYPE_BUSINESS_UNIT;
    }

    /**
     * Indicates whether the ownership of the entity is User
     *
     * @return bool
     */
    public function isUserOwned()
    {
        return $this->ownerType === self::OWNER_TYPE_USER;
    }

    /**
     * Gets the name of the field is used to store the entity owner
     *
     * @return string
     */
    public function getOwnerFieldName()
    {
        return $this->ownerFieldName;
    }

    /**
     * Gets the name of the database column is used to store the entity owner
     *
     * @return string
     */
    public function getOwnerColumnName()
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
                $this->ownerType,
                $this->ownerFieldName,
                $this->ownerColumnName
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->ownerType,
            $this->ownerFieldName,
            $this->ownerColumnName
            ) = unserialize($serialized);
    }
}
