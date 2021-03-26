<?php

namespace Specification\Akeneo\UserManagement\Component\Connector\Reader\Database;

use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\UserManagement\Component\Connector\Reader\Database\UserRole;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;

class UserRoleSpec extends ObjectBehavior
{
    function let(ObjectRepository $userRoleRepository, StepExecution $stepExecution)
    {
        $this->beConstructedWith($userRoleRepository);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ItemReaderInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
        $this->shouldImplement(InitializableInterface::class);
        $this->shouldHaveType(UserRole::class);
    }

    function it_reads_roles_from_database_and_filters_anonymous_role(
        ObjectRepository $userRoleRepository,
        RoleInterface $roleAdmin,
        RoleInterface $roleUser,
        RoleInterface $anonymous
    ) {
        $roleAdmin->getRole()->willReturn('ROLE_ADMIN');
        $roleUser->getRole()->willReturn('ROLE_USER');
        $anonymous->getRole()->willReturn(User::ROLE_ANONYMOUS);
        $userRoleRepository->findAll()->shouldBeCalledOnce()->willReturn([$roleAdmin, $roleUser, $anonymous]);

        $this->read()->shouldReturn($roleAdmin);
        $this->read()->shouldReturn($roleUser);
        $this->read()->shouldReturn(null);
    }

    function it_counts_total_items_and_filters_anonymous_role(
        ObjectRepository $userRoleRepository,
        RoleInterface $roleAdmin,
        RoleInterface $roleUser,
        RoleInterface $anonymous
    ) {
        $roleAdmin->getRole()->willReturn('ROLE_ADMIN');
        $roleUser->getRole()->willReturn('ROLE_USER');
        $anonymous->getRole()->willReturn(User::ROLE_ANONYMOUS);
        $userRoleRepository->findAll()->shouldBeCalledOnce()->willReturn([$roleAdmin, $roleUser, $anonymous]);

        $this->totalItems()->shouldReturn(2);
    }
}
