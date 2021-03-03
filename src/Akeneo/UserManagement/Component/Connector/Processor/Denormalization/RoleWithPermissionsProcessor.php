<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\Processor\Denormalization;

use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class RoleWithPermissionsProcessor extends AbstractProcessor implements ItemProcessorInterface
{
    private const ACL_DEFAULT_EXTENSION = 'action';

    private RoleRepositoryInterface $roleRepository;
    private ObjectUpdaterInterface $roleUpdater;
    private ValidatorInterface $validator;
    private ObjectDetacherInterface $objectDetacher;
    private AclManager $aclManager;

    public function __construct(
        RoleRepositoryInterface $roleRepository,
        ObjectUpdaterInterface $roleUpdater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher,
        AclManager $aclManager
    ) {
        $this->roleRepository = $roleRepository;
        $this->roleUpdater = $roleUpdater;
        $this->validator = $validator;
        $this->objectDetacher = $objectDetacher;
        $this->aclManager = $aclManager;
    }

    public function process($item): RoleWithPermissions
    {
        Assert::isArray($item);
        Assert::string($item['role'] ?? null);
        Assert::string($item['label'] ?? null);
        Assert::isArray($item['permissions'] ?? []);

        $allowedPermissions = null;

        $role = $this->roleRepository->findOneByIdentifier($item['role']);
        if (null === $role) {
            $role = new Role();
            $allowedPermissions = [];
        }

        if (\array_key_exists('permissions', $item)) {
            $allowedPermissions = $item['permissions'];
            unset($item['permissions']);
        }
        $this->roleUpdater->update($role, $item);

        $violations = $this->validator->validate($role);
        if ($violations->count() > 0) {
            $this->objectDetacher->detach($role);
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        $privileges = $this->aclManager->getPrivilegeRepository()->getPrivileges($this->aclManager->getSid($role));
        if (null !== $allowedPermissions) {
            $nonExistentPermissions = $this->processPrivileges($privileges, $allowedPermissions);
            if ([] !== $nonExistentPermissions) {
                $this->skipItemWithMessage(
                    $item,
                    \sprintf('The following permissions are invalid: %s', \implode(', ', $nonExistentPermissions))
                );
            }
        }

        $roleWithPermissions = RoleWithPermissions::createFromRoleAndPrivileges($role, $privileges->getValues());
        if (null !== $this->stepExecution) {
            $this->saveProcessedItemInStepExecutionContext($item['role'], $roleWithPermissions);
        }

        return $roleWithPermissions;
    }

    protected function saveProcessedItemInStepExecutionContext(string $itemIdentifier, $processedItem): void
    {
        $executionContext = $this->stepExecution->getExecutionContext();
        $processedItemsBatch = $executionContext->get('processed_items_batch') ?? [];
        $processedItemsBatch[$itemIdentifier] = $processedItem;

        $executionContext->put('processed_items_batch', $processedItemsBatch);
    }

    private function processPrivileges(Collection $aclPrivileges, array $permissions): array
    {
        $allowedPermissions = \array_flip($permissions);
        foreach ($aclPrivileges as $privilege) {
            if (self::ACL_DEFAULT_EXTENSION !== $privilege->getExtensionKey()) {
                $aclPrivileges->removeElement($privilege);
                continue;
            } elseif ($privilege->getIdentity()->getName() === AclPrivilegeRepository::ROOT_PRIVILEGE_NAME) {
                continue;
            }
            foreach ($privilege->getPermissions() as $permission) {
                $permission->setAccessLevel(
                    \array_key_exists(
                        $privilege->getIdentity()->getId(),
                        $allowedPermissions
                    ) ? AccessLevel::SYSTEM_LEVEL : AccessLevel::NONE_LEVEL
                );
                unset($allowedPermissions[$privilege->getIdentity()->getId()]);
            }
        }

        return \array_keys($allowedPermissions);
    }
}
