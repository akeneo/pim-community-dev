<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Proposal;

use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposal;
use Symfony\Component\Security\Core\User\UserInterface;

class GridHelperSpec extends ObjectBehavior
{
    function let(UserManager $userManager, ObjectRepository $userRepository)
    {
        $this->beConstructedWith($userManager);

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
