<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Remover;

use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Event\AttributeEvents;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductTemplateBuilderInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductTemplate;
use Pim\Bundle\CatalogBundle\Repository\ProductTemplateRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AttributeRemoverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        RemovingOptionsResolverInterface $optionsResolver,
        EventDispatcherInterface $eventDispatcher,
        ProductTemplateBuilderInterface $productTemplateBuilder,
        ProductTemplateRepositoryInterface $productTemplateRepository
    ) {
        $this->beConstructedWith($objectManager, $optionsResolver, $eventDispatcher, $productTemplateBuilder, $productTemplateRepository);
    }

    function it_is_a_remover()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Remover\RemoverInterface');
    }

    function it_dispatches_an_event_when_removing_an_attribute(
        $eventDispatcher,
        $objectManager,
        $optionsResolver,
        AttributeInterface $attribute,
        ProductTemplateBuilderInterface $productTemplateBuilder,
        ProductTemplateRepositoryInterface $productTemplateRepository
    ) {
        $optionsResolver->resolveRemoveOptions([])->willReturn(['flush' => true]);
        $eventDispatcher->dispatch(
            AttributeEvents::PRE_REMOVE,
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
        )->shouldBeCalled();

        $objectManager->remove($attribute)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(
            AttributeEvents::POST_REMOVE,
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
        )->shouldBeCalled();

        $objectManager->getRepository('Pim\CatalogBundle\Entity\ProductTemplate')->willReturn($productTemplateRepository);
        $productTemplateRepository->findAll()->willReturn([]);

        $this->remove($attribute);
    }

    function it_removes_an_empty_product_template_if_attribute_has_been_deleted(
        $eventDispatcher,
        $objectManager,
        $optionsResolver,
        AttributeInterface $attribute,
        ProductTemplateBuilderInterface $productTemplateBuilder,
        ProductTemplateRepositoryInterface $productTemplateRepository,
        ProductTemplate $productTemplate
    ) {
        $optionsResolver->resolveRemoveOptions([])->willReturn(['flush' => true]);

        $objectManager->remove($attribute)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $attribute->getCode()->willReturn('test');

        $productTemplate->getValuesData()->willReturn(['test']);
        $productTemplateRepository->findAll()->willReturn([$productTemplate]);
        $productTemplate->hasValueForAttribute($attribute)->willReturn(true);
        $productTemplateBuilder->removeAttribute($productTemplate, $attribute)->shouldBeCalled();
        $productTemplate->getAttributeCodes()->willReturn([]);
        $objectManager->remove($productTemplate)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->remove($attribute);
    }

    function it_updates_a_product_template_if_attribute_has_been_deleted(
        $eventDispatcher,
        $objectManager,
        $optionsResolver,
        AttributeInterface $attribute,
        ProductTemplateBuilderInterface $productTemplateBuilder,
        ProductTemplateRepositoryInterface $productTemplateRepository,
        ProductTemplate $productTemplate
    ) {
        $optionsResolver->resolveRemoveOptions([])->willReturn(['flush' => true]);

        $objectManager->remove($attribute)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $attribute->getCode()->willReturn('test');

        $productTemplate->getValuesData()->willReturn(['test', 'test2']);
        $productTemplateRepository->findAll()->willReturn([$productTemplate]);
        $productTemplate->hasValueForAttribute($attribute)->willReturn(true);
        $productTemplateBuilder->removeAttribute($productTemplate, $attribute)->shouldBeCalled();
        $productTemplate->getAttributeCodes()->willReturn(['test2']);
        $objectManager->persist($productTemplate)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->remove($attribute);
    }

    function it_throws_exception_when_remove_anything_else_than_an_attribute()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects an "Pim\Bundle\CatalogBundle\Model\AttributeInterface", "%s" provided.',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringRemove($anythingElse);
    }
}
