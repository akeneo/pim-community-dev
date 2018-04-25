<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Form\Applier;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class ProductDraftChangesApplierSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $dispatcher
    ) {
        $this->beConstructedWith($formFactory, $dispatcher);
    }

    function it_uses_form_factory_to_apply_data_to_a_product_value(
        $formFactory,
        FormBuilderInterface $formBuilder,
        FormInterface $form,
        ProductInterface $product,
        EntityWithValuesDraftInterface $productDraft
    ) {
        $formFactory->createBuilder('form', $product, ['csrf_protection' => false])->willReturn($formBuilder);
        $valuesFieldOptions = [
            'type'               => 'pim_product_value',
            'allow_add'          => false,
            'allow_delete'       => false,
            'by_reference'       => false,
            'cascade_validation' => true,
            'currentLocale'      => null,
            'comparisonLocale'   => null,
        ];
        $formBuilder->add('values', 'pim_enrich_localized_collection', $valuesFieldOptions)->willReturn($formBuilder);
        $formBuilder->addEventListener(FormEvents::PRE_SUBMIT, Argument::any())->willReturn($formBuilder);
        $formBuilder->getForm()->willReturn($form);
        $form->all()->willReturn([]);

        $productDraft->getChanges()->willReturn(['foo' => 'bar']);
        $form->submit(['foo' => 'bar'], false)->shouldBeCalled();

        $this->apply($product, $productDraft);
    }
}
