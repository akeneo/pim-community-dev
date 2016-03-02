<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Akeneo\Component\FileStorage\Model\FileInfo');
    }

    function it_is_a_file_type()
    {
        $this->beAnInstanceOf('Akeneo\Bundle\FileStorageBundle\Form\Type\FileType');
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_media');
    }

    function it_builds_form(FormBuilderInterface $builder)
    {
        $builder->add('uploadedFile', 'file', ['required' => false])->willReturn($builder);
        $builder->add(
            'removed',
            'checkbox',
            [
                'required' => false,
                'label'    => 'Remove media',
            ]
        )->willReturn($builder);

        $builder->add('id', 'hidden')->willReturn($builder);

        $this->buildForm($builder, []);
    }

    function it_sets_default_options(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => 'Akeneo\Component\FileStorage\Model\FileInfo',
            ]
        )->shouldHaveBeenCalled();
    }
}
