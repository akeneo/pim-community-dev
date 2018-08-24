<?php

namespace spec\Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\CategoryRepository;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Gedmo\Tree\Strategy\ORM\Nested;
use Gedmo\Tree\TreeListener;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Prophecy\Argument;

class CategoryRepositorySpec extends ObjectBehavior
{
    function let(
        UserContext $userContext,
        EntityManager $em,
        ClassMetadata $classMetadata,
        EventManager $eventManager,
        TreeListener $treeListener,
        Nested $strategy
    ) {
        $classMetadata->name = 'category';

        $userContext->getCurrentLocaleCode()->willReturn('en_US');
        $em->getEventManager()->willReturn($eventManager);
        $eventManager->getListeners()->willReturn([[$treeListener]]);

        $treeListener->getStrategy(Argument::cetera())->willReturn($strategy);
        $treeListener->getConfiguration(Argument::cetera())->willReturn([
            'parent' => 'parent',
            'left'   => 'left'
        ]);

        $this->beConstructedWith($userContext, $em, $classMetadata);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CategoryRepository::class);
    }

    function it_provides_translated_data()
    {
        $this->shouldImplement(TranslatedLabelsProviderInterface::class);
    }

    function it_is_a_doctrine_repository()
    {
        $this->shouldHaveType('Doctrine\ORM\EntityRepository');
    }
}
