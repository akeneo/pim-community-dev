<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Component\Connector\Processor\Denormalization;

use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\UserManagement\Component\Connector\Processor\Denormalization\RoleWithPermissionsProcessor;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
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
        ObjectDetacherInterface $objectDetacher
    ) {
        $roleRepository->findOneByIdentifier('ROLE_NEW')->willReturn(null);

        $this->beConstructedWith($roleRepository, $validator, $objectDetacher);
    }

    function it_is_a_processor()
    {
        $this->shouldBeAnInstanceOf(RoleWithPermissionsProcessor::class);
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    function it_processes_a_new_role(ValidatorInterface $validator)
    {
        $permissions = [
            [
                'id' => 'id1',
                'name' => 'name1',
                'permissions' => [['access_level' => 0]],
            ],
            [
                'id' => 'id2',
                'name' => 'name2',
                'permissions' => [['access_level' => 1]],
            ],
        ];
        $item = ['role' => 'ROLE_NEW', 'label' => 'the label', 'permissions' => $permissions];

        $validator->validate(Argument::type(Role::class))->willReturn(new ConstraintViolationList());

        $roleWithPermissions = $this->process($item);
        $roleWithPermissions->shouldBeAnInstanceOf(RoleWithPermissions::class);
        $roleWithPermissions->role()->getRole()->shouldBe('ROLE_NEW');
        $roleWithPermissions->role()->getLabel()->shouldBe('the label');
        $roleWithPermissions->allowedPermissionIds()->shouldBe(['id2']);
    }

    function it_processes_an_existing_role(RoleRepositoryInterface $roleRepository, ValidatorInterface $validator)
    {
        $permissions = [
            [
                'id' => 'id1',
                'name' => 'name1',
                'permissions' => [['access_level' => 1]],
            ],
            [
                'id' => 'id2',
                'name' => 'name2',
                'permissions' => [['access_level' => 1]],
            ],
        ];
        $item = ['role' => 'ROLE_USER', 'label' => 'the new label', 'permissions' => $permissions];

        $role = new Role('ROLE_USER');
        $role->setLabel('old label');
        $roleRepository->findOneByIdentifier('ROLE_USER')->willReturn($role);
        $validator->validate($role)->willReturn(new ConstraintViolationList());

        $roleWithPermissions = $this->process($item);
        $roleWithPermissions->shouldBeAnInstanceOf(RoleWithPermissions::class);
        $roleWithPermissions->role()->getRole()->shouldBe('ROLE_USER');
        $roleWithPermissions->role()->getLabel()->shouldBe('the new label');
        $roleWithPermissions->allowedPermissionIds()->shouldBe(['id1', 'id2']);
    }

    function it_throws_an_exception_when_validation_fails(
        RoleRepositoryInterface $roleRepository,
        ValidatorInterface $validator
    ) {
        $permissions = [
            [
                'id' => 'id1',
                'name' => 'name1',
                'permissions' => [['access_level' => 1]],
            ],
        ];
        $item = ['role' => 'ROLE_USER', 'label' => 'the new label', 'permissions' => $permissions];

        $role = new Role('ROLE_USER');
        $role->setLabel('old label');
        $roleRepository->findOneByIdentifier('ROLE_USER')->willReturn($role);
        $validator->validate($role)->willReturn(new ConstraintViolationList([
            new ConstraintViolation('message', null, [], null, null, null),
        ]));

        $this->shouldThrow(InvalidItemFromViolationsException::class)->during('process', [$item]);
    }
}
