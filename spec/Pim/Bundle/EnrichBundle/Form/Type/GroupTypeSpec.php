<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupTypeSpec extends ObjectBehavior
{
    function let(ProductRepositoryInterface $productRepository)
    {
        $this->beConstructedWith(
            $productRepository,
            'Pim\Bundle\CatalogBundle\Entity\Attribute',
            'Pim\Bundle\CatalogBundle\Entity\Group'
        );
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_group');
    }

    function it_sets_default_options(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => 'Pim\Bundle\CatalogBundle\Entity\Group',
            ]
        )->shouldHaveBeenCalled();
    }
}
