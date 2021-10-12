<?php

namespace spec\Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Common\Saver;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RuleDefinitionSaverSpec extends ObjectBehavior
{
    function let(
        EntityManager $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $eventDispatcher->dispatch(Argument::any(), Argument::type('string'))->willReturn(Argument::type('object'));
        $this->beConstructedWith(
            $entityManager,
            $eventDispatcher,
            'Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface');
        $this->shouldHaveType('Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface');
    }

    function it_saves_a_rule_object($entityManager)
    {
        $rule = new RuleDefinition();
        $entityManager->persist($rule)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->save($rule);
    }

    function it_saves_multiple_rule_objects($entityManager)
    {
        $rule1 = new RuleDefinition();
        $rule2 = new RuleDefinition();
        $rules = [$rule1, $rule2];

        $entityManager->persist($rule1)->shouldBeCalled();
        $entityManager->persist($rule2)->shouldBeCalled();

        $entityManager->flush()->shouldBeCalledTimes(1);

        $this->saveAll($rules);
    }

    function it_throws_an_exception_if_object_is_not_a_rule_on_save(
        $entityManager,
        ProductInterface $productInterface
    ) {
        $entityManager->persist($productInterface)->shouldNotBeCalled();
        $entityManager->flush()->shouldNotBeCalled();

        $this->shouldThrow('\InvalidArgumentException')->during('save', [$productInterface]);
    }
}
