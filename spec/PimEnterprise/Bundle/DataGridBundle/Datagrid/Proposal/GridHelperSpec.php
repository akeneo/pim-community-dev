<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Datagrid\Proposal;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GridHelperSpec extends ObjectBehavior
{
    function let(ProductDraftRepositoryInterface $repository, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->beConstructedWith($repository, $authorizationChecker);
    }

    function it_provides_proposal_author_choices($repository)
    {
        $repository->getDistinctAuthors()->willReturn(['bar', 'foo']);

        $this->getAuthorChoices()->shouldReturn(
            [
                'bar' => 'bar',
                'foo' => 'foo'
            ]
        );
    }
}
