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
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Workflow\Builder\ProductDraftBuilderInterface;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DelegatingProductSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        EventDispatcherInterface $eventDispatcher,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductDraftBuilderInterface $productDraftBuilder,
        TokenStorageInterface $tokenStorage,
        ProductDraftRepositoryInterface $productDraftRepo,
        RemoverInterface $productDraftRemover,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer
    ) {
        $this->beConstructedWith(
            $objectManager,
            $completenessManager,
            $eventDispatcher,
            $authorizationChecker,
            $productDraftBuilder,
            $tokenStorage,
            $productDraftRepo,
            $productDraftRemover,
            $uniqueDataSynchronizer
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
        ProductInterface $product
    ) {
        $product->getId()->willReturn(42);
        $authorizationChecker->isGranted(Attributes::OWN, $product)
            ->willReturn(true);
        $tokenStorage->getToken()->willReturn('token');

        $objectManager->persist($product)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $completenessManager->schedule($product)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($product)->shouldBeCalled();
        $uniqueDataSynchronizer->synchronize($product)->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($product);
    }

    function it_saves_the_product_when_user_is_not_the_owner_and_product_not_exists(
        $objectManager,
        $completenessManager,
        $eventDispatcher,
        ProductInterface $product
    ) {
        $product->getId()->willReturn(null);

        $objectManager->persist($product)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $completenessManager->schedule($product)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($product)->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($product);
    }

    function it_remove_the_existing_product_draft_when_user_is_not_the_owner_and_product_exists_without_changes_anymore(
        $objectManager,
        $authorizationChecker,
        $productDraftBuilder,
        $tokenStorage,
        $productDraftRepo,
        $productDraftRemover,
        ProductInterface $product,
        ProductDraftInterface $productDraft,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $product->getId()->willReturn(42);
        $authorizationChecker->isGranted(Attributes::OWN, $product)
            ->willReturn(false);

        $user->getUsername()->willReturn('username');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $productDraftBuilder->build($product, 'username')
            ->willReturn(null)
            ->shouldBeCalled();

        $productDraftRepo->findUserProductDraft($product, 'username')->willReturn($productDraft);
        $productDraftRemover->remove($productDraft)->shouldBeCalled();

        $objectManager->persist(Argument::any())->shouldNotBeCalled();
        $objectManager->flush()->shouldNotBeCalled();

        $this->save($product);
    }

    function it_does_not_remove_any_product_draft_when_user_is_not_the_owner_and_product_exists_without_changes_but_the_draft_does_not_exists(
        $objectManager,
        $authorizationChecker,
        $productDraftBuilder,
        $tokenStorage,
        $productDraftRepo,
        $productDraftRemover,
        ProductInterface $product,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $product->getId()->willReturn(42);
        $authorizationChecker->isGranted(Attributes::OWN, $product)
            ->willReturn(false);

        $user->getUsername()->willReturn('username');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $productDraftBuilder->build($product, 'username')
            ->willReturn(null)
            ->shouldBeCalled();

        $productDraftRepo->findUserProductDraft($product, 'username')->willReturn();
        $productDraftRemover->remove(Argument::any())->shouldNotBeCalled();

        $objectManager->persist(Argument::any())->shouldNotBeCalled();
        $objectManager->flush()->shouldNotBeCalled();

        $this->save($product);
    }

    function it_saves_the_product_draft_when_user_is_not_the_owner_and_product_exists_with_changes(
        $objectManager,
        $eventDispatcher,
        $authorizationChecker,
        $productDraftBuilder,
        $tokenStorage,
        ProductInterface $product,
        ProductDraftInterface $productDraft,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $product->getId()->willReturn(42);
        $authorizationChecker->isGranted(Attributes::OWN, $product)
            ->shouldBeCalled()
            ->willReturn(false);
        $user->getUsername()->willReturn('username');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $productDraftBuilder->build($product, 'username')
            ->willReturn($productDraft)
            ->shouldBeCalled();

        $objectManager->persist($productDraft)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $objectManager->refresh($product)->shouldBeCalled();

        $this->save($product);
    }

    function it_saves_the_product_when_there_is_no_token_generated(
        $objectManager,
        $completenessManager,
        $eventDispatcher,
        $tokenStorage,
        ProductInterface $product
    ) {
        $product->getId()->willReturn(42);
        $tokenStorage->getToken()->willReturn(null);

        $objectManager->persist($product)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $completenessManager->schedule($product)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($product)->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($product);
    }

    function it_saves_several_product_and_product_drafts_depending_on_user_ownership(
        $objectManager,
        $completenessManager,
        $eventDispatcher,
        $authorizationChecker,
        $productDraftBuilder,
        $tokenStorage,
        ProductInterface $ownedProduct,
        ProductInterface $notOwnedProduct,
        ProductDraftInterface $productDraft,
        UsernamePasswordToken $token,
        UserInterface $user
    ) {
        $ownedProduct->getId()->willReturn(42);
        $completenessManager->generateMissingForProduct($ownedProduct)->shouldBeCalled();
        $authorizationChecker->isGranted(Attributes::OWN, $ownedProduct)
            ->willReturn(true);
        $user->getUsername()->willReturn('username');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $objectManager->persist($ownedProduct)->shouldBeCalled();
        $completenessManager->schedule($ownedProduct)->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();

        $notOwnedProduct->getId()->willReturn(43);
        $authorizationChecker->isGranted(Attributes::OWN, $notOwnedProduct)
            ->shouldBeCalled()
            ->willReturn(false);
        $productDraftBuilder->build($notOwnedProduct, 'username')
            ->willReturn($productDraft)
            ->shouldBeCalled();
        $objectManager->persist($productDraft)->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, Argument::cetera())->shouldBeCalled();

        $objectManager->flush()->shouldBeCalledTimes(1);

        $this->saveAll([$ownedProduct, $notOwnedProduct]);
    }
}
