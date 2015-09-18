<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\StorageEvents;
use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AttributeSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        SavingOptionsResolverInterface $optionsResolver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($objectManager, $optionsResolver, $eventDispatcher);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Saver\SaverInterface');
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Saver\BulkSaverInterface');
    }

    function it_saves_an_attribute_and_flushes_by_default(
        $objectManager,
        $optionsResolver,
        $eventDispatcher,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('my_code');
        $optionsResolver->resolveSaveOptions([])
            ->shouldBeCalled()
            ->willReturn(['flush' => true, 'schedule' => true]);
        $eventDispatcher
            ->dispatch(
                Argument::exact(StorageEvents::PRE_SAVE),
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();
        $objectManager->persist($attribute)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $eventDispatcher
            ->dispatch(
                Argument::exact(StorageEvents::POST_SAVE),
                Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
            )
            ->shouldBeCalled();
        $this->save($attribute);
    }

    function it_saves_an_attribute_and_does_not_flush($objectManager, $optionsResolver, AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('my_code');
        $optionsResolver->resolveSaveOptions(['flush' => false])
            ->shouldBeCalled()
            ->willReturn(['flush' => false]);
        $objectManager->persist($attribute)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();
        $this->save($attribute, ['flush' => false]);
    }

    function it_saves_a_collection_attributes(
        $objectManager,
        $optionsResolver,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2
    ) {
        $attribute1->getCode()->willReturn('my_code1');
        $attribute2->getCode()->willReturn('my_code2');

        $optionsResolver->resolveSaveAllOptions(Argument::any())
            ->willReturn(['flush' => false]);

        $optionsResolver->resolveSaveOptions(['flush' => false])
            ->willReturn(['flush' => false]);

        $objectManager->persist($attribute1)->shouldBeCalled();
        $objectManager->persist($attribute2)->shouldBeCalled();
        $objectManager->flush()->shouldNotBeCalled();

        $attributes = [
            $attribute1,
            $attribute2
        ];

        $this->saveAll($attributes);

        $optionsResolver->resolveSaveAllOptions(Argument::any())
            ->willReturn(['flush' => true]);

        $objectManager->flush()->shouldBeCalled();

        $this->saveAll($attributes, ['flush' => true]);
    }

    function it_throws_exception_when_save_anything_else_than_an_attribute()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a "Pim\Bundle\CatalogBundle\Model\AttributeInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->during('save', [$anythingElse]);
    }
}
