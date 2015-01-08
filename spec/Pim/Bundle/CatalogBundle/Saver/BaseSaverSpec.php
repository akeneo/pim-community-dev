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
        $this->shouldHaveType('Akeneo\Component\Persistence\BulkSaverInterface');
    }

    function it_persists_the_object_and_flushes_the_unit_of_work($objectManager, $optionsResolver, GroupType $type)
    {
        $optionsResolver->resolveSaveOptions([])
            ->shouldBeCalled()
            ->willReturn(['flush' => true, 'flush_only_object' => false]);

        $objectManager->persist($type)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->save($type);
    }

    function it_persists_the_objects_and_flushes_the_unit_of_work($objectManager, $optionsResolver, GroupType $type1, GroupType $type2)
    {
        $optionsResolver->resolveSaveAllOptions([])
            ->shouldBeCalled()
            ->willReturn(['flush' => true]);

        $optionsResolver->resolveSaveOptions(['flush' => false])
            ->shouldBeCalledTimes(2)
            ->willReturn(['flush' => false, 'flush_only_object' => false]);

        $objectManager->persist($type1)->shouldBeCalled();
        $objectManager->persist($type2)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->saveAll([$type1, $type2]);
    }

    function it_persists_the_object_and_does_not_flush($objectManager, $optionsResolver, GroupType $type)
    {
        $optionsResolver->resolveSaveOptions(['flush' => false])
            ->shouldBeCalled()
            ->willReturn(['flush' => false, 'flush_only_object' => false]);

        $objectManager->persist($type)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();
        $this->save($type, ['flush' => false]);
    }

    function it_persists_the_objects_and_does_not_flush($objectManager, $optionsResolver, GroupType $type1, GroupType $type2)
    {
        $optionsResolver->resolveSaveAllOptions(['flush' => false])
            ->shouldBeCalled()
            ->willReturn(['flush' => false]);

        $optionsResolver->resolveSaveOptions(['flush' => false])
            ->shouldBeCalledTimes(2)
            ->willReturn(['flush' => false, 'flush_only_object' => false]);

        $objectManager->persist($type1)->shouldBeCalled();
        $objectManager->persist($type2)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();
        $this->saveAll([$type1, $type2], ['flush' => false]);
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
        $exception = new \InvalidArgumentException(
            sprintf(
                'Expects a "Pim\Bundle\CatalogBundle\Entity\GroupType", "%s" provided.',
                get_class($anythingElse)
            )
        );
        $this->shouldThrow($exception)->during('save', [$anythingElse]);
        $this->shouldThrow($exception)->during('saveAll', [[$anythingElse, $anythingElse]]);
    }
}
