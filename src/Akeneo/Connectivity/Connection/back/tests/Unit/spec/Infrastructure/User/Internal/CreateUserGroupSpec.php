<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\User\Internal;

use Akeneo\Connectivity\Connection\Application\User\CreateUserGroupInterface;
use Akeneo\Connectivity\Connection\Infrastructure\User\Internal\CreateUserGroup;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateUserGroupSpec extends ObjectBehavior
{
    public function let(
        SimpleFactoryInterface $userGroupFactory,
        ObjectUpdaterInterface $userGroupUpdater,
        SaverInterface $userGroupSaver,
        ValidatorInterface $validator
    ): void {
        $this->beConstructedWith($userGroupFactory, $userGroupUpdater, $userGroupSaver, $validator);
    }

    public function it_is_a_create_user_group(): void
    {
        $this->shouldHaveType(CreateUserGroup::class);
        $this->shouldImplement(CreateUserGroupInterface::class);
    }

    public function it_creates_a_user_group(
        SimpleFactoryInterface $userGroupFactory,
        ObjectUpdaterInterface $userGroupUpdater,
        SaverInterface $userGroupSaver,
        ValidatorInterface $validator
    ): void {
        $group = new Group();
        $userGroupFactory->create()->willReturn($group);

        $violations = new ConstraintViolationList([]);
        $validator->validate($group)->willReturn($violations);
        $userGroupSaver->save($group)->shouldBeCalled();

        $this->execute('NEW GROUP NAME')->shouldReturn($group);
    }

    public function it_does_not_create_an_invalid_group(
        SimpleFactoryInterface $userGroupFactory,
        ObjectUpdaterInterface $userGroupUpdater,
        SaverInterface $userGroupSaver,
        ValidatorInterface $validator
    ): void {
        $group = new Group();
        $userGroupFactory->create()->willReturn($group);

        $violation1 = new ConstraintViolation(
            'an_error',
            '',
            [],
            '',
            'a_path',
            'invalid'
        );
        $violation2 = new ConstraintViolation(
            'an_error2',
            '',
            [],
            '',
            'a_path2',
            'invalid'
        );
        $violations = new ConstraintViolationList([$violation1, $violation2]);
        $validator->validate($group)->willReturn($violations);
        $userGroupSaver->save($group)->shouldNotBeCalled();

        $this
            ->shouldThrow(
                new \LogicException('The user group creation failed :\na_path: an_error\na_path2: an_error2')
            )
            ->during('execute', ['NEW GROUP NAME']);
    }
}
