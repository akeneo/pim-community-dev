<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Doctrine\Common\Saver;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSavingOptionsResolver;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ChangesCollector;
use PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ChangeSetComputerInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
        EventDispatcherInterface $dispatcher,
        ChangesCollector $collector,
        ChangeSetComputerInterface $changeSet,
        MetricFactory $metricFactory,
        MediaFactory $mediaFactory

    ) {
        $this->beConstructedWith(
            $objectManager,
            $optionsResolver,
            $securityContext,
            $factory,
            $repository,
            $dispatcher,
            $collector,
            $changeSet,
            AkeneoStorageUtilsExtension::DOCTRINE_ORM,
            $metricFactory,
            $mediaFactory
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
        $collector,
        $securityContext,
        TokenInterface $token,
        User $user,
        $factory,
        ProductDraft $draft,
        $objectManager,
        $dispatcher,
        ProductDraftEvent $event
    ) {
        $optionsResolver->resolveSaveOptions(['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->shouldBeCalled()
            ->willReturn(['recalculate' => true, 'flush' => true, 'schedule' => true]);

        $product->getValues()->willReturn([]);

        $collector->getData()->willReturn(['values' => ['name' => 'my proposed name']]);
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('julia');
        $factory->createProductDraft($product, 'julia')->willReturn($draft);
        $objectManager->persist($draft)->shouldBeCalled();

        $dispatcher->dispatch(ProductDraftEvents::PRE_UPDATE, Argument::any())->willReturn($event);
        $event->getChanges()->willReturn(['values' => ['name' => 'my proposed name']]);

        $draft->setChanges(['values' => ['name' => 'my proposed name']])->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->save($product, ['recalculate' => true, 'flush' => true, 'schedule' => true]);
    }

    function it_updates_and_saves_a_draft(
        ProductInterface $product,
        $optionsResolver,
        $collector,
        $securityContext,
        TokenInterface $token,
        User $user,
        $repository,
        ProductDraft $draft,
        $objectManager,
        $dispatcher,
        ProductDraftEvent $event
    ) {
        $optionsResolver->resolveSaveOptions(['recalculate' => true, 'flush' => true, 'schedule' => true])
            ->shouldBeCalled()
            ->willReturn(['recalculate' => true, 'flush' => true, 'schedule' => true]);

        $product->getValues()->willReturn([]);

        $collector->getData()->willReturn(['values' => ['name' => 'my proposed name']]);
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('julia');
        $repository->findUserProductDraft($product, 'julia')->willReturn($draft);

        $dispatcher->dispatch(ProductDraftEvents::PRE_UPDATE, Argument::any())->willReturn($event);
        $event->getChanges()->willReturn(['values' => ['name' => 'my proposed name']]);

        $draft->setChanges(['values' => ['name' => 'my proposed name']])->shouldBeCalled();
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