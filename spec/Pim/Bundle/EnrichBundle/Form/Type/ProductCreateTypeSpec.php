<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormBuilderInterface;

class ProductCreateTypeSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_product_create');
    }

    function it_has_a_parent()
    {
        $this->getParent()->shouldReturn('pim_product');
    }

    function it_builds_form(FormBuilderInterface $builder, $repository)
    {
        $builder->add(
            'values',
            'collection',
            [
                'type'               => 'pim_product_value',
                'allow_add'          => true,
                'allow_delete'       => true,
                'by_reference'       => false,
                'cascade_validation' => true,
            ]
        )->shouldBeCalled()->willReturn($builder);

        $builder->add(
            'family',
            'pim_async_select',
            [
                'repository' => $repository,
                'route'      => 'pim_enrich_family_rest_index',
                'required'   => false,
                'attr'       => [
                    'data-placeholder' => 'Choose a family'
                ],
            ]
        )->shouldBeCalled();

        $this->buildForm($builder, []);
    }
}
