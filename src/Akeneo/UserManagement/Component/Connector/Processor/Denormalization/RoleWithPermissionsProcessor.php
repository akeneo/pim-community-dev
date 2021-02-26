<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\Processor\Denormalization;

use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationProvider;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class RoleWithPermissionsProcessor extends AbstractProcessor implements ItemProcessorInterface, InitializableInterface
{
    private RoleRepositoryInterface $roleRepository;
    private ValidatorInterface $validator;
    private ObjectDetacherInterface $objectDetacher;
    private AclAnnotationProvider $aclProvider;

    /** @var string[] */
    private array $acls = [];

    public function __construct(
        RoleRepositoryInterface $roleRepository,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher,
        AclAnnotationProvider $aclProvider
    ) {
        parent::__construct($roleRepository);

        $this->roleRepository = $roleRepository;
        $this->validator = $validator;
        $this->objectDetacher = $objectDetacher;
        $this->aclProvider = $aclProvider;
    }

    public function process($item): RoleWithPermissions
    {
        Assert::isArray($item);
        Assert::string($item['role'] ?? null);
        Assert::string($item['label'] ?? null);
        Assert::isArray($item['permissions'] ?? []);

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

        $allowedPermissionIds = $item['permissions'] ?? [];
        $nonExistingPrivileges = array_diff($allowedPermissionIds, $this->acls);
        if ([] !== $nonExistingPrivileges) {
            $this->objectDetacher->detach($role);
            $this->skipItemWithMessage(
                $item,
                \sprintf('The following permissions are invalid: %s', \implode(', ', $nonExistingPrivileges))
            );
        }

        $roleWithPermissions = RoleWithPermissions::createFromRoleAndPermissionIds($role, $allowedPermissionIds);
        if (null !== $this->stepExecution) {
            $this->saveProcessedItemInStepExecutionContext($item['role'], $item);
        }

        return $roleWithPermissions;
    }

    public function initialize(): void
    {
        $this->acls = \array_map(
            fn (Acl $acl): string => \sprintf('%s:%s', $acl->getType(), $acl->getId()),
            $this->aclProvider->getAnnotations()
        );
    }

    protected function saveProcessedItemInStepExecutionContext(string $itemIdentifier, $processedItem): void
    {
        $executionContext = $this->stepExecution->getExecutionContext();
        $processedItemsBatch = $executionContext->get('processed_items_batch') ?? [];
        $processedItemsBatch[$itemIdentifier] = $processedItem;

        $executionContext->put('processed_items_batch', $processedItemsBatch);
    }
}
