<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Component\Connector\Processor\Denormalization;

use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Connector\Processor\Denormalization\RoleWithPermissionsProcessor;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RoleWithPermissionsProcessorSpec extends ObjectBehavior
{
    function let(
        RoleRepositoryInterface $roleRepository,
        ObjectUpdaterInterface $roleUpdater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher,
        AclManager $aclManager,
        StepExecution $stepExecution,
        ExecutionContext $executionContext,
        AclPrivilegeRepository $privilegeRepository
    ) {
        $aclManager->getSid(Argument::type(RoleInterface::class))->will(
            function ($args): RoleSecurityIdentity {
                return new RoleSecurityIdentity($args[0]->getRole());
            }
        );
        $aclManager->getPrivilegeRepository()->willReturn($privilegeRepository);
        $privilegeRepository->getPrivileges(Argument::type(RoleSecurityIdentity::class))->willReturn(
            new ArrayCollection(
                [
                    $this->createPrivilege('root:(default)', '(default)', 'action', 5),
                    $this->createPrivilege('action:privilege1', 'privilege1', 'action', 5),
                    $this->createPrivilege('action:privilege2', 'privilege2', 'action', 5),
                ]
            )
        );

        $this->beConstructedWith($roleRepository, $roleUpdater, $validator, $objectDetacher, $aclManager);
        $stepExecution->getExecutionContext()->willReturn($executionContext);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_processor()
    {
        $this->shouldBeAnInstanceOf(RoleWithPermissionsProcessor::class);
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    function it_processes_a_new_role(
        ObjectUpdaterInterface $roleUpdater,
        ValidatorInterface $validator
    ) {
        $item = ['role' => 'ROLE_NEW', 'label' => 'the label', 'permissions' => ['action:privilege1']];

        $roleUpdater->update(
            Argument::type(RoleInterface::class),
            ['role' => 'ROLE_NEW', 'label' => 'the label']
        )->shouldBeCalled();
        $validator->validate(Argument::type(RoleInterface::class))->willReturn(new ConstraintViolationList());

        $roleWithPermissions = $this->process($item);
        $roleWithPermissions->shouldBeAnInstanceOf(RoleWithPermissions::class);
        $roleWithPermissions->privileges()->shouldBeLike(
            [
                $this->createPrivilege('root:(default)', '(default)', 'action', 5),
                $this->createPrivilege('action:privilege1', 'privilege1', 'action', 5),
                $this->createPrivilege('action:privilege2', 'privilege2', 'action', 0),
            ]
        );
    }

    function it_processes_an_existing_role(
        RoleRepositoryInterface $roleRepository,
        ObjectUpdaterInterface $roleUpdater,
        ValidatorInterface $validator,
        RoleInterface $role
    ) {
        $item = [
            'role' => 'ROLE_USER',
            'label' => 'the new label',
            'permissions' => [],
        ];

        $roleRepository->findOneByIdentifier('ROLE_USER')->willReturn($role);
        $roleUpdater->update($role, ['role' => 'ROLE_USER','label' => 'the new label'])->shouldBeCalled();
        $validator->validate($role)->willReturn(new ConstraintViolationList());

        $roleWithPermissions = $this->process($item);
        $roleWithPermissions->shouldBeAnInstanceOf(RoleWithPermissions::class);
        $roleWithPermissions->role()->shouldBe($role);
        $roleWithPermissions->privileges()->shouldBeLike(
            [
                $this->createPrivilege('root:(default)', '(default)', 'action', 5),
                $this->createPrivilege('action:privilege1', 'privilege1', 'action', 0),
                $this->createPrivilege('action:privilege2', 'privilege2', 'action', 0),
            ]
        );
    }

    function it_does_not_update_permissions_if_they_are_not_specified(
        RoleRepositoryInterface $roleRepository,
        ObjectUpdaterInterface $roleUpdater,
        ValidatorInterface $validator,
        RoleInterface $role
    ) {
        $item = [
            'role' => 'ROLE_USER',
            'label' => 'the new label',
        ];

        $roleRepository->findOneByIdentifier('ROLE_USER')->willReturn($role);
        $roleUpdater->update($role, ['role' => 'ROLE_USER', 'label' => 'the new label'])->shouldBeCalled();
        $validator->validate($role)->willReturn(new ConstraintViolationList());

        $roleWithPermissions = $this->process($item);
        $roleWithPermissions->shouldBeAnInstanceOf(RoleWithPermissions::class);
        $roleWithPermissions->role()->shouldBe($role);
        $roleWithPermissions->privileges()->shouldBeLike(
            [
                $this->createPrivilege('root:(default)', '(default)', 'action', 5),
                $this->createPrivilege('action:privilege1', 'privilege1', 'action', 5),
                $this->createPrivilege('action:privilege2', 'privilege2', 'action', 5),
            ]
        );
    }

    function it_throws_an_exception_when_validation_fails(
        RoleRepositoryInterface $roleRepository,
        ObjectUpdaterInterface $roleUpdater,
        ValidatorInterface $validator,
        StepExecution $stepExecution,
        RoleInterface $role
    ) {
        $item = ['role' => 'ROLE_USER', 'label' => 'the new label'];

        $roleRepository->findOneByIdentifier('ROLE_USER')->willReturn($role);
        $roleUpdater->update($role, $item)->shouldBeCalled();
        $validator->validate($role)->willReturn(
            new ConstraintViolationList(
                [
                    new ConstraintViolation('message', null, [], null, null, null),
                ]
            )
        );

        $stepExecution->getSummaryInfo('item_position')->willReturn(44);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $this->shouldThrow(InvalidItemFromViolationsException::class)->during('process', [$item]);
    }

    function it_throws_an_exception_if_a_permission_does_not_exist(
        ObjectUpdaterInterface $roleUpdater,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $item = ['role' => 'ROLE_NEW', 'label' => 'My new role', 'permissions' => ['unknown:unknown']];
        $roleUpdater->update(
            Argument::type(RoleInterface::class),
            ['role' => 'ROLE_NEW', 'label' => 'My new role']
        )->shouldBeCalled();
        $validator->validate(Argument::type(RoleInterface::class))->willReturn(new ConstraintViolationList([]));

        $stepExecution->getSummaryInfo('item_position')->willReturn(42);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $this->shouldThrow(InvalidItemException::class)->during('process', [$item]);
    }

    private function createPrivilege(string $id, string $name, string $extensionKey, int $accessLevel = 0): AclPrivilege
    {
        $privilege = new AclPrivilege();
        $privilege->setIdentity(new AclPrivilegeIdentity($id, $name));
        $privilege->setExtensionKey($extensionKey);
        $privilege->addPermission(new AclPermission('EXECUTE', $accessLevel));

        return $privilege;
    }
}
