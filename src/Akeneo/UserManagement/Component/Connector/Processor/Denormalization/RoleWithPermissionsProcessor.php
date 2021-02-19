<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\Processor\Denormalization;

use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class RoleWithPermissionsProcessor extends AbstractProcessor implements ItemProcessorInterface
{
    private RoleRepositoryInterface $roleRepository;
    private ValidatorInterface $validator;
    private ObjectDetacherInterface $objectDetacher;

    public function __construct(
        RoleRepositoryInterface $roleRepository,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher
    ) {
        parent::__construct($roleRepository);

        $this->roleRepository = $roleRepository;
        $this->validator = $validator;
        $this->objectDetacher = $objectDetacher;
    }

    public function process($item): RoleWithPermissions
    {
        Assert::isArray($item);
        Assert::string($item['role'] ?? null);
        Assert::string($item['label'] ?? null);
        Assert::isArray($item['permissions'] ?? null);

        $role = $this->roleRepository->findOneByIdentifier($item['role']);
        if (null === $role) {
            $role = new Role($item['role']);
        }
        $role->setLabel($item['label']);

        $violations = $this->validator->validate($role);
        if ($violations->count() > 0) {
            $this->objectDetacher->detach($role);
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        $allowedPermissionIds = [];
        foreach ($item['permissions'] as $privilege) {
            foreach ($privilege['permissions'] as $permission) {
                if ($permission['access_level'] !== AccessLevel::NONE_LEVEL) {
                    $allowedPermissionIds[] = $privilege['id'];

                    break;
                }
            }
        }

        $roleWithPermissions = RoleWithPermissions::createFromRoleAndPermissionIds($role, $allowedPermissionIds);
        if (null !== $this->stepExecution) {
            $this->saveProcessedItemInStepExecutionContext($item['role'], $item);
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
}
