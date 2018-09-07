<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Extension\Formatter\Property\Version;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\User;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class AuthorPropertySpec extends ObjectBehavior
{
    function let(TranslatorInterface $translator, UserManager $userManager)
    {
        $this->beConstructedWith($translator, $userManager);
    }

    function it_prepares_an_author_value($userManager, ResultRecordInterface $record, User $user)
    {
        $record->getValue('author')->willReturn('julia');
        $record->getValue('context')->willReturn(null);
        $userManager->findUserByUsername(Argument::any())->shouldBeCalled()->willReturn($user);
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->getEmail()->willReturn('julia@zaro.com');

        $this->getValue($record)->shouldReturn('Julia Doe - julia@zaro.com');
    }

    function it_prepares_a_removed_author_value($userManager, ResultRecordInterface $record, $translator)
    {
        $record->getValue('author')->willReturn('julia');
        $record->getValue('context')->willReturn(null);
        $userManager->findUserByUsername(Argument::any())->shouldBeCalled()->willReturn(null);
        $translator->trans('pim_user.user.removed_user')->willReturn('Removed user');

        $this->getValue($record)->shouldReturn(' - Removed user');
    }

    function it_prepares_an_author_value_with_context($userManager, ResultRecordInterface $record, User $user)
    {
        $record->getValue('author')->willReturn('julia');
        $record->getValue('context')->willReturn('my context');
        $userManager->findUserByUsername(Argument::any())->shouldBeCalled()->willReturn($user);
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->getEmail()->willReturn('julia@zaro.com');

        $this->getValue($record)->shouldReturn('Julia Doe - julia@zaro.com (my context)');
    }
}
