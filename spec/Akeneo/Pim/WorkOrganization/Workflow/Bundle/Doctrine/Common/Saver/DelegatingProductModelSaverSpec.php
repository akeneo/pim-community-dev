<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\Common\Saver;

use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
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
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage,
        EntityWithValuesDraftBuilderInterface $draftBuilder,
        RemoverInterface $productModelDraftRemover,
        NotGrantedDataMergerInterface $mergeDataOnProductModel,
        ProductModelRepositoryInterface $productModelRepository,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
        BulkSaverInterface $bulkProductModelSaver
    ) {
        $this->beConstructedWith(
            $objectManager,
            $productModelSaver,
            $productModelDraftSaver,
            $authorizationChecker,
            $tokenStorage,
            $draftBuilder,
            $productModelDraftRemover,
            $mergeDataOnProductModel,
            $productModelRepository,
            $productModelDraftRepository,
            $bulkProductModelSaver
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

    function it_bulk_saves_product_models(
        $bulkProductModelSaver,
        $authorizationChecker,
        $tokenStorage,
        $mergeDataOnProductModel,
        $productModelRepository,
        ProductModelInterface $filteredProductModel1,
        ProductModelInterface $filteredProductModel2,
        ProductModelInterface $fullProductModel1,
        ProductModelInterface $fullProductModel2
    ) {
        $tokenStorage->getToken()->willReturn('token');

        $productModelRepository->find(42)->willReturn($fullProductModel1);
        $mergeDataOnProductModel->merge($filteredProductModel1, $fullProductModel1)->willReturn($filteredProductModel1);
        $productModelRepository->findOneByIdentifier('code_1')->willReturn($fullProductModel1);
        $filteredProductModel1->getCode()->willReturn('code_1');
        $filteredProductModel1->getId()->willReturn(42);
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProductModel1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProductModel1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW, $filteredProductModel1)->willReturn(true);

        $productModelRepository->find(16)->willReturn($fullProductModel2);
        $mergeDataOnProductModel->merge($filteredProductModel2, $fullProductModel2)->willReturn($filteredProductModel2);
        $productModelRepository->findOneByIdentifier('code_2')->willReturn($fullProductModel2);
        $filteredProductModel2->getCode()->willReturn('code_2');
        $filteredProductModel2->getId()->willReturn(16);
        $authorizationChecker->isGranted(Attributes::OWN, $filteredProductModel2)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::EDIT, $filteredProductModel2)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW, $filteredProductModel2)->willReturn(true);

        $bulkProductModelSaver->saveAll([$filteredProductModel1, $filteredProductModel2], [])->shouldBeCalled();

        $this->saveAll([$filteredProductModel1, $filteredProductModel2], []);
    }
}
