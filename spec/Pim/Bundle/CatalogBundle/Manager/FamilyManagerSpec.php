<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;

class FamilyManagerSpec extends ObjectBehavior
{
    function let(
        FamilyRepositoryInterface $repository,
        UserContext $userContext
    ) {
        $this->beConstructedWith(
            $repository,
            $userContext
        );
    }

    function it_provides_a_choice_list($userContext, $repository)
    {
        $userContext->getCurrentLocaleCode()->willReturn('foo');
        $repository->getChoices(['localeCode' => 'foo'])->willReturn(['foo' => 'foo']);

        $this->getChoices()->shouldReturn(['foo' => 'foo']);
    }
}
