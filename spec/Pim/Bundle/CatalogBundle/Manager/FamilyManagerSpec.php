<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FamilyManagerSpec extends ObjectBehavior
{
    function let(
        FamilyRepositoryInterface $repository,
        UserContext $userContext,
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        CompletenessManager $completenessManager
    ) {
        $this->beConstructedWith(
            $repository,
            $userContext,
            $objectManager,
            $eventDispatcher,
            $completenessManager
        );
    }

    function it_provides_a_choice_list($userContext, $repository)
    {
        $userContext->getCurrentLocaleCode()->willReturn('foo');
        $repository->getChoices(['localeCode' => 'foo'])->willReturn(['foo' => 'foo']);

        $this->getChoices()->shouldReturn(['foo' => 'foo']);
    }
}
