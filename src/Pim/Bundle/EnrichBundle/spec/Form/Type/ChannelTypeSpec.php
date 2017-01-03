<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChannelTypeSpec extends ObjectBehavior
{
    function let(
        LocaleRepositoryInterface $localeRepository,
        LocaleHelper $localeHelper,
        TranslatedLabelsProviderInterface $categoryRepository
    ) {
        $this->beConstructedWith(
            $localeRepository,
            $localeHelper,
            $categoryRepository,
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

    function it_does_not_map_the_fields_to_the_entity_by_default(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => 'Pim\Bundle\CatalogBundle\Entity\Channel',
            ]
        )->shouldHaveBeenCalled();
    }
}
