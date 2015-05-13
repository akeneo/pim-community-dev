<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Doctrine\Common\Saver;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSavingOptionsResolver;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Builder\DraftBuilder;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ProductDraftSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        ProductSavingOptionsResolver $optionsResolver,
        SecurityContextInterface $securityContext,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $repository,
        DraftBuilder $draftBuilder
    ) {
        $this->beConstructedWith(
            $objectManager,
            $optionsResolver,
            $securityContext,
            $factory,
            $repository,
            $draftBuilder
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

    function it_creates_and_saves_a_draft(
        ProductInterface $product,
        $optionsResolver,
        $securityContext,
        TokenInterface $token,
        User $user,
        $factory,
        ProductDraft $draft,
        $objectManager,
        $draftBuilder
    ) {
        $optionsResolver->resolveSaveOptions(['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->willReturn(['recalculate' => true, 'flush' => true, 'schedule' => true]);

        $draftBuilder->builder($product)->willReturn(['values' => ['name' => 'my proposed name']]);

        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('julia');
        $factory->createProductDraft($product, 'julia')->willReturn($draft);

        $draft->setChanges(['values' => ['name' => 'my proposed name']])->shouldBeCalled();
        $objectManager->persist($draft)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true]);
    }

    function it_updates_and_saves_a_draft(
        ProductInterface $product,
        $optionsResolver,
        $securityContext,
        TokenInterface $token,
        User $user,
        $repository,
        ProductDraft $draft,
        $objectManager,
        $draftBuilder
    ) {
        $optionsResolver->resolveSaveOptions(['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->willReturn(['recalculate' => true, 'flush' => true, 'schedule' => true]);

        $draftBuilder->builder($product)->willReturn(['values' => ['name' => 'my proposed name']]);

        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('julia');
        $repository->findUserProductDraft($product, 'julia')->willReturn($draft);

        $draft->setChanges(['values' => ['name' => 'my proposed name']])->shouldBeCalled();
        $objectManager->persist($draft)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true]);
    }

    function it_throws_an_exception_when_try_to_save_something_else_than_a_product(
        $objectManager
    ) {
        $otherObject = new \stdClass();
        $objectManager->persist(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(new \InvalidArgumentException('Expects a Pim\Bundle\CatalogBundle\Model\ProductInterface, "stdClass" provided'))
            ->duringSave($otherObject, ['recalculate' => false, 'flush' => false, 'schedule' => true]);
    }
}
