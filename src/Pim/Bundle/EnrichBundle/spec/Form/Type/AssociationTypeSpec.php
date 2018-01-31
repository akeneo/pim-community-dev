<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\EnrichBundle\Form\Type\ObjectIdentifierType;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Repository\AssociationRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssociationTypeSpec extends ObjectBehavior
{
    function let(ProductRepositoryInterface $productRepository, EntityManager $entityManager)
    {
        $this->beConstructedWith(
            $productRepository,
            $entityManager,
            Product::class,
            AssociationType::class,
            Group::class,
            AssociationInterface::class
        );
    }

    function it_is_a_form_type()
    {
        $this->shouldBeAnInstanceOf(AbstractType::class);
    }

    function it_has_a_block_prefix()
    {
        $this->getBlockPrefix()->shouldReturn('pim_enrich_association');
    }

    function it_builds_form(
        $entityManager,
        $productRepository,
        FormBuilderInterface $builder,
        GroupRepositoryInterface $groupRepository,
        AssociationRepositoryInterface $associationRepository
    ) {
        $entityManager->getRepository(Group::class)->willReturn($groupRepository);
        $entityManager->getRepository(AssociationType::class)->willReturn(
            $associationRepository
        );

        $builder
        ->add(
            'associationType',
            ObjectIdentifierType::class,
            [
                'repository' => $associationRepository,
                'multiple'   => false
            ]
        )->willReturn($builder);

        $builder->add(
            'appendProducts',
            ObjectIdentifierType::class,
            [
                'repository' => $productRepository,
                'mapped'     => false,
                'required'   => false,
                'multiple'   => true
            ]
        )->willReturn($builder);

        $builder->add(
            'removeProducts',
            ObjectIdentifierType::class,
            [
                'repository' => $productRepository,
                'mapped'     => false,
                'required'   => false,
                'multiple'   => true
            ]
        )->willReturn($builder);

        $builder->add(
            'appendGroups',
            ObjectIdentifierType::class,
            [
                'repository' => $groupRepository,
                'mapped'     => false,
                'required'   => false,
                'multiple'   => true
            ]
        )->willReturn($builder);

        $builder->add(
            'removeGroups',
            ObjectIdentifierType::class,
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
        $this->configureOptions($resolver);

        $resolver->setDefaults(
            [
                'data_class' => AssociationInterface::class,
            ]
        )->shouldHaveBeenCalled();
    }
}
