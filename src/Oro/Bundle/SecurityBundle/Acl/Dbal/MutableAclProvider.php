<?php

namespace Oro\Bundle\SecurityBundle\Acl\Dbal;

use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Security\Acl\Dbal\MutableAclProvider as BaseMutableAclProvider;
use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\AclCacheInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\PermissionGrantingStrategyInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

/**
 * This class extends the standard Symfony MutableAclProvider.
 *
 * @todo Periodically check if updateSecurityIdentity and deleteSecurityIdentity methods exist
 *       in the standard Symfony MutableAclProvider and delete them from this class if so.
 *       Before deleting carefully check standard implementation of these methods,
 *       especially updateSecurityIdentity.
 * @see https://github.com/symfony/symfony/pull/8305
 * @see https://github.com/symfony/symfony/pull/8650
 */
class MutableAclProvider extends BaseMutableAclProvider
{
    /**
     * @var PermissionGrantingStrategyInterface
     */
    protected $permissionStrategy;

    /**
     * Constructor.
     *
     * @param Connection                          $connection
     * @param PermissionGrantingStrategyInterface $permissionGrantingStrategy
     * @param array                               $options
     * @param AclCacheInterface                   $cache
     */
    public function __construct(
        Connection $connection,
        PermissionGrantingStrategyInterface $permissionGrantingStrategy,
        array $options,
        AclCacheInterface $cache = null
    ) {
        $this->permissionStrategy = $permissionGrantingStrategy;
        parent::__construct($connection, $permissionGrantingStrategy, $options, $cache);
    }

    /**
     * Clear cache by $oid
     *
     * @param ObjectIdentityInterface $oid
     */
    public function clearOidCache(ObjectIdentityInterface $oid)
    {
        $this->cache->evictFromCacheByIdentity($oid);
    }

    /**
     * Put in cache empty ACL object for given OID
     *
     * @param ObjectIdentityInterface $oid
     */
    public function cacheEmptyAcl(ObjectIdentityInterface $oid)
    {
        $this->cache->putInCache(new Acl(0, $oid, $this->permissionStrategy, [], true));
    }

    /**
     * Initiates a transaction
     */
    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    /**
     * Commits a transaction
     */
    public function commit()
    {
        $this->connection->commit();
    }

    /**
     * Rolls back a transaction
     */
    public function rollBack()
    {
        $this->connection->rollBack();
    }

    /**
     * Updates a security identity when the user's username or the role name changes
     *
     * @param SecurityIdentityInterface $sid
     * @param string $oldName The old security identity name.
     *                        It is the user's username if $sid is UserSecurityIdentity
     *                        or the role name if $sid is RoleSecurityIdentity
     */
    public function updateSecurityIdentity(SecurityIdentityInterface $sid, $oldName)
    {
        $this->connection->executeQuery($this->getUpdateSecurityIdentitySql($sid, $oldName));
    }

    /**
     * Deletes the security identity from the database.
     * ACL entries have the CASCADE option on their foreign key so they will also get deleted
     *
     * @param SecurityIdentityInterface $sid
     * @throws \InvalidArgumentException
     */
    public function deleteSecurityIdentity(SecurityIdentityInterface $sid)
    {
        $this->connection->executeQuery($this->getDeleteSecurityIdentityIdSql($sid));
    }

    /**
     * Clear ACLs internal cache
     */
    public function clearCache()
    {
        $this->cache->clearCache();
    }

    /**
     * Returns the result using the storage.
     */
    public function isObjectIdentityExists(ObjectIdentityInterface $oid): bool
    {
        return false !== $this->retrieveObjectIdentityPrimaryKey($oid);
    }

    /**
     * Constructs the SQL for updating a security identity.
     *
     * @param SecurityIdentityInterface $sid
     * @param string $oldName
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function getUpdateSecurityIdentitySql(SecurityIdentityInterface $sid, $oldName)
    {
        if ($sid instanceof UserSecurityIdentity) {
            if ($sid->getUsername() == $oldName) {
                throw new \InvalidArgumentException('There are no changes.');
            }
            $oldIdentifier = $sid->getClass() . '-' . $oldName;
            $newIdentifier = $sid->getClass() . '-' . $sid->getUsername();
            $username = true;
        } elseif ($sid instanceof RoleSecurityIdentity) {
            if ($sid->getRole() == $oldName) {
                throw new \InvalidArgumentException('There are no changes.');
            }
            $oldIdentifier = $oldName;
            $newIdentifier = $sid->getRole();
            $username = false;
        } else {
            throw new \InvalidArgumentException(
                '$sid must either be an instance of UserSecurityIdentity, or RoleSecurityIdentity.'
            );
        }

        return sprintf(
            'UPDATE %s SET identifier = %s WHERE identifier = %s AND username = %s',
            $this->options['sid_table_name'],
            $this->connection->quote($newIdentifier),
            $this->connection->quote($oldIdentifier),
            $this->connection->getDatabasePlatform()->convertBooleans($username)
        );
    }

    /**
     * Constructs the SQL to delete a security identity.
     *
     * @param SecurityIdentityInterface $sid
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function getDeleteSecurityIdentityIdSql(SecurityIdentityInterface $sid)
    {
        $select = $this->getSelectSecurityIdentityIdSql($sid);
        $delete = preg_replace('/^SELECT id FROM/', 'DELETE FROM', $select);

        return $delete;
    }

    /**
     * {@inheritdoc}
     *
     * The goal of this overrided method is to fix the PIM-9779 issue. The same error was reported in the following issues:
     * https://github.com/symfony/security-acl/issues/5 (still open since 2015)
     * https://github.com/symfony/security-acl/issues/23 (still open since 2016)
     * https://github.com/symfony/security-acl/issues/24
     * https://github.com/symfony/security-acl/issues/28
     *
     * This method is an adaptation of the fix coming from https://github.com/symfony/security-acl/pull/29 (The PR was never merged for no reason, despite one approve)
     * We cannot override the hydrateObjectIdentities method to do exactly the same fix as the PR, so we fix the results
     */
    public function findAcls(array $oids, array $sids = array())
    {
        /** @var \SplObjectStorage $acls */
        $acls = parent::findAcls($oids, $sids);
        if (0 === $acls->count()) {
            return $acls;
        }

        $aclReflection = new \ReflectionClass(Acl::class);
        $aclClassAcesProperty = $aclReflection->getProperty('classAces');
        $aclClassAcesProperty->setAccessible(true);
        $aclClassFieldAcesProperty = $aclReflection->getProperty('classFieldAces');
        $aclClassFieldAcesProperty->setAccessible(true);
        $aclObjectAcesProperty = $aclReflection->getProperty('objectAces');
        $aclObjectAcesProperty->setAccessible(true);
        $aclObjectFieldAcesProperty = $aclReflection->getProperty('objectFieldAces');
        $aclObjectFieldAcesProperty->setAccessible(true);

        foreach ($oids as $oid) {
            /** @var Acl|null $acl */
            $acl = $acls->offsetGet($oid);
            if (null === $acl) {
                continue;
            }

            $aclClassAcesProperty->setValue($acl, $this->orderAces($acl->getClassAces()));
            $aclClassFieldAcesProperty->setValue(
                $acl,
                $this->orderFieldAces($aclClassFieldAcesProperty->getValue($acl))
            );
            $aclObjectAcesProperty->setValue($acl, $this->orderAces($acl->getObjectAces()));
            $aclObjectFieldAcesProperty->setValue(
                $acl,
                $this->orderFieldAces($aclObjectFieldAcesProperty->getValue($acl))
            );
        }

        $aclClassAcesProperty->setAccessible(false);
        $aclClassFieldAcesProperty->setAccessible(false);
        $aclObjectAcesProperty->setAccessible(false);
        $aclObjectFieldAcesProperty->setAccessible(false);

        return $acls;
    }

    private function orderAces(array $aces): array
    {
        ksort($aces);

        return array_values($aces);
    }

    private function orderFieldAces(array $fieldAces): array
    {
        foreach (array_keys($fieldAces) as $fieldName) {
            ksort($fieldAces[$fieldName]);
            $fieldAces[$fieldName] = array_values($fieldAces[$fieldName]);
        }

        return $fieldAces;
    }
}
