<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;
use PimEnterprise\Component\Workflow\Builder\ProductDraftBuilderInterface;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DelegatingProductSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        EventDispatcherInterface $eventDispatcher,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductDraftBuilderInterface $filteredProductDraftBuilder,
        TokenStorageInterface $tokenStorage,
        ProductDraftRepositoryInterface $filteredProductDraftRepo,
        RemoverInterface $filteredProductDraftRemover,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        NotGrantedDataMergerInterface $mergeDataOnProduct,
        ProductRepositoryInterface $productRepository
    ) {
        $this->beConstructedWith(
            $objectManager,
            $completenessManager,
            $eventDispatcher,
            $authorizationChecker,
            $filteredProductDraftBuilder,
            $tokenStorage,
            $filteredProductDraftRepo,
            $filteredProductDraftRemover,
            $uniqueDataSynchronizer,
            $mergeDataOnProduct,
            $productRepository
        );
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SaverInterface');
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\BulkSaverInterface');
    }

    function it_saves_the_product_when_user_is_the_owner(
        $objectManager,
        $completenessManager,
        $eventDispatcher,
        $authorizationChecker,
        $tokenStorage,
        $uniqueDataSynchronizer,
        $mergeDataOnProduct,
        $productRepository,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct
    ) {
        $productRepository->find(42)->willReturn($fullProduct);
        $mergeDataOnProduct->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);

        $filteredProduct->getId()->willReturn(42);
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProduct)
            ->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProduct)
            ->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW, $filteredProduct)
            ->willReturn(true);
        $tokenStorage->getToken()->willReturn('token');

        $objectManager->persist($filteredProduct)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $completenessManager->schedule($filteredProduct)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($filteredProduct)->shouldBeCalled();
        $uniqueDataSynchronizer->synchronize($filteredProduct)->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($filteredProduct);
    }

    function it_does_not_save_neither_product_nor_draft_if_the_user_has_only_the_view_permission_on_product(
        $authorizationChecker,
        $tokenStorage,
        $mergeDataOnProduct,
        $objectManager,
        $productRepository,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        TokenInterface $token
    ) {
        $productRepository->find(42)->willReturn($fullProduct);
        $mergeDataOnProduct->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);
        
        $tokenStorage->getToken()->willReturn($token);
        $filteredProduct->getId()->willReturn(42);
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW, $filteredProduct)
            ->willReturn(true);

        $filteredProduct->getIdentifier()->willReturn('sku');

        $objectManager->persist($filteredProduct)->shouldNotBeCalled();
        $objectManager->flush($filteredProduct)->shouldNotBeCalled();

        $this->save($filteredProduct);
    }

    function it_saves_the_product_when_user_is_not_the_owner_and_product_not_exists(
        $objectManager,
        $completenessManager,
        $eventDispatcher,
        $mergeDataOnProduct,
        $productRepository,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct
    ) {
        $productRepository->find(42)->willReturn($fullProduct);
        $mergeDataOnProduct->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);
        
        $filteredProduct->getId()->willReturn(null);

        $objectManager->persist($filteredProduct)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $completenessManager->schedule($filteredProduct)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($filteredProduct)->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($filteredProduct);
    }

    function it_removes_the_existing_product_draft_when_user_is_not_the_owner_and_product_exists_without_changes_anymore(
        $objectManager,
        $authorizationChecker,
        $filteredProductDraftBuilder,
        $tokenStorage,
        $mergeDataOnProduct,
        $filteredProductDraftRepo,
        $filteredProductDraftRemover,
        $productRepository,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        ProductDraftInterface $filteredProductDraft,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $productRepository->find(42)->willReturn($fullProduct);
        $mergeDataOnProduct->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);
        
        $filteredProduct->getId()->willReturn(42);
        $filteredProduct->getIdentifier()->willReturn('sku');
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProduct)
            ->willReturn(true);

        $user->getUsername()->willReturn('username');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $filteredProductDraftBuilder->build($filteredProduct, 'username')
            ->willReturn(null)
            ->shouldBeCalled();

        $filteredProductDraftRepo->findUserProductDraft($filteredProduct, 'username')->willReturn($filteredProductDraft);
        $filteredProductDraftRemover->remove($filteredProductDraft)->shouldBeCalled();

        $objectManager->persist(Argument::any())->shouldNotBeCalled();
        $objectManager->flush()->shouldNotBeCalled();

        $this->save($filteredProduct);
    }

    function it_removes_the_existing_product_draft_when_user_is_not_the_owner_and_product_exists_without_changes_anymore_even_with_edit_rights(
        $objectManager,
        $authorizationChecker,
        $filteredProductDraftBuilder,
        $tokenStorage,
        $mergeDataOnProduct,
        $filteredProductDraftRepo,
        $filteredProductDraftRemover,
        $productRepository,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        ProductDraftInterface $filteredProductDraft,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $productRepository->find(42)->willReturn($fullProduct);
        $mergeDataOnProduct->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);
        
        $filteredProduct->getId()->willReturn(42);
        $filteredProduct->getIdentifier()->willReturn('sku');
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProduct)
            ->willReturn(true);

        $user->getUsername()->willReturn('username');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $filteredProductDraftBuilder->build($filteredProduct, 'username')
            ->willReturn(null)
            ->shouldBeCalled();

        $filteredProductDraftRepo->findUserProductDraft($filteredProduct, 'username')->willReturn($filteredProductDraft);
        $filteredProductDraftRemover->remove($filteredProductDraft)->shouldBeCalled();

        $objectManager->persist(Argument::any())->shouldNotBeCalled();
        $objectManager->flush()->shouldNotBeCalled();

        $this->save($filteredProduct);
    }

    function it_does_not_remove_any_product_draft_when_user_is_not_the_owner_and_product_exists_without_changes_but_the_draft_does_not_exists(
        $objectManager,
        $authorizationChecker,
        $filteredProductDraftBuilder,
        $tokenStorage,
        $mergeDataOnProduct,
        $filteredProductDraftRepo,
        $filteredProductDraftRemover,
        $productRepository,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $productRepository->find(42)->willReturn($fullProduct);
        $mergeDataOnProduct->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);
        
        $filteredProduct->getId()->willReturn(42);
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProduct)
            ->willReturn(true);

        $user->getUsername()->willReturn('username');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $filteredProductDraftBuilder->build($filteredProduct, 'username')
            ->willReturn(null)
            ->shouldBeCalled();

        $filteredProductDraftRepo->findUserProductDraft($filteredProduct, 'username')->willReturn();
        $filteredProductDraftRemover->remove(Argument::any())->shouldNotBeCalled();

        $objectManager->persist(Argument::any())->shouldNotBeCalled();
        $objectManager->flush()->shouldNotBeCalled();

        $this->save($filteredProduct);
    }

    function it_saves_the_product_draft_when_user_is_not_the_owner_and_product_exists_with_changes(
        $objectManager,
        $eventDispatcher,
        $authorizationChecker,
        $filteredProductDraftBuilder,
        $tokenStorage,
        $mergeDataOnProduct,
        $productRepository,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        ProductDraftInterface $filteredProductDraft,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $productRepository->find(42)->willReturn($fullProduct);
        $mergeDataOnProduct->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);
        
        $filteredProduct->getId()->willReturn(42);
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProduct)
            ->willReturn(true);

        $user->getUsername()->willReturn('username');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $filteredProductDraftBuilder->build($filteredProduct, 'username')
            ->willReturn($filteredProductDraft)
            ->shouldBeCalled();

        $objectManager->persist($filteredProductDraft)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $objectManager->refresh($filteredProduct)->shouldBeCalled();

        $this->save($filteredProduct);
    }

    function it_saves_several_product_and_product_drafts_depending_on_user_ownership(
        $objectManager,
        $completenessManager,
        $eventDispatcher,
        $authorizationChecker,
        $filteredProductDraftBuilder,
        $tokenStorage,
        $mergeDataOnProduct,
        $productRepository,
        ProductInterface $filteredOwnedProduct,
        ProductInterface $fullOwnedProduct,
        ProductInterface $filteredNotOwnedProduct,
        ProductInterface $fullNotOwnedProduct,
        ProductDraftInterface $productDraft,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $productRepository->find(42)->willReturn($fullOwnedProduct);
        $mergeDataOnProduct->merge($filteredOwnedProduct, $fullOwnedProduct)->willReturn($fullOwnedProduct);

        $productRepository->find(43)->willReturn($fullNotOwnedProduct);
        $mergeDataOnProduct->merge($filteredNotOwnedProduct, $fullNotOwnedProduct)->willReturn($fullNotOwnedProduct);

        $fullOwnedProduct->getId()->willReturn(42);
        $filteredOwnedProduct->getId()->willReturn(42);
        $completenessManager->generateMissingForProduct($fullOwnedProduct)->shouldBeCalled();
        $authorizationChecker->isGranted(Attributes::OWN, $fullOwnedProduct)
            ->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT, $fullOwnedProduct)
            ->willReturn(true);
        $user->getUsername()->willReturn('username');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $objectManager->persist($fullOwnedProduct)->shouldBeCalled();
        $completenessManager->schedule($fullOwnedProduct)->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();

        $fullNotOwnedProduct->getId()->willReturn(43);
        $filteredNotOwnedProduct->getId()->willReturn(43);
        $authorizationChecker->isGranted(Attributes::OWN, $fullNotOwnedProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $fullNotOwnedProduct)
            ->willReturn(true);

        $filteredProductDraftBuilder->build($fullNotOwnedProduct, 'username')
            ->willReturn($productDraft)
            ->shouldBeCalled();
        $objectManager->persist($productDraft)->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, Argument::cetera())->shouldBeCalled();

        $objectManager->flush()->shouldBeCalledTimes(1);

        $this->saveAll([$filteredOwnedProduct, $filteredNotOwnedProduct]);
    }
}
