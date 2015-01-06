<?php

namespace spec\Pim\Bundle\CatalogBundle\Saver;

use PhpSpec\ObjectBehavior;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Saver\BaseSavingOptionsResolver;
use Prophecy\Argument;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class BaseSaverSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, BaseSavingOptionsResolver $optionsResolver)
    {
        $this->beConstructedWith($objectManager, $optionsResolver, 'Pim\Bundle\CatalogBundle\Entity\GroupType');
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Akeneo\Component\Persistence\SaverInterface');
    }

    function it_persists_the_object_and_flush_the_unit_of_work($objectManager, $optionsResolver, GroupType $type)
    {
        $optionsResolver->resolveSaveOptions([])
            ->shouldBeCalled()
            ->willReturn(['flush' => true, 'flush_only_object' => false]);

        $objectManager->persist($type)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->save($type);
    }

    function it_persists_the_object_and_dont_flush($objectManager, $optionsResolver, GroupType $type)
    {
        $optionsResolver->resolveSaveOptions(['flush' => false])
            ->shouldBeCalled()
            ->willReturn(['flush' => false, 'flush_only_object' => false]);

        $objectManager->persist($type)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();
        $this->save($type, ['flush' => false]);
    }

    function it_persists_the_object_and_flush_only_the_object($objectManager, $optionsResolver, GroupType $type)
    {
        $optionsResolver->resolveSaveOptions(['flush_only_object' => true])
            ->shouldBeCalled()
            ->willReturn(['flush' => true, 'flush_only_object' => true]);

        $objectManager->persist($type)->shouldBeCalled();
        $objectManager->flush($type)->shouldBeCalled();
        $this->save($type, ['flush_only_object' => true]);
    }

    function it_throws_exception_when_save_anything_else_than_the_expected_class()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Pim\Bundle\CatalogBundle\Entity\GroupType", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('save', [$anythingElse]);
    }
}
