<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\EnrichBundle\Form\Type\EntityIdentifierType;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify;
use Symfony\Component\Form\AbstractType;
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
            Classify::class,
            'pim_enrich_mass_classify'
        );
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf(AbstractType::class);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_enrich_mass_classify');
    }

    function it_sets_default_options(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Classify::class,
        ])->shouldBeCalled();

        $this->configureOptions($resolver, []);
    }

    function it_builds_classify_products_form($categoryRepository, FormBuilderInterface $builder)
    {
        $categoryRepository->getClassName()->willReturn(Category::class);

        $builder ->add(
            'trees',
            EntityIdentifierType::class,
            [
                'class'    => Category::class,
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            ]
        )->shouldBeCalled();

        $builder->add(
            'categories',
            EntityIdentifierType::class,
            [
                'class'    => Category::class,
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
