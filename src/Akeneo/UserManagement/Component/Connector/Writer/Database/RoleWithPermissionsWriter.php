<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\Writer\Database;

use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Model\User;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class RoleWithPermissionsWriter implements ItemWriterInterface, StepExecutionAwareInterface, FlushableInterface
{
    private const ACL_EXTENSION_KEY = 'action';

    private ItemWriterInterface $writer;
    private AclManager $aclManager;
    private ?StepExecution $stepExecution;

    public function __construct(ItemWriterInterface $writer, AclManager $aclManager)
    {
        $this->writer = $writer;
        $this->aclManager = $aclManager;
    }

    public function write(array $roleWithPermissions)
    {
        Assert::notNull($this->stepExecution);
        Assert::allIsInstanceOf($roleWithPermissions, RoleWithPermissions::class);

        $this->writer->write(array_map(
            fn (RoleWithPermissions $roleWithPermissions): RoleInterface => $roleWithPermissions->role(),
            $roleWithPermissions
        ));
        array_walk($roleWithPermissions, [$this, 'updatePermissions']);
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
        if ($this->writer instanceof StepExecutionAwareInterface) {
            $this->writer->setStepExecution($stepExecution);
        }
    }

    public function flush(): void
    {
        $this->aclManager->flush();
    }

    private function updatePermissions(RoleWithPermissions $roleWithPermissions): void
    {
        if (User::ROLE_ANONYMOUS === $roleWithPermissions->role()->getRole()) {
            return;
        }

        $indexedPrivilegesNames = array_flip(array_map(
            fn (string $permissionId): string => false !== strpos($permissionId, ':')
                ? substr($permissionId, 1 + strpos($permissionId, ':'))
                : $permissionId,
            $roleWithPermissions->allowedPermissionIds()
        ));
        $sid = $this->aclManager->getSid($roleWithPermissions->role());

        foreach ($this->aclManager->getAllExtensions() as $extension) {
            if (static::ACL_EXTENSION_KEY !== $extension->getExtensionKey()) {
                continue;
            }

            $rootOid = $this->aclManager->getRootOid($extension->getExtensionKey());
            foreach ($extension->getAllMaskBuilders() as $maskBuilder) {
                $fullAccessMask = $maskBuilder->hasConst('GROUP_SYSTEM')
                    ? $maskBuilder->getConst('GROUP_SYSTEM')
                    : $maskBuilder->getConst('GROUP_ALL');
                $this->aclManager->setPermission($sid, $rootOid, $fullAccessMask, true);
            }

            foreach ($extension->getClasses() as $aclClassInfo) {
                $mask = array_key_exists($aclClassInfo->getClassName(), $indexedPrivilegesNames)
                    ? AccessLevel::BASIC_LEVEL
                    : AccessLevel::NONE_LEVEL
                ;
                $oid = new ObjectIdentity($extension->getExtensionKey(), $aclClassInfo->getClassName());
                $this->aclManager->setPermission($sid, $oid, $mask, true);
            }
        }
    }
}
