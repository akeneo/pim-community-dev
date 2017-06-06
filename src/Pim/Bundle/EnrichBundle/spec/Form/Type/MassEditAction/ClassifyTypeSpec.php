<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClassifyTypeSpec extends ObjectBehavior
{
    function let(CategoryRepositoryInterface $categoryRepository)
    {
        $this->beConstructedWith(
            $categoryRepository,
            'Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify',
            'pim_enrich_mass_classify'
        );
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_mass_classify');
    }

    function it_sets_default_options(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify',
        ])->shouldBeCalled();

        $this->configureOptions($resolver, []);
    }

    function it_builds_classify_products_form($categoryRepository, FormBuilderInterface $builder)
    {
        $categoryRepository->getClassName()->willReturn('Pim\Bundle\CatalogBundle\Entity\Category');

        $builder ->add(
            'trees',
            'pim_enrich_entity_identifier',
            [
                'class'    => 'Pim\Bundle\CatalogBundle\Entity\Category',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            ]
        )->shouldBeCalled();

        $builder->add(
            'categories',
            'pim_enrich_entity_identifier',
            [
                'class'    => 'Pim\Bundle\CatalogBundle\Entity\Category',
                'required' => true,
                'mapped'   => true,
                'multiple' => true,
            ]
        )->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_builds_view($categoryRepository, FormView $view, FormInterface $form)
    {
        $categoryRepository->findBy(['parent' => null])->shouldBeCalled();

        $this->buildView($view, $form, []);
    }
}
