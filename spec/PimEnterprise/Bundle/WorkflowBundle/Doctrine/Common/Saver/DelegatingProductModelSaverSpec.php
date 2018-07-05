<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Doctrine\Common\Saver;

use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
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
        RemoverInterface $productModelDraftRemover,
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
            $productModelDraftRemover,
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
        $productModelSaver,
        $authorizationChecker,
        $tokenStorage,
        $mergeDataOnProductModel,
        $productModelRepository,
        ProductModelInterface $filteredProductModel,
        ProductModelInterface $fullProductModel
    ) {
        $productModelRepository->find(42)->willReturn($fullProductModel);
        $productModelRepository->findOneByIdentifier('code')->willReturn($fullProductModel);
        $mergeDataOnProductModel->merge($filteredProductModel, $fullProductModel)->willReturn($filteredProductModel);

        $filteredProductModel->getCode()->willReturn('code');
        $filteredProductModel->getId()->willReturn(42);
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProductModel)
            ->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProductModel)
            ->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW, $filteredProductModel)
            ->willReturn(true);
        $tokenStorage->getToken()->willReturn('token');

        $productModelSaver->save($filteredProductModel, [])->shouldBeCalled();

        $this->save($filteredProductModel);
    }

    function it_does_not_save_neither_product_model_nor_draft_if_the_user_has_only_the_view_permission_on_product_model(
        $authorizationChecker,
        $tokenStorage,
        $mergeDataOnProductModel,
        $productModelSaver,
        $productModelRepository,
        ProductModelInterface $filteredProductModel,
        ProductModelInterface $fullProductModel,
        TokenInterface $token
    ) {
        $productModelRepository->find(42)->willReturn($fullProductModel);
        $mergeDataOnProductModel->merge($filteredProductModel, $fullProductModel)->willReturn($filteredProductModel);
        $productModelRepository->findOneByIdentifier('code')->willReturn($fullProductModel);
        $productModelRepository->findOneByIdentifier('sku')->willReturn($fullProductModel);

        $tokenStorage->getToken()->willReturn($token);
        $filteredProductModel->getId()->willReturn(42);
        $filteredProductModel->getCode()->willReturn('code');
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProductModel)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProductModel)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::VIEW, $filteredProductModel)
            ->willReturn(true);

        $filteredProductModel->getCode()->willReturn('sku');

        $productModelSaver->save($filteredProductModel, [])->shouldNotBeCalled();

        $this->save($filteredProductModel);
    }

    function it_saves_the_product_model_when_user_is_not_the_owner_and_product_does_not_exist(
        $productModelSaver,
        $mergeDataOnProductModel,
        $authorizationChecker,
        ProductModelInterface $filteredProductModel
    ) {
        $mergeDataOnProductModel->merge($filteredProductModel)->willReturn($filteredProductModel);
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProductModel)
            ->willReturn(false);
        
        $filteredProductModel->getId()->willReturn(null);

        $productModelSaver->save($filteredProductModel, [])->shouldBeCalled();

        $this->save($filteredProductModel);
    }

    function it_removes_the_existing_product_model_draft_when_user_is_not_the_owner_and_product_model_exists_without_changes_anymore(
        $productModelSaver,
        $authorizationChecker,
        $tokenStorage,
        $mergeDataOnProductModel,
        $productModelRepository,
        $draftBuilder,
        $productModelDraftRepository,
        $productModelDraftRemover,
        ProductModelInterface $filteredProductModel,
        ProductModelInterface $fullProductModel,
        EntityWithValuesDraftInterface $filteredProductModelDraft,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $productModelRepository->find(42)->willReturn($fullProductModel);
        $mergeDataOnProductModel->merge($filteredProductModel, $fullProductModel)->willReturn($filteredProductModel);
        $productModelRepository->findOneByIdentifier('code')->willReturn($fullProductModel);
        $productModelRepository->findOneByIdentifier('sku')->willReturn($fullProductModel);

        $filteredProductModel->getId()->willReturn(42);
        $filteredProductModel->getCode()->willReturn('sku');
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProductModel)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProductModel)
            ->willReturn(true);

        $user->getUsername()->willReturn('username');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $draftBuilder->build($filteredProductModel, 'username')
            ->willReturn(null)
            ->shouldBeCalled();

        $productModelDraftRepository->findUserEntityWithValuesDraft($filteredProductModel, 'username')->willReturn($filteredProductModelDraft);
        $productModelDraftRemover->remove($filteredProductModelDraft)->shouldBeCalled();

        $productModelSaver->save(Argument::any(), [])->shouldNotBeCalled();

        $this->save($filteredProductModel);
    }

    function it_removes_the_existing_product_model_draft_when_user_is_not_the_owner_and_product_model_exists_without_changes_anymore_even_with_edit_rights(
        $productModelSaver,
        $authorizationChecker,
        $draftBuilder,
        $tokenStorage,
        $mergeDataOnProductModel,
        $productModelRepository,
        $productModelDraftRemover,
        $productModelDraftRepository,
        ProductModelInterface $filteredProductModel,
        ProductModelInterface $fullProductModel,
        EntityWithValuesDraftInterface $filteredProductModelDraft,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $productModelRepository->find(42)->willReturn($fullProductModel);
        $mergeDataOnProductModel->merge($filteredProductModel, $fullProductModel)->willReturn($filteredProductModel);
        
        $filteredProductModel->getId()->willReturn(42);
        $filteredProductModel->getCode()->willReturn('sku');
        $productModelRepository->findOneByIdentifier('sku')->willReturn($fullProductModel);
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProductModel)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProductModel)
            ->willReturn(true);

        $user->getUsername()->willReturn('username');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $draftBuilder->build($filteredProductModel, 'username')
            ->willReturn(null)
            ->shouldBeCalled();

        $productModelDraftRepository->findUserEntityWithValuesDraft($filteredProductModel, 'username')->willReturn($filteredProductModelDraft);
        $productModelDraftRemover->remove($filteredProductModelDraft)->shouldBeCalled();

        $productModelSaver->save(Argument::any(), [])->shouldNotBeCalled();

        $this->save($filteredProductModel);
    }

    function it_does_not_remove_any_product_model_draft_when_user_is_not_the_owner_and_product_model_exists_without_changes_but_the_draft_does_not_exists(
        $productModelSaver,
        $authorizationChecker,
        $draftBuilder,
        $tokenStorage,
        $mergeDataOnProductModel,
        $productModelDraftRepository,
        $productModelDraftRemover,
        $productModelRepository,
        ProductModelInterface $filteredProductModel,
        ProductModelInterface $fullProductModel,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $productModelRepository->find(42)->willReturn($fullProductModel);
        $mergeDataOnProductModel->merge($filteredProductModel, $fullProductModel)->willReturn($filteredProductModel);
        
        $filteredProductModel->getId()->willReturn(42);
        $filteredProductModel->getCode()->willReturn('sku');
        $productModelRepository->findOneByIdentifier('sku')->willReturn($fullProductModel);
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProductModel)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProductModel)
            ->willReturn(true);

        $user->getUsername()->willReturn('username');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $draftBuilder->build($filteredProductModel, 'username')
            ->willReturn(null)
            ->shouldBeCalled();

        $productModelDraftRepository->findUserEntityWithValuesDraft($filteredProductModel, 'username')->willReturn();
        $productModelDraftRemover->remove(Argument::any())->shouldNotBeCalled();

        $productModelSaver->save(Argument::any(), [])->shouldNotBeCalled();

        $this->save($filteredProductModel);
    }

    function it_saves_the_product_model_draft_when_user_is_not_the_owner_and_product_model_exists_with_changes(
        $productModelSaver,
        $authorizationChecker,
        $draftBuilder,
        $tokenStorage,
        $mergeDataOnProductModel,
        $productModelRepository,
        ProductModelInterface $filteredProductModel,
        ProductModelInterface $fullProductModel,
        EntityWithValuesDraftInterface $filteredProductDraft,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $productModelRepository->find(42)->willReturn($fullProductModel);
        $filteredProductModel->getCode()->willReturn('sku');
        $productModelRepository->findOneByIdentifier('sku')->willReturn($fullProductModel);
        $mergeDataOnProductModel->merge($filteredProductModel, $fullProductModel)->willReturn($filteredProductModel);
        
        $filteredProductModel->getId()->willReturn(42);
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProductModel)
            ->willReturn(false);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProductModel)
            ->willReturn(true);

        $user->getUsername()->willReturn('username');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $draftBuilder->build($filteredProductModel, 'username')
            ->willReturn($filteredProductDraft)
            ->shouldBeCalled();

        $productModelSaver->save(Argument::any(), [])->shouldNotBeCalled();

        $this->save($filteredProductModel);
    }
}
