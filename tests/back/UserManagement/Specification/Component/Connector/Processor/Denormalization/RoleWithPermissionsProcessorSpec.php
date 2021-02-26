<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Component\Connector\Processor\Denormalization;

use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\UserManagement\Component\Connector\Processor\Denormalization\RoleWithPermissionsProcessor;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationProvider;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RoleWithPermissionsProcessorSpec extends ObjectBehavior
{
    function let(
        RoleRepositoryInterface $roleRepository,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher,
        AclAnnotationProvider $aclProvider,
        StepExecution $stepExecution,
        ExecutionContext $executionContext
    ) {
        $aclProvider->getAnnotations()->willReturn([
            new Acl(['type' => 'type1', 'id' => 'privilege1']),
            new ACl(['type' => 'type2', 'id' => 'privilege2']),
        ]);

        $roleRepository->findOneByIdentifier('ROLE_NEW')->willReturn(null);
        $this->beConstructedWith($roleRepository, $validator, $objectDetacher, $aclProvider);
        $this->initialize();
        $stepExecution->getExecutionContext()->willReturn($executionContext);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_processor()
    {
        $this->shouldBeAnInstanceOf(RoleWithPermissionsProcessor::class);
        $this->shouldImplement(ItemProcessorInterface::class);
        $this->shouldImplement(InitializableInterface::class);
    }

    function it_processes_a_new_role(ValidatorInterface $validator)
    {
        $item = ['role' => 'ROLE_NEW', 'label' => 'the label', 'permissions' => ['type1:privilege1']];

        $validator->validate(Argument::type(Role::class))->willReturn(new ConstraintViolationList());

        $roleWithPermissions = $this->process($item);
        $roleWithPermissions->shouldBeAnInstanceOf(RoleWithPermissions::class);
        $roleWithPermissions->role()->getRole()->shouldBe('ROLE_NEW');
        $roleWithPermissions->role()->getLabel()->shouldBe('the label');
        $roleWithPermissions->allowedPermissionIds()->shouldBe(['type1:privilege1']);
    }

    function it_processes_an_existing_role(RoleRepositoryInterface $roleRepository, ValidatorInterface $validator)
    {
        $item = [
            'role' => 'ROLE_USER',
            'label' => 'the new label',
            'permissions' => ['type1:privilege1', 'type2:privilege2'],
        ];

        $role = new Role('ROLE_USER');
        $role->setLabel('old label');
        $roleRepository->findOneByIdentifier('ROLE_USER')->willReturn($role);
        $validator->validate($role)->willReturn(new ConstraintViolationList());

        $roleWithPermissions = $this->process($item);
        $roleWithPermissions->shouldBeAnInstanceOf(RoleWithPermissions::class);
        $roleWithPermissions->role()->getRole()->shouldBe('ROLE_USER');
        $roleWithPermissions->role()->getLabel()->shouldBe('the new label');
        $roleWithPermissions->allowedPermissionIds()->shouldBe(['type1:privilege1', 'type2:privilege2']);
    }

    function it_throws_an_exception_when_validation_fails(
        RoleRepositoryInterface $roleRepository,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $item = ['role' => 'ROLE_USER', 'label' => 'the new label'];

        $role = new Role('ROLE_USER');
        $role->setLabel('old label');
        $roleRepository->findOneByIdentifier('ROLE_USER')->willReturn($role);
        $validator->validate($role)->willReturn(new ConstraintViolationList([
            new ConstraintViolation('message', null, [], null, null, null),
        ]));

        $stepExecution->getSummaryInfo('item_position')->willReturn(44);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $this->shouldThrow(InvalidItemFromViolationsException::class)->during('process', [$item]);
    }

    function it_throws_an_exception_if_a_permission_does_not_exist(
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $item = ['role' => 'ROLE_NEW', 'label' => 'My new role', 'permissions' => ['unknown_type:unknown']];

        $role = new Role('ROLE_NEW');
        $role->setLabel('My new role');
        $validator->validate($role)->willReturn(new ConstraintViolationList([]));

        $stepExecution->getSummaryInfo('item_position')->willReturn(42);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $this->shouldThrow(InvalidItemException::class)->during('process', [$item]);
    }
}
