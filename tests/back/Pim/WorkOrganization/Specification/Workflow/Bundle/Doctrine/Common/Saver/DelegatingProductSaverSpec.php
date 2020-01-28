<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductUniqueDataSynchronizer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
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
        EventDispatcherInterface $eventDispatcher,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftBuilderInterface $filteredProductDraftBuilder,
        TokenStorageInterface $tokenStorage,
        EntityWithValuesDraftRepositoryInterface $filteredProductDraftRepo,
        RemoverInterface $filteredProductDraftRemover,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        NotGrantedDataMergerInterface $mergeDataOnProduct,
        ProductRepositoryInterface $productRepository,
        PimUserDraftSourceFactory $draftSourceFactory
    ) {
        $this->beConstructedWith(
            $objectManager,
            $eventDispatcher,
            $authorizationChecker,
            $filteredProductDraftBuilder,
            $tokenStorage,
            $filteredProductDraftRepo,
            $filteredProductDraftRemover,
            $uniqueDataSynchronizer,
            $mergeDataOnProduct,
            $productRepository,
            $draftSourceFactory
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

    function it_saves_the_product_when_user_is_the_owner(
        $objectManager,
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
        $eventDispatcher,
        $mergeDataOnProduct,
        ProductInterface $filteredProduct
    ) {
        $mergeDataOnProduct->merge($filteredProduct)->willReturn($filteredProduct);

        $filteredProduct->getId()->willReturn(null);

        $objectManager->persist($filteredProduct)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

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
        $draftSourceFactory,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        EntityWithValuesDraftInterface $filteredProductDraft,
        UsernamePasswordToken $token,
        UserInterface $user,
        DraftSource $draftSource
    ) {
        $this->prepareDraftSource($tokenStorage, $token, $user, $draftSource, $draftSourceFactory);

        $productRepository->find(42)->willReturn($fullProduct);
        $mergeDataOnProduct->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);

        $filteredProduct->getId()->willReturn(42);
        $filteredProduct->getIdentifier()->willReturn('sku');
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProduct)
            ->willReturn(true);

        $filteredProductDraftBuilder->build($filteredProduct, $draftSource)
            ->willReturn(null)
            ->shouldBeCalled();

        $filteredProductDraftRepo->findUserEntityWithValuesDraft($filteredProduct, 'username')->willReturn($filteredProductDraft);
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
        $draftSourceFactory,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        EntityWithValuesDraftInterface $filteredProductDraft,
        UsernamePasswordToken $token,
        UserInterface $user,
        DraftSource $draftSource
    ) {
        $this->prepareDraftSource($tokenStorage, $token, $user, $draftSource, $draftSourceFactory);

        $productRepository->find(42)->willReturn($fullProduct);
        $mergeDataOnProduct->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);

        $filteredProduct->getId()->willReturn(42);
        $filteredProduct->getIdentifier()->willReturn('sku');
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProduct)
            ->willReturn(true);

        $filteredProductDraftBuilder->build($filteredProduct, $draftSource)
            ->willReturn(null)
            ->shouldBeCalled();

        $filteredProductDraftRepo->findUserEntityWithValuesDraft($filteredProduct, 'username')->willReturn($filteredProductDraft);
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
        $draftSourceFactory,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        UsernamePasswordToken $token,
        UserInterface $user,
        DraftSource $draftSource
    ) {
        $this->prepareDraftSource($tokenStorage, $token, $user, $draftSource, $draftSourceFactory);

        $productRepository->find(42)->willReturn($fullProduct);
        $mergeDataOnProduct->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);

        $filteredProduct->getId()->willReturn(42);
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProduct)
            ->willReturn(true);

        $filteredProductDraftBuilder->build($filteredProduct, $draftSource)
            ->willReturn(null)
            ->shouldBeCalled();

        $filteredProductDraftRepo->findUserEntityWithValuesDraft($filteredProduct, 'username')->willReturn();
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
        $draftSourceFactory,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        EntityWithValuesDraftInterface $filteredProductDraft,
        UsernamePasswordToken $token,
        UserInterface $user,
        DraftSource $draftSource
    ) {
        $this->prepareDraftSource($tokenStorage, $token, $user, $draftSource, $draftSourceFactory);

        $productRepository->find(42)->willReturn($fullProduct);
        $mergeDataOnProduct->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);

        $filteredProduct->getId()->willReturn(42);
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProduct)
            ->willReturn(true);

        $filteredProductDraftBuilder->build($filteredProduct, $draftSource)
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
        $eventDispatcher,
        $authorizationChecker,
        $filteredProductDraftBuilder,
        $tokenStorage,
        $mergeDataOnProduct,
        $productRepository,
        $draftSourceFactory,
        ProductInterface $filteredOwnedProduct,
        ProductInterface $fullOwnedProduct,
        ProductInterface $filteredNotOwnedProduct,
        ProductInterface $fullNotOwnedProduct,
        EntityWithValuesDraftInterface $productDraft,
        UsernamePasswordToken $token,
        UserInterface $user,
        DraftSource $draftSource
    ) {
        $this->prepareDraftSource($tokenStorage, $token, $user, $draftSource, $draftSourceFactory);

        $productRepository->find(42)->willReturn($fullOwnedProduct);
        $mergeDataOnProduct->merge($filteredOwnedProduct, $fullOwnedProduct)->willReturn($fullOwnedProduct);

        $productRepository->find(43)->willReturn($fullNotOwnedProduct);
        $mergeDataOnProduct->merge($filteredNotOwnedProduct, $fullNotOwnedProduct)->willReturn($fullNotOwnedProduct);

        $fullOwnedProduct->getId()->willReturn(42);
        $filteredOwnedProduct->getId()->willReturn(42);
        $authorizationChecker->isGranted(Attributes::OWN, $fullOwnedProduct)
            ->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT, $fullOwnedProduct)
            ->willReturn(true);

        $objectManager->persist($fullOwnedProduct)->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();

        $fullNotOwnedProduct->getId()->willReturn(43);
        $filteredNotOwnedProduct->getId()->willReturn(43);
        $authorizationChecker->isGranted(Attributes::OWN, $fullNotOwnedProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $fullNotOwnedProduct)
            ->willReturn(true);

        $filteredProductDraftBuilder->build($fullNotOwnedProduct, $draftSource)
            ->willReturn($productDraft)
            ->shouldBeCalled();

        $objectManager->persist($productDraft)->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, Argument::cetera())->shouldBeCalled();

        $objectManager->flush()->shouldBeCalledTimes(1);

        $this->saveAll([$filteredOwnedProduct, $filteredNotOwnedProduct]);
    }

    private function prepareDraftSource(
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        DraftSource $draftSource,
        PimUserDraftSourceFactory $draftSourceFactory
    ): void {
        $fullName = 'User full name';
        $username = 'username';
        $source = 'pim';
        $sourceLabel = 'PIM';

        $user->getFullName()->willReturn($fullName);
        $user->getUsername()->willReturn($username);

        $tokenStorage->getToken()->willReturn($token);

        $token->getUsername()->willReturn($username);
        $token->getUser()->willReturn($user);

        $draftSource->getSource()->willReturn($source);
        $draftSource->getSourceLabel()->willReturn($sourceLabel);
        $draftSource->getAuthor()->willReturn($username);
        $draftSource->getAuthorLabel()->willReturn($fullName);

        $draftSourceFactory->createFromUser($user)->willReturn($draftSource);
    }
}
