<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\EnrichBundle\Form\View\ProductFormViewInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditCommonAttributesTypeSpec extends ObjectBehavior
{
    function let(ProductFormViewInterface $productFormView, LocaleHelper $localeHelper)
    {
        $this->beConstructedWith(
            $productFormView,
            $localeHelper,
            'Pim\Bundle\CatalogBundle\Entity\Attribute',
            'Pim\Bundle\CatalogBundle\Entity\Locale',
            'Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes'
        );
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_mass_edit_common_attributes');
    }

    function it_sets_default_options(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => 'Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes',
                'locales' => [],
                'all_attributes' => [],
                'current_locale'    => null
            ]
        )->shouldHaveBeenCalled();
    }
}
