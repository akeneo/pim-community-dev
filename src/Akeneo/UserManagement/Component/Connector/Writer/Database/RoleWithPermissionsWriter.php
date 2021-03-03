<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\Writer\Database;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class RoleWithPermissionsWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    private ItemWriterInterface $writer;
    private AclManager $aclManager;
    private ?StepExecution $stepExecution;

    public function __construct(ItemWriterInterface $writer, AclManager $aclManager)
    {
        $this->writer = $writer;
        $this->aclManager = $aclManager;
    }

    public function write(array $rolesWithPermissions): void
    {
        Assert::notNull($this->stepExecution);
        Assert::allIsInstanceOf($rolesWithPermissions, RoleWithPermissions::class);

        $this->writer->write(array_map(
            fn (RoleWithPermissions $roleWithPermissions): RoleInterface => $roleWithPermissions->role(),
            $rolesWithPermissions
        ));
        array_walk($rolesWithPermissions, [$this, 'updatePermissions']);
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
        if ($this->writer instanceof StepExecutionAwareInterface) {
            $this->writer->setStepExecution($stepExecution);
        }
    }

    private function updatePermissions(RoleWithPermissions $roleWithPermissions): void
    {
        if (User::ROLE_ANONYMOUS === $roleWithPermissions->role()->getRole()) {
            return;
        }

        $this->aclManager->getPrivilegeRepository()->savePrivileges(
            $this->aclManager->getSid($roleWithPermissions->role()),
            new ArrayCollection($roleWithPermissions->privileges())
        );
    }
}
