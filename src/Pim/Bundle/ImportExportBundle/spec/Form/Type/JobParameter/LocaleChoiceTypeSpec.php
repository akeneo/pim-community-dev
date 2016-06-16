<?php

namespace spec\Pim\Bundle\ImportExportBundle\Form\Type\JobParameter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocaleChoiceTypeSpec extends ObjectBehavior
{
    function let(LocaleRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_a_form_type()
    {
        $this->shouldImplement('Symfony\Component\Form\FormTypeInterface');
    }

    function it_is_a_child_of_choice()
    {
        $this->getParent()->shouldReturn('choice');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_import_export_product_export_locale_choice');
    }

    function it_configure_options($repository, OptionsResolver $resolver)
    {
        $repository->getActivatedLocaleCodes()->willReturn(['fr_FR', 'en_US']);
        $resolver->setDefaults([
            'choices' => ['fr_FR' => 'fr_FR', 'en_US' => 'en_US'],
            'required' => true,
            'select2'  => true,
            'multiple' => true,
            'label'    => 'pim_connector.export.locales.label',
            'help'     => 'pim_connector.export.locales.help',
            'attr'     => ['data-tab' => 'content']
        ])->shouldBeCalled();

        $this->configureOptions($resolver);
    }
}
