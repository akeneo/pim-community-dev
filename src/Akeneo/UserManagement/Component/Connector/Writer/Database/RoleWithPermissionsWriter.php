<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\Writer\Database;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class RoleWithPermissionsWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    private BulkSaverInterface $roleWithPermissionsSaver;
    private ?StepExecution $stepExecution = null;

    public function __construct(BulkSaverInterface $roleWithPermissionsSaver)
    {
        $this->roleWithPermissionsSaver = $roleWithPermissionsSaver;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $rolesWithPermissions): void
    {
        Assert::allIsInstanceOf($rolesWithPermissions, RoleWithPermissions::class);

        $this->incrementCount($rolesWithPermissions);
        $this->roleWithPermissionsSaver->saveAll($rolesWithPermissions);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    private function incrementCount(array $rolesWithPermissions): void
    {
        if (null === $this->stepExecution) {
            return;
        }
        foreach ($rolesWithPermissions as $roleWithPermissions) {
            if ($roleWithPermissions->role()->getId()) {
                $this->stepExecution->incrementSummaryInfo('process');
            } else {
                $this->stepExecution->incrementSummaryInfo('create');
            }
        }
    }
}
