<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MetricTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            'Pim\Bundle\CatalogBundle\Model\Metric'
        );
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_metric');
    }

    function it_sets_default_options(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => 'Pim\Bundle\CatalogBundle\Model\Metric',
                'units'        => [],
                'default_unit' => null,
                'family'       => null
            ]
        )->shouldHaveBeenCalled();
    }
}
