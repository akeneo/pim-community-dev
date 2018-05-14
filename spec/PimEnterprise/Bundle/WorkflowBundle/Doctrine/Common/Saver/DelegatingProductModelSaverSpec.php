<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;
use PimEnterprise\Component\Workflow\Builder\EntityWithValuesDraftBuilderInterface;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use PimEnterprise\Component\Workflow\Repository\EntityWithValuesDraftRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DelegatingProductModelSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        SaverInterface $productModelSaver,
        SaverInterface $productModelDraftSaver,
        EventDispatcherInterface $eventDispatcher,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        EntityWithValuesDraftBuilderInterface $draftBuilder,
        RemoverInterface $productDraftRemover,
        NotGrantedDataMergerInterface $mergeDataOnProductModel,
        ProductModelRepositoryInterface $productModelRepository,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository
    ) {
        $this->beConstructedWith(
            $objectManager,
            $productModelSaver,
            $productModelDraftSaver,
            $eventDispatcher,
            $authorizationChecker,
            $tokenStorage,
            $draftBuilder,
            $productDraftRemover,
            $mergeDataOnProductModel,
            $productModelRepository,
            $productModelDraftRepository
        );
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType(SaverInterface::class);
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldHaveType(BulkSaverInterface::class);
    }

    function it_saves_the_product_model_when_user_is_the_owner(
        $objectManager,
        $eventDispatcher,
        $authorizationChecker,
        $tokenStorage,
        $mergeDataOnProductModel,
        $productModelRepository,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct
    ) {
        $productModelRepository->find(42)->willReturn($fullProduct);
        $mergeDataOnProductModel->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);

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

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($filteredProduct);
    }

    function it_does_not_save_neither_product_model_nor_draft_if_the_user_has_only_the_view_permission_on_product_model(
        $authorizationChecker,
        $tokenStorage,
        $mergeDataOnProductModel,
        $objectManager,
        $productModelRepository,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        TokenInterface $token
    ) {
        $productModelRepository->find(42)->willReturn($fullProduct);
        $mergeDataOnProductModel->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);
        
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

    function it_saves_the_product_model_when_user_is_not_the_owner_and_product_not_exists(
        $objectManager,
        $eventDispatcher,
        $mergeDataOnProductModel,
        ProductInterface $filteredProduct
    ) {
        $mergeDataOnProductModel->merge($filteredProduct)->willReturn($filteredProduct);
        
        $filteredProduct->getId()->willReturn(null);

        $objectManager->persist($filteredProduct)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($filteredProduct);
    }

    function it_removes_the_existing_product_model_draft_when_user_is_not_the_owner_and_product_model_exists_without_changes_anymore(
        $objectManager,
        $authorizationChecker,
        $filteredProductDraftBuilder,
        $tokenStorage,
        $mergeDataOnProductModel,
        $filteredProductDraftRepo,
        $filteredProductDraftRemover,
        $productModelRepository,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        EntityWithValuesDraftInterface $filteredProductDraft,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $productModelRepository->find(42)->willReturn($fullProduct);
        $mergeDataOnProductModel->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);
        
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

        $filteredProductDraftRepo->findUserEntityWithValuesDraft($filteredProduct, 'username')->willReturn($filteredProductDraft);
        $filteredProductDraftRemover->remove($filteredProductDraft)->shouldBeCalled();

        $objectManager->persist(Argument::any())->shouldNotBeCalled();
        $objectManager->flush()->shouldNotBeCalled();

        $this->save($filteredProduct);
    }

    function it_removes_the_existing_product_model_draft_when_user_is_not_the_owner_and_product_model_exists_without_changes_anymore_even_with_edit_rights(
        $objectManager,
        $authorizationChecker,
        $filteredProductDraftBuilder,
        $tokenStorage,
        $mergeDataOnProductModel,
        $filteredProductDraftRepo,
        $filteredProductDraftRemover,
        $productModelRepository,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        EntityWithValuesDraftInterface $filteredProductDraft,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $productModelRepository->find(42)->willReturn($fullProduct);
        $mergeDataOnProductModel->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);
        
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

        $filteredProductDraftRepo->findUserEntityWithValuesDraft($filteredProduct, 'username')->willReturn($filteredProductDraft);
        $filteredProductDraftRemover->remove($filteredProductDraft)->shouldBeCalled();

        $objectManager->persist(Argument::any())->shouldNotBeCalled();
        $objectManager->flush()->shouldNotBeCalled();

        $this->save($filteredProduct);
    }

    function it_does_not_remove_any_product_model_draft_when_user_is_not_the_owner_and_product_model_exists_without_changes_but_the_draft_does_not_exists(
        $objectManager,
        $authorizationChecker,
        $filteredProductDraftBuilder,
        $tokenStorage,
        $mergeDataOnProductModel,
        $filteredProductDraftRepo,
        $filteredProductDraftRemover,
        $productModelRepository,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $productModelRepository->find(42)->willReturn($fullProduct);
        $mergeDataOnProductModel->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);
        
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

        $filteredProductDraftRepo->findUserEntityWithValuesDraft($filteredProduct, 'username')->willReturn();
        $filteredProductDraftRemover->remove(Argument::any())->shouldNotBeCalled();

        $objectManager->persist(Argument::any())->shouldNotBeCalled();
        $objectManager->flush()->shouldNotBeCalled();

        $this->save($filteredProduct);
    }

    function it_saves_the_product_model_draft_when_user_is_not_the_owner_and_product_model_exists_with_changes(
        $objectManager,
        $eventDispatcher,
        $authorizationChecker,
        $filteredProductDraftBuilder,
        $tokenStorage,
        $mergeDataOnProductModel,
        $productModelRepository,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        EntityWithValuesDraftInterface $filteredProductDraft,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $productModelRepository->find(42)->willReturn($fullProduct);
        $mergeDataOnProductModel->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);
        
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

    function it_saves_several_product_model_and_product_drafts_depending_on_user_ownership(
        $objectManager,
        $completenessManager,
        $eventDispatcher,
        $authorizationChecker,
        $filteredProductDraftBuilder,
        $tokenStorage,
        $mergeDataOnProductModel,
        $productModelRepository,
        ProductInterface $filteredOwnedProduct,
        ProductInterface $fullOwnedProduct,
        ProductInterface $filteredNotOwnedProduct,
        ProductInterface $fullNotOwnedProduct,
        EntityWithValuesDraftInterface $productDraft,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $productModelRepository->find(42)->willReturn($fullOwnedProduct);
        $mergeDataOnProductModel->merge($filteredOwnedProduct, $fullOwnedProduct)->willReturn($fullOwnedProduct);

        $productModelRepository->find(43)->willReturn($fullNotOwnedProduct);
        $mergeDataOnProductModel->merge($filteredNotOwnedProduct, $fullNotOwnedProduct)->willReturn($fullNotOwnedProduct);

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
