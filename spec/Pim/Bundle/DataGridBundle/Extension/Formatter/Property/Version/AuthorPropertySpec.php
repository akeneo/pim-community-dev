<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\Formatter\Property\Version;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Pim\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class AuthorPropertySpec extends ObjectBehavior
{
    function let(TranslatorInterface $translator, IdentifiableObjectRepositoryInterface $repository)
    {
        $this->beConstructedWith($translator, $repository);
    }

    function it_prepares_an_author_value($repository, ResultRecordInterface $record, User $user)
    {
        $record->getValue('author')->willReturn('julia');
        $record->getValue('context')->willReturn(null);
        $repository->findOneByIdentifier(Argument::any())->shouldBeCalled()->willReturn($user);
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->getEmail()->willReturn('julia@zaro.com');

        $this->getValue($record)->shouldReturn('Julia Doe - julia@zaro.com');
    }

    function it_prepares_a_removed_author_value($repository, ResultRecordInterface $record, $translator)
    {
        $record->getValue('author')->willReturn('julia');
        $record->getValue('context')->willReturn(null);
        $repository->findOneByIdentifier(Argument::any())->shouldBeCalled()->willReturn(null);
        $translator->trans('Removed user')->willReturn('Removed user');

        $this->getValue($record)->shouldReturn(' - Removed user');
    }

    function it_prepares_an_author_value_with_context($repository, ResultRecordInterface $record, User $user)
    {
        $record->getValue('author')->willReturn('julia');
        $record->getValue('context')->willReturn('my context');
        $repository->findOneByIdentifier(Argument::any())->shouldBeCalled()->willReturn($user);
        $user->getFirstName()->willReturn('Julia');
        $user->getLastName()->willReturn('Doe');
        $user->getEmail()->willReturn('julia@zaro.com');

        $this->getValue($record)->shouldReturn('Julia Doe - julia@zaro.com (my context)');
    }
}
