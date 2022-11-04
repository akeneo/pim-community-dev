<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\Common\Saver;

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
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DelegatingProductSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftBuilderInterface $productDraftBuilder,
        TokenStorageInterface $tokenStorage,
        EntityWithValuesDraftRepositoryInterface $productDraftRepo,
        RemoverInterface $productDraftRemover,
        NotGrantedDataMergerInterface $mergeDataOnProduct,
        ProductRepositoryInterface $productRepository,
        PimUserDraftSourceFactory $draftSourceFactory,
        SaverInterface $productSaver,
        BulkSaverInterface $bulkProductSaver,
        SaverInterface $productDraftSaver,
        TokenInterface $token
    ) {
        $tokenStorage->getToken()->willReturn($token);

        $this->beConstructedWith(
            $objectManager,
            $authorizationChecker,
            $productDraftBuilder,
            $tokenStorage,
            $productDraftRepo,
            $productDraftRemover,
            $mergeDataOnProduct,
            $productRepository,
            $draftSourceFactory,
            $productSaver,
            $bulkProductSaver,
            $productDraftSaver
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
        AuthorizationCheckerInterface $authorizationChecker,
        SaverInterface $productSaver,
        ProductInterface $filteredProduct
    ) {
        $filteredProduct->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d', '2020-01-01'));
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProduct)
            ->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProduct)
            ->willReturn(true);

        $productSaver->save($filteredProduct, [])->shouldBeCalled();

        $this->save($filteredProduct);
    }

    function it_does_not_save_neither_product_nor_draft_if_the_user_has_only_the_view_permission_on_product(
        AuthorizationCheckerInterface $authorizationChecker,
        SaverInterface $productSaver,
        SaverInterface $productDraftSaver,
        ProductInterface $filteredProduct
    ) {
        $filteredProduct->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d', '2020-01-01'));
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProduct)
            ->willReturn(false);

        $productSaver->save(Argument::any(), Argument::any())->shouldNotBeCalled();
        $productDraftSaver->save(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->save($filteredProduct);
    }

    function it_saves_the_product_when_user_is_not_the_owner_and_product_not_exists(
        SaverInterface $productSaver,
        ProductInterface $filteredProduct
    ) {
        $filteredProduct->getCreated()->willReturn(null);
        $productSaver->save($filteredProduct, [])->shouldBeCalled();

        $this->save($filteredProduct);
    }

    function it_removes_the_existing_product_draft_when_user_is_not_the_owner_and_product_exists_without_changes_anymore(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftBuilderInterface $productDraftBuilder,
        NotGrantedDataMergerInterface $mergeDataOnProduct,
        EntityWithValuesDraftRepositoryInterface $productDraftRepo,
        RemoverInterface $productDraftRemover,
        ProductRepositoryInterface $productRepository,
        PimUserDraftSourceFactory $draftSourceFactory,
        SaverInterface $productDraftSaver,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        EntityWithValuesDraftInterface $filteredProductDraft,
        TokenInterface $token,
        UserInterface $user,
        DraftSource $draftSource
    ) {
        $productUuid = Uuid::fromString('75cfd06e-9c03-44cb-93d3-b2e93d8f82b3');
        $this->prepareDraftSource($token, $user, $draftSource, $draftSourceFactory);

        $productRepository->find($productUuid)->willReturn($fullProduct);
        $mergeDataOnProduct->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);

        $filteredProduct->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d', '2020-01-01'));
        $filteredProduct->getUuid()->willReturn($productUuid);
        $filteredProduct->getIdentifier()->willReturn('sku');
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProduct)
            ->willReturn(true);

        $productDraftBuilder->build($filteredProduct, $draftSource)
            ->willReturn(null)
            ->shouldBeCalled();

        $productDraftRepo->findUserEntityWithValuesDraft($filteredProduct, 'username')->willReturn($filteredProductDraft);
        $productDraftRemover->remove($filteredProductDraft)->shouldBeCalled();

        $productDraftSaver->save(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->save($filteredProduct);
    }

    function it_removes_the_existing_product_draft_when_user_is_not_the_owner_and_product_exists_without_changes_anymore_even_with_edit_rights(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftBuilderInterface $productDraftBuilder,
        EntityWithValuesDraftRepositoryInterface $productDraftRepo,
        RemoverInterface $productDraftRemover,
        NotGrantedDataMergerInterface $mergeDataOnProduct,
        ProductRepositoryInterface $productRepository,
        PimUserDraftSourceFactory $draftSourceFactory,
        SaverInterface $productDraftSaver,
        TokenInterface $token,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        EntityWithValuesDraftInterface $filteredProductDraft,
        DraftSource $draftSource,
        UserInterface $user
    ) {
        $productUuid = Uuid::fromString('75cfd06e-9c03-44cb-93d3-b2e93d8f82b3');
        $this->prepareDraftSource($token, $user, $draftSource, $draftSourceFactory);

        $productRepository->find($productUuid)->willReturn($fullProduct);
        $mergeDataOnProduct->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);

        $filteredProduct->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d', '2020-01-01'));
        $filteredProduct->getUuid()->willReturn($productUuid);
        $filteredProduct->getIdentifier()->willReturn('sku');
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProduct)
            ->willReturn(true);

        $productDraftBuilder->build($filteredProduct, $draftSource)
            ->willReturn(null)
            ->shouldBeCalled();

        $productDraftRepo->findUserEntityWithValuesDraft($filteredProduct, 'username')->willReturn($filteredProductDraft);
        $productDraftRemover->remove($filteredProductDraft)->shouldBeCalled();

        $productDraftSaver->save(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->save($filteredProduct);
    }

    function it_does_not_remove_any_product_draft_when_user_is_not_the_owner_and_product_exists_without_changes_but_the_draft_does_not_exists(
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftBuilderInterface $productDraftBuilder,
        EntityWithValuesDraftRepositoryInterface $productDraftRepo,
        RemoverInterface $productDraftRemover,
        NotGrantedDataMergerInterface $mergeDataOnProduct,
        ProductRepositoryInterface $productRepository,
        PimUserDraftSourceFactory $draftSourceFactory,
        SaverInterface $productDraftSaver,
        TokenInterface $token,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        UserInterface $user,
        DraftSource $draftSource
    ) {
        $productUuid = Uuid::fromString('75cfd06e-9c03-44cb-93d3-b2e93d8f82b3');
        $this->prepareDraftSource($token, $user, $draftSource, $draftSourceFactory);

        $productRepository->find($productUuid)->willReturn($fullProduct);
        $mergeDataOnProduct->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);

        $filteredProduct->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d', '2020-01-01'));
        $filteredProduct->getUuid()->willReturn($productUuid);
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProduct)
            ->willReturn(true);

        $productDraftBuilder->build($filteredProduct, $draftSource)
            ->willReturn(null)
            ->shouldBeCalled();

        $productDraftRepo->findUserEntityWithValuesDraft($filteredProduct, 'username')->willReturn();
        $productDraftRemover->remove(Argument::any())->shouldNotBeCalled();

        $productDraftSaver->save(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->save($filteredProduct);
    }

    function it_saves_the_product_draft_when_user_is_not_the_owner_and_product_exists_with_changes(
        ObjectManager $objectManager,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftBuilderInterface $productDraftBuilder,
        NotGrantedDataMergerInterface $mergeDataOnProduct,
        ProductRepositoryInterface $productRepository,
        PimUserDraftSourceFactory $draftSourceFactory,
        SaverInterface $productDraftSaver,
        TokenInterface $token,
        ProductInterface $filteredProduct,
        ProductInterface $fullProduct,
        EntityWithValuesDraftInterface $filteredProductDraft,
        UserInterface $user,
        DraftSource $draftSource
    ) {
        $productUuid = Uuid::fromString('75cfd06e-9c03-44cb-93d3-b2e93d8f82b3');
        $this->prepareDraftSource($token, $user, $draftSource, $draftSourceFactory);

        $productRepository->find($productUuid)->willReturn($fullProduct);
        $mergeDataOnProduct->merge($filteredProduct, $fullProduct)->willReturn($filteredProduct);

        $filteredProduct->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d', '2020-01-01'));
        $filteredProduct->getUuid()->willReturn($productUuid);
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProduct)
            ->willReturn(true);

        $productDraftBuilder->build($filteredProduct, $draftSource)
            ->willReturn($filteredProductDraft)
            ->shouldBeCalled();

        $productDraftSaver->save($filteredProductDraft, [])->shouldBeCalled();
        $objectManager->refresh($filteredProduct)->shouldBeCalled();

        $this->save($filteredProduct);
    }

    function it_saves_several_product_and_product_drafts_depending_on_user_ownership(
        ObjectManager $objectManager,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityWithValuesDraftBuilderInterface $productDraftBuilder,
        NotGrantedDataMergerInterface $mergeDataOnProduct,
        ProductRepositoryInterface $productRepository,
        PimUserDraftSourceFactory $draftSourceFactory,
        BulkSaverInterface $bulkProductSaver,
        SaverInterface $productDraftSaver,
        TokenInterface $token,
        ProductInterface $filteredOwnedProduct,
        ProductInterface $filteredNotOwnedProduct,
        ProductInterface $fullNotOwnedProduct,
        EntityWithValuesDraftInterface $productDraft,
        UserInterface $user,
        DraftSource $draftSource
    ) {
        $this->prepareDraftSource($token, $user, $draftSource, $draftSourceFactory);

        $filteredOwnedProduct->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d', '2020-01-01'));
        $productUuid = Uuid::fromString('75cfd06e-9c03-44cb-93d3-b2e93d8f82b3');
        $filteredOwnedProduct->getUuid()->willReturn($productUuid);
        $productRepository->find($productUuid)->shouldNotBeCalled();
        $mergeDataOnProduct->merge($filteredOwnedProduct, Argument::any())->shouldNotBeCalled();

        $filteredNotOwnedProduct->getCreated()->willReturn(\DateTime::createFromFormat('Y-m-d', '2020-01-01'));
        $productUuid2 = Uuid::fromString('df31ba3f-508d-424c-8bc4-446c6e2966e5');
        $filteredNotOwnedProduct->getUuid()->willReturn($productUuid2);
        $productRepository->find($productUuid2)->willReturn($fullNotOwnedProduct);
        $mergeDataOnProduct->merge($filteredNotOwnedProduct, $fullNotOwnedProduct)->willReturn($fullNotOwnedProduct);

        $authorizationChecker->isGranted(Attributes::OWN, $filteredOwnedProduct)
            ->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredOwnedProduct)
            ->willReturn(true);

        $filteredNotOwnedProduct->getUuid()->willReturn($productUuid2);
        $authorizationChecker->isGranted(Attributes::OWN, $filteredNotOwnedProduct)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredNotOwnedProduct)
            ->willReturn(true);

        $productDraftBuilder->build($fullNotOwnedProduct, $draftSource)
            ->willReturn($productDraft)
            ->shouldBeCalled();

        $bulkProductSaver->saveAll([$filteredOwnedProduct], [])->shouldBeCalled();
        $productDraftSaver->save($productDraft, [])->shouldBeCalled();
        $objectManager->refresh($fullNotOwnedProduct)->shouldBeCalled();

        $this->saveAll([$filteredOwnedProduct, $filteredNotOwnedProduct]);
    }

    private function prepareDraftSource(
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
        $user->getUserIdentifier()->willReturn($username);

        $token->getUserIdentifier()->willReturn($username);
        $token->getUser()->willReturn($user);

        $draftSource->getSource()->willReturn($source);
        $draftSource->getSourceLabel()->willReturn($sourceLabel);
        $draftSource->getAuthor()->willReturn($username);
        $draftSource->getAuthorLabel()->willReturn($fullName);

        $draftSourceFactory->createFromUser($user)->willReturn($draftSource);
    }
}
