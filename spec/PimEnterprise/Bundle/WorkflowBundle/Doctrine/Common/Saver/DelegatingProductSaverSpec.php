<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Builder\ProductDraftBuilderInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSavingOptionsResolver;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DelegatingProductSaverSpec extends ObjectBehavior
{
    function let(
        SaverInterface $workingCopySaver,
        SaverInterface $draftSaver,
        ObjectManager $objectManager,
        ProductSavingOptionsResolver $optionsResolver,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductDraftBuilderInterface $productDraftBuilder,
        TokenStorageInterface $tokenStorage,
        ProductDraftRepositoryInterface $productDraftRepo,
        RemoverInterface $productDraftRemover
    ) {
        $this->beConstructedWith(
            $workingCopySaver,
            $draftSaver,
            $objectManager,
            $optionsResolver,
            $authorizationChecker,
            $productDraftBuilder,
            $tokenStorage,
            $productDraftRepo,
            $productDraftRemover
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

    function it_delegates_to_working_copy_saver_when_user_is_the_owner(
        $optionsResolver,
        $authorizationChecker,
        $workingCopySaver,
        $tokenStorage,
        ProductInterface $product
    ) {
        $optionsResolver->resolveSaveOptions(['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->willReturn(['recalculate' => true, 'flush' => true, 'schedule' => true]);

        $product->getId()->willReturn(42);
        $authorizationChecker->isGranted(Attributes::OWN, $product)
            ->willReturn(true);
        $tokenStorage->getToken()->willReturn('token');

        $workingCopySaver->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->shouldBeCalled();

        $this->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true]);
    }

    function it_delegates_to_working_copy_saver_when_user_is_not_the_owner_and_product_not_exists(
        $optionsResolver,
        $workingCopySaver,
        ProductInterface $product
    ) {
        $optionsResolver->resolveSaveOptions(['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->willReturn(['recalculate' => true, 'flush' => true, 'schedule' => true]);

        $product->getId()->willReturn(null);

        $workingCopySaver->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->shouldBeCalled();

        $this->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true]);
    }

    function it_delegates_to_draft_saver_when_user_is_not_the_owner_and_product_exists_without_changes(
        $optionsResolver,
        $authorizationChecker,
        $productDraftBuilder,
        $draftSaver,
        $tokenStorage,
        $productDraftRepo,
        $productDraftRemover,
        ProductInterface $product,
        ProductDraftInterface $productDraft,
        UsernamePasswordToken $token,
        User $user
    ) {
        $optionsResolver->resolveSaveOptions(['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->willReturn(['recalculate' => true, 'flush' => true, 'schedule' => true]);

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

        $draftSaver->save()->shouldNotBeCalled();

        $this->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true]);
    }

    function it_does_not_remove_draft_when_user_is_not_the_owner_and_product_exists_without_changes_but_the_draft_does_not_exists(
        $optionsResolver,
        $authorizationChecker,
        $productDraftBuilder,
        $draftSaver,
        $tokenStorage,
        $productDraftRepo,
        $productDraftRemover,
        ProductInterface $product,
        UsernamePasswordToken $token,
        User $user
    ) {
        $optionsResolver->resolveSaveOptions(['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->willReturn(['recalculate' => true, 'flush' => true, 'schedule' => true]);

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

        $draftSaver->save()->shouldNotBeCalled();

        $this->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true]);
    }

    function it_delegates_to_draft_saver_when_user_is_not_the_owner_and_product_exists_with_changes(
        $optionsResolver,
        $authorizationChecker,
        $productDraftBuilder,
        $draftSaver,
        $tokenStorage,
        ProductInterface $product,
        ProductDraftInterface $productDraft,
        UsernamePasswordToken $token,
        User $user
    ) {
        $optionsResolver->resolveSaveOptions(['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->shouldBeCalled()
            ->willReturn(['recalculate' => true, 'flush' => true, 'schedule' => true]);

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

        $draftSaver->save($productDraft, ['recalculate' => true, 'flush' => true, 'schedule' => true])->shouldBeCalled();

        $this->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true]);
    }

    function it_delegates_to_working_copy_saver_when_there_is_no_token_generated(
        $optionsResolver,
        $workingCopySaver,
        $tokenStorage,
        ProductInterface $product
    ) {
        $optionsResolver->resolveSaveOptions(['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->willReturn(['recalculate' => true, 'flush' => true, 'schedule' => true]);

        $product->getId()->willReturn(42);
        $tokenStorage->getToken()->willReturn(null);

        $workingCopySaver->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->shouldBeCalled();

        $this->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true]);
    }
}
