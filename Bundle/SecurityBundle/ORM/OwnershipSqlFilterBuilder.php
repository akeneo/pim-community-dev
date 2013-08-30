<?php

namespace Oro\Bundle\SecurityBundle\ORM;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;
use Oro\Bundle\EntityBundle\Owner\Metadata\OwnershipMetadata;
use Oro\Bundle\EntityBundle\Owner\Metadata\OwnershipMetadataProvider;
use Oro\Bundle\SecurityBundle\Owner\OwnerTree;
use Oro\Bundle\SecurityBundle\Acl\Domain\OneShotIsGrantedObserver;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdAccessor;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Voter\AclVoter;

class OwnershipSqlFilterBuilder
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var ObjectIdAccessor
     */
    protected $objectIdAccessor;

    /**
     * @var AclVoter
     */
    protected $aclVoter;

    /**
     * @var OwnershipMetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var OwnerTree
     */
    protected $tree;

    /**
     * Constructor
     *
     * @param SecurityContextInterface $securityContext
     * @param AclVoter $aclVoter
     * @param ObjectIdAccessor $objectIdAccessor
     * @param OwnershipMetadataProvider $metadataProvider
     * @param OwnerTree $tree
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        AclVoter $aclVoter,
        ObjectIdAccessor $objectIdAccessor,
        OwnershipMetadataProvider $metadataProvider,
        OwnerTree $tree
    ) {
        $this->securityContext = $securityContext;
        $this->aclVoter = $aclVoter;
        $this->objectIdAccessor = $objectIdAccessor;
        $this->metadataProvider = $metadataProvider;
        $this->tree = $tree;
    }

    /**
     * Gets the SQL query part to add to a query.
     *
     * @param string $targetEntityClassName
     * @param string $targetTableAlias
     * @return string The constraint SQL if there is available, empty string otherwise
     *
     * The cyclomatic complexity warning is suppressed by performance reasons
     * (to avoid unnecessary cloning od arrays)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function buildFilterConstraint($targetEntityClassName, $targetTableAlias)
    {
        $constraint = null;

        // @TODO Add check for service entities (not annotated as ACL)

        $observer = new OneShotIsGrantedObserver();
        $this->aclVoter->addOneShotIsGrantedObserver($observer);
        $isGranted = $this->securityContext->isGranted('VIEW', 'entity:' . $targetEntityClassName);

        $metadata = $this->metadataProvider->getMetadata($targetEntityClassName);
        if ($isGranted) {
            $accessLevel = $observer->getAccessLevel();
            if (AccessLevel::SYSTEM_LEVEL === $accessLevel) {
                $constraint = '';
            } elseif (!$metadata->hasOwner()) {
                if (AccessLevel::GLOBAL_LEVEL === $accessLevel) {
                    if ($this->metadataProvider->getOrganizationClass() === $targetEntityClassName) {
                        $orgIds = $this->tree->getUserOrganizationIds($this->getUserId());
                        $constraint = $this->getCondition($orgIds, $metadata, $targetTableAlias, 'id');
                    } else {
                        $constraint = '';
                    }
                } else {
                    $constraint = '';
                }
            } else {
                if (AccessLevel::BASIC_LEVEL === $accessLevel) {
                    if ($this->metadataProvider->getUserClass() === $targetEntityClassName) {
                        $constraint = $this->getCondition($this->getUserId(), $metadata, $targetTableAlias, 'id');
                    } elseif ($metadata->isUserOwned()) {
                        $constraint = $this->getCondition($this->getUserId(), $metadata, $targetTableAlias);
                    }
                } elseif (AccessLevel::LOCAL_LEVEL === $accessLevel) {
                    if ($this->metadataProvider->getBusinessUnitClass() === $targetEntityClassName) {
                        $buIds = $this->tree->getUserBusinessUnitIds($this->getUserId());
                        $constraint = $this->getCondition($buIds, $metadata, $targetTableAlias, 'id');
                    } elseif ($metadata->isBusinessUnitOwned()) {
                        $buIds = $this->tree->getUserBusinessUnitIds($this->getUserId());
                        $constraint = $this->getCondition($buIds, $metadata, $targetTableAlias);
                    } elseif ($metadata->isUserOwned()) {
                        $userIds = array();
                        $this->fillBusinessUnitUserIds($this->getUserId(), $userIds);
                        $constraint = $this->getCondition($userIds, $metadata, $targetTableAlias);
                    }
                } elseif (AccessLevel::DEEP_LEVEL === $accessLevel) {
                    if ($this->metadataProvider->getBusinessUnitClass() === $targetEntityClassName) {
                        $buIds = array();
                        $this->fillSubordinateBusinessUnitIds($this->getUserId(), $buIds);
                        $constraint = $this->getCondition($buIds, $metadata, $targetTableAlias, 'id');
                    } elseif ($metadata->isBusinessUnitOwned()) {
                        $buIds = array();
                        $this->fillSubordinateBusinessUnitIds($this->getUserId(), $buIds);
                        $constraint = $this->getCondition($buIds, $metadata, $targetTableAlias);
                    } elseif ($metadata->isUserOwned()) {
                        $userIds = array();
                        $this->fillSubordinateBusinessUnitUserIds($this->getUserId(), $userIds);
                        $constraint = $this->getCondition($userIds, $metadata, $targetTableAlias);
                    }
                } elseif (AccessLevel::GLOBAL_LEVEL === $accessLevel) {
                    if ($metadata->isOrganizationOwned()) {
                        $orgIds = $this->tree->getUserOrganizationIds($this->getUserId());
                        $constraint = $this->getCondition($orgIds, $metadata, $targetTableAlias);
                    } elseif ($metadata->isBusinessUnitOwned()) {
                        $buIds = array();
                        $this->fillSubordinateBusinessUnitIds($this->getUserId(), $buIds);
                        $constraint = $this->getCondition($buIds, $metadata, $targetTableAlias);
                    } elseif ($metadata->isUserOwned()) {
                        $userIds = array();
                        $this->fillOrganizationUserIds($this->getUserId(), $userIds);
                        $constraint = $this->getCondition($userIds, $metadata, $targetTableAlias);
                    }
                }
            }
        }

        if ($constraint === null) {
            // "deny access" SQL condition
            $constraint = empty($targetTableAlias)
                ? '1 = 0'
                : sprintf('\'%s\' = \'\'', $targetTableAlias);
        }

        return $constraint;
    }

    /**
     * Adds all business unit ids within all subordinate business units the given user is associated
     *
     * @param int|string $userId
     * @param array $result [output]
     */
    protected function fillSubordinateBusinessUnitIds($userId, array &$result)
    {
        $buIds = $this->tree->getUserBusinessUnitIds($userId);
        $result = array_merge($buIds, array());
        foreach ($buIds as $buId) {
            $diff = array_diff($this->tree->getSubordinateBusinessUnitIds($buId), $result);
            if (!empty($diff)) {
                $result = array_merge($result, $diff);
            }
        }
    }

    /**
     * Adds all user ids within all business units the given user is associated
     *
     * @param int|string $userId
     * @param array $result [output]
     */
    protected function fillBusinessUnitUserIds($userId, array &$result)
    {
        foreach ($this->tree->getUserBusinessUnitIds($userId) as $buId) {
            $userIds = $this->tree->getBusinessUnitUserIds($buId);
            if (!empty($userIds)) {
                $result = array_merge($result, $userIds);
            }
        }
    }

    /**
     * Adds all user ids within all subordinate business units the given user is associated
     *
     * @param int|string $userId
     * @param array $result [output]
     */
    protected function fillSubordinateBusinessUnitUserIds($userId, array &$result)
    {
        $buIds = array();
        $this->fillSubordinateBusinessUnitIds($userId, $buIds);
        foreach ($buIds as $buId) {
            $userIds = $this->tree->getBusinessUnitUserIds($buId);
            if (!empty($userIds)) {
                $result = array_merge($result, $userIds);
            }
        }
    }

    /**
     * Adds all user ids within all organizations the given user is associated
     *
     * @param int|string $userId
     * @param array $result [output]
     */
    protected function fillOrganizationUserIds($userId, array &$result)
    {
        foreach ($this->tree->getUserOrganizationIds($userId) as $orgId) {
            foreach ($this->tree->getOrganizationBusinessUnitIds($orgId) as $buId) {
                $userIds = $this->tree->getBusinessUnitUserIds($buId);
                if (!empty($userIds)) {
                    $result = array_merge($result, $userIds);
                }
            }
        }
    }

    /**
     * Gets the id of logged in user
     *
     * @return int|string
     * @throws InvalidDomainObjectException
     */
    protected function getUserId()
    {
        $user = $this->securityContext->getToken()->getUser();
        if (!is_object($user) || !is_a($user, $this->metadataProvider->getUserClass())) {
            throw new InvalidDomainObjectException(
                sprintf(
                    '$user must be an instance of %s.',
                    $this->metadataProvider->getUserClass()
                )
            );
        }

        return $this->objectIdAccessor->getId($user);
    }

    /**
     * Gets SQL condition for the given owner id or ids
     *
     * @param int|int[]|null $idOrIds
     * @param OwnershipMetadata $metadata
     * @param string $targetTableAlias
     * @param string|null $columnName
     * @return string|null A string represents SQL condition or null if the given owner id(s) is not provided
     */
    protected function getCondition($idOrIds, OwnershipMetadata $metadata, $targetTableAlias, $columnName = null)
    {
        $result = null;
        if (!empty($idOrIds)) {
            if (is_array($idOrIds)) {
                if (count($idOrIds) > 1) {
                    $result = sprintf(
                        '%s IN (%s)',
                        $this->getColumnName($metadata, $targetTableAlias, $columnName),
                        implode(',', $idOrIds)
                    );
                } else {
                    $result = $this->getColumnName($metadata, $targetTableAlias, $columnName) . ' = ' . $idOrIds[0];
                }
            } else {
                $result = $this->getColumnName($metadata, $targetTableAlias, $columnName) . ' = ' . $idOrIds;
            }
        }

        return $result;
    }

    /**
     * Gets the name of owner column
     *
     * @param OwnershipMetadata $metadata
     * @param string $targetTableAlias
     * @param string|null $columnName
     * @return string
     */
    protected function getColumnName(OwnershipMetadata $metadata, $targetTableAlias, $columnName = null)
    {
        if ($columnName === null) {
            $columnName = $metadata->getOwnerColumnName();
        }

        return empty($targetTableAlias)
            ? $columnName
            : $targetTableAlias . '.' . $columnName;
    }
}
