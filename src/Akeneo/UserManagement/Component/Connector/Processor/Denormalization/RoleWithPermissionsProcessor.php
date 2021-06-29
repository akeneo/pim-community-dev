<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\Processor\Denormalization;

use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class RoleWithPermissionsProcessor extends AbstractProcessor implements ItemProcessorInterface
{
    private SimpleFactoryInterface $roleWithPermissionsFactory;
    private ObjectUpdaterInterface $roleWithPermissionsUpdater;
    private ValidatorInterface $validator;

    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        SimpleFactoryInterface $roleWithPermissionsFactory,
        ObjectUpdaterInterface $roleWithPermissionsUpdater,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);
        $this->roleWithPermissionsFactory = $roleWithPermissionsFactory;
        $this->roleWithPermissionsUpdater = $roleWithPermissionsUpdater;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item): RoleWithPermissions
    {
        $itemIdentifier = $this->getItemIdentifier($this->repository, $item);
        $roleWithPermissions = $this->findOrCreateRoleWithPermissions($itemIdentifier);

        try {
            $permissions = (null === $roleWithPermissions->role()->getId()) ? ['permissions' => []] : [];
            $this->roleWithPermissionsUpdater->update($roleWithPermissions, array_merge($permissions, $item));
        } catch (PropertyException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validator->validate($roleWithPermissions);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        if (null !== $this->stepExecution) {
            $this->saveProcessedItemInStepExecutionContext($itemIdentifier, $roleWithPermissions);
        }

        return $roleWithPermissions;
    }

    protected function findOrCreateRoleWithPermissions(string $roleIdentifier): RoleWithPermissions
    {
        $entity = $this->repository->findOneByIdentifier($roleIdentifier);
        if (null !== $entity) {
            return $entity;
        }

        if ('' === $roleIdentifier || null === $this->stepExecution) {
            return $this->roleWithPermissionsFactory->create();
        }

        $executionContext = $this->stepExecution->getExecutionContext();
        $processedItemsBatch = $executionContext->get('processed_items_batch') ?? [];

        return $processedItemsBatch[$roleIdentifier] ?? $this->roleWithPermissionsFactory->create();
    }

    private function saveProcessedItemInStepExecutionContext(string $itemIdentifier, $processedItem): void
    {
        if (null === $this->stepExecution) {
            return;
        }

        $executionContext = $this->stepExecution->getExecutionContext();
        $processedItemsBatch = $executionContext->get('processed_items_batch') ?? [];
        $processedItemsBatch[$itemIdentifier] = $processedItem;

        $executionContext->put('processed_items_batch', $processedItemsBatch);
    }
}
