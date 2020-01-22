<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\InternalApi;

use Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Gedmo\Tree\Strategy\ORM\Nested;
use Gedmo\Tree\TreeListener;
use PhpSpec\ObjectBehavior;
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
        $em->getClassMetadata('category')->willReturn($classMetadata);

        $userContext->getCurrentLocaleCode()->willReturn('en_US');
        $em->getEventManager()->willReturn($eventManager);
        $eventManager->getListeners()->willReturn([[$treeListener]]);

        $treeListener->getStrategy(Argument::cetera())->willReturn($strategy);
        $treeListener->getConfiguration(Argument::cetera())->willReturn([
            'parent' => 'parent',
            'left'   => 'left'
        ]);

        $this->beConstructedWith($userContext, $em, 'category');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(\Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\InternalApi\CategoryRepository::class);
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
