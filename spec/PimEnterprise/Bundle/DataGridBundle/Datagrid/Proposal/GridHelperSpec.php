<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Proposal;

use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class GridHelperSpec extends ObjectBehavior
{
    function let(UserManager $userManager, SecurityContextInterface $securityContext, ObjectRepository $userRepository)
    {
        $this->beConstructedWith($userManager, $securityContext);

        $userManager->getRepository()->willReturn($userRepository);
    }

    function it_provides_proposal_author_choices($userRepository, UserInterface $foo, UserInterface $bar)
    {
        $userRepository->findAll()->willReturn([$foo, $bar]);
        $foo->getUsername()->willReturn('foo');
        $bar->getUsername()->willReturn('bar');

        $this->getAuthorChoices()->shouldReturn(
            [
                'foo' => 'foo',
                'bar' => 'bar'
            ]
        );
    }
}
