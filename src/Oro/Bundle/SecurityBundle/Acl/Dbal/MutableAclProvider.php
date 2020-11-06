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
    public function clearOidCache(ObjectIdentityInterface $oid): void
    {
        $this->cache->evictFromCacheByIdentity($oid);
    }

    /**
     * Put in cache empty ACL object for given OID
     *
     * @param ObjectIdentityInterface $oid
     */
    public function cacheEmptyAcl(ObjectIdentityInterface $oid): void
    {
        $this->cache->putInCache(new Acl(0, $oid, $this->permissionStrategy, [], true));
    }

    /**
     * Initiates a transaction
     */
    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    /**
     * Commits a transaction
     */
    public function commit(): void
    {
        $this->connection->commit();
    }

    /**
     * Rolls back a transaction
     */
    public function rollBack(): void
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
    public function updateSecurityIdentity(SecurityIdentityInterface $sid, string $oldName): void
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
    public function deleteSecurityIdentity(SecurityIdentityInterface $sid): void
    {
        $this->connection->executeQuery($this->getDeleteSecurityIdentityIdSql($sid));
    }

    /**
     * Clear ACLs internal cache
     */
    public function clearCache(): void
    {
        $this->cache->clearCache();
    }

    /**
     * Constructs the SQL for updating a security identity.
     *
     * @param SecurityIdentityInterface $sid
     * @param string $oldName
     * @throws \InvalidArgumentException
     */
    protected function getUpdateSecurityIdentitySql(SecurityIdentityInterface $sid, string $oldName): string
    {
        if ($sid instanceof UserSecurityIdentity) {
            if ($sid->getUsername() === $oldName) {
                throw new \InvalidArgumentException('There are no changes.');
            }
            $oldIdentifier = $sid->getClass() . '-' . $oldName;
            $newIdentifier = $sid->getClass() . '-' . $sid->getUsername();
            $username = true;
        } elseif ($sid instanceof RoleSecurityIdentity) {
            if ($sid->getRole() === $oldName) {
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
     */
    protected function getDeleteSecurityIdentityIdSql(SecurityIdentityInterface $sid): ?string
    {
        $select = $this->getSelectSecurityIdentityIdSql($sid);

        return preg_replace('/^SELECT id FROM/', 'DELETE FROM', $select);
    }
}
