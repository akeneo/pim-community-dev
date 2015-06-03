<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\EnrichBundle\Provider\ColorsProvider;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChannelTypeSpec extends ObjectBehavior
{
    function let(
        LocaleManager $localeManager,
        LocaleHelper $localeHelper,
        ColorsProvider $provider
    ) {
        $this->beConstructedWith(
            $localeManager,
            $localeHelper,
            $provider,
            'Pim\Bundle\CatalogBundle\Entity\Category',
            'Pim\Bundle\CatalogBundle\Entity\Channel'
        );
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_channel');
    }

    function it_does_not_map_the_fields_to_the_entity_by_default(OptionsResolverInterface $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => 'Pim\Bundle\CatalogBundle\Entity\Channel',
            ]
        )->shouldHaveBeenCalled();
    }
}
