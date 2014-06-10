<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Persistence;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductChangesApplierSpec extends ObjectBehavior
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
        Model\AbstractProduct $product
    ) {
        $formFactory->createBuilder('form', $product)->willReturn($formBuilder);
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
        $formBuilder->getForm()->willReturn($form);
        $form->submit(['foo' => 'bar'], false)->shouldBeCalled();

        $this->apply($product, ['foo' => 'bar']);
    }
}
