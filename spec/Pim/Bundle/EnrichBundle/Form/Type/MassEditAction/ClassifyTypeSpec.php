<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type\MassEditAction;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClassifyTypeSpec extends ObjectBehavior
{
    function let(
        CategoryManager $categoryManager,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $categoryRepository->findBy(['parent' => null])->willReturn(['this', 'is', 'a', 'category', 'tree']);
        $categoryManager->getEntityRepository()->willReturn($categoryRepository);

        $this->beConstructedWith(
            $categoryManager,
            'Pim\Bundle\CatalogBundle\Entity\Category',
            'Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify'
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
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => 'Pim\Bundle\EnrichBundle\MassEditAction\Operation\Classify',
            ]
        )->shouldHaveBeenCalled();
    }

    function it_builds_classify_products_form(FormBuilderInterface $builder)
    {
        $builder
            ->add(
                'trees',
                'oro_entity_identifier',
                [
                    'class'    => 'Pim\Bundle\CatalogBundle\Entity\Category',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                ]
            )->shouldBeCalled();

        $builder
            ->add(
                'categories',
                'oro_entity_identifier',
                [
                    'class'    => 'Pim\Bundle\CatalogBundle\Entity\Category',
                    'required' => true,
                    'mapped'   => true,
                    'multiple' => true,
                ]
            )->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_returns_category_trees()
    {
        $this->getTrees()->shouldReturn(['this', 'is', 'a', 'category', 'tree']);
    }
}
