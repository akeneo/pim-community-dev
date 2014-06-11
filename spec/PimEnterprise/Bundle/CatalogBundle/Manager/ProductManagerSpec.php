<?php

namespace spec\PimEnterprise\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\AvailableAttributes;
use Pim\Bundle\CatalogBundle\Persistence\ProductPersister;
use PimEnterprise\Bundle\WorkflowBundle\Persistence\ProductChangesApplier;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\Security\Core\User\UserInterface;
use PimEnterprise\Bundle\WorkflowBundle\Doctrine\Repository\PropositionRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

class ProductManagerSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        ProductPersister $persister,
        EventDispatcherInterface $eventDispatcher,
        MediaManager $mediaManager,
        ProductBuilder $builder,
        ProductRepositoryInterface $productRepository,
        AssociationTypeRepository $associationTypeRepository,
        AttributeRepository $attributeRepository,
        AttributeOptionRepository $attributeOptionRepository,
        UserContext $userContext,
        PropositionRepositoryInterface $propositionRepository,
        ProductChangesApplier $applier
    ) {
        $this->beConstructedWith(
            [],
            $objectManager,
            $persister,
            $eventDispatcher,
            $mediaManager,
            $builder,
            $productRepository,
            $associationTypeRepository,
            $attributeRepository,
            $attributeOptionRepository,
            $userContext,
            $propositionRepository,
            $applier
        );
    }

    function it_extends_the_community_product_manager()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Manager\ProductManager');
    }

    function it_applies_product_changes_when_finding_one(
        $productRepository,
        $userContext,
        $propositionRepository,
        $applier,
        AbstractProduct $product,
        UserInterface $user,
        Proposition $proposition
    ) {
        $productRepository->findOneByWithValues(42)->willReturn($product);
        $userContext->getUser()->willReturn($user);
        $user->getUsername()->willReturn('julia');
        $propositionRepository->findUserProposition('julia', 'en_US')->willReturn($proposition);
        $proposition->getChanges()->willReturn(['changes']);
        $applier->apply($product, ['changes'])->shouldBeCalled();

        $this->find(42)->shouldReturn($product);
    }

    function it_applies_nothing_if_there_is_no_product(
        $productRepository,
        $applier
    ) {
        $productRepository->findOneByWithValues(42)->willReturn(null);
        $applier->apply(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->find(42)->shouldReturn(null);
    }

    function it_applies_nothing_if_there_is_no_user(
        $productRepository,
        $userContext,
        $applier,
        AbstractProduct $product
    ) {
        $productRepository->findOneByWithValues(42)->willReturn($product);
        $userContext->getUser()->willReturn(null);
        $applier->apply($product, Argument::any())->shouldNotBeCalled();

        $this->find(42)->shouldReturn($product);
    }

    function it_applies_nothing_if_there_is_no_proposition(
        $productRepository,
        $userContext,
        $propositionRepository,
        $applier,
        AbstractProduct $product,
        UserInterface $user
    ) {
        $productRepository->findOneByWithValues(42)->willReturn($product);
        $userContext->getUser()->willReturn($user);
        $user->getUsername()->willReturn('julia');
        $propositionRepository->findUserProposition('julia', 'en_US')->willReturn(null);
        $applier->apply($product, Argument::any())->shouldNotBeCalled();

        $this->find(42)->shouldReturn($product);
    }
}
