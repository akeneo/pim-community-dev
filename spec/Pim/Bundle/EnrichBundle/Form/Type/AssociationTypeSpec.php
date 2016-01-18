<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\AssociationRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssociationTypeSpec extends ObjectBehavior
{
    function let(ProductRepositoryInterface $productRepository, EntityManager $entityManager)
    {
        $this->beConstructedWith(
            $productRepository,
            $entityManager,
            'Pim\Bundle\CatalogBundle\Model\Product',
            'Pim\Bundle\CatalogBundle\Entity\AssociationType',
            'Pim\Bundle\CatalogBundle\Entity\Group',
            'Pim\Bundle\CatalogBundle\Model\AssociationInterface'
        );
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Form\AbstractType');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_enrich_association');
    }

    function it_builds_form(
        $entityManager,
        $productRepository,
        FormBuilderInterface $builder,
        GroupRepositoryInterface $groupRepository,
        AssociationRepositoryInterface $associationRepository
    ) {
        $entityManager->getRepository('Pim\Bundle\CatalogBundle\Entity\Group')->willReturn($groupRepository);
        $entityManager->getRepository('Pim\Bundle\CatalogBundle\Entity\AssociationType')->willReturn(
            $associationRepository
        );

        $builder
        ->add(
            'associationType',
            'pim_object_identifier',
            [
                'repository' => $associationRepository,
                'multiple'   => false
            ]
        )->willReturn($builder);

        $builder->add(
            'appendProducts',
            'pim_object_identifier',
            [
                'repository' => $productRepository,
                'mapped'     => false,
                'required'   => false,
                'multiple'   => true
            ]
        )->willReturn($builder);

        $builder->add(
            'removeProducts',
            'pim_object_identifier',
            [
                'repository' => $productRepository,
                'mapped'     => false,
                'required'   => false,
                'multiple'   => true
            ]
        )->willReturn($builder);

        $builder->add(
            'appendGroups',
            'pim_object_identifier',
            [
                'repository' => $groupRepository,
                'mapped'     => false,
                'required'   => false,
                'multiple'   => true
            ]
        )->willReturn($builder);

        $builder->add(
            'removeGroups',
            'pim_object_identifier',
            [
                'repository' => $groupRepository,
                'mapped'     => false,
                'required'   => false,
                'multiple'   => true
            ]
        )->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_sets_default_options(OptionsResolver $resolver)
    {
        $this->setDefaultOptions($resolver, []);

        $resolver->setDefaults(
            [
                'data_class' => 'Pim\Bundle\CatalogBundle\Model\AssociationInterface',
            ]
        )->shouldHaveBeenCalled();
    }
}
