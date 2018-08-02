<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber\Bulk;

use Akeneo\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Classification\Model\CategoryInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class BulkComputeProductModelDescendantsSubscriberSpec extends ObjectBehavior
{
    function let(
        TokenStorage $tokenStorage,
        SimpleJobLauncher $jobLauncher,
        JobInstanceRepository $jobInstanceRepository
    ) {
        $this->beConstructedWith($tokenStorage, $jobLauncher, $jobInstanceRepository, 'compute_product_models_descendants');
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::POST_SAVE_ALL => 'bulkComputeProductModelDescendantsCompleteness',
        ]);
    }

    function it_computes_product_model_descendants_completeness_on_bulk_event(
        $tokenStorage,
        $jobLauncher,
        $jobInstanceRepository,
        ProductModelInterface $productModel,
        ProductModelInterface $productModel2,
        GenericEvent $event,
        TokenInterface $token,
        UserInterface $user,
        JobInstance $jobInstance
    ) {
        $event->getSubject()->willReturn([$productModel, $productModel2]);
        $productModel->getCode()->willReturn('product_model_code');
        $productModel2->getCode()->willReturn('product_model_code_2');

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $jobInstanceRepository->findOneByIdentifier('compute_product_models_descendants')
            ->willReturn($jobInstance);

        $jobLauncher->launch($jobInstance, $user, ['product_model_codes' => ['product_model_code', 'product_model_code_2']])
            ->shouldBeCalled();

        $this->bulkComputeProductModelDescendantsCompleteness($event);
    }

    function it_computes_product_model_descendants_only_on_product_models(
        $tokenStorage,
        $jobLauncher,
        $jobInstanceRepository,
        ProductModelInterface $productModel,
        CategoryInterface $category,
        GenericEvent $event,
        TokenInterface $token,
        UserInterface $user,
        JobInstance $jobInstance
    ) {
        $event->getSubject()->willReturn([$productModel, $category]);
        $productModel->getCode()->willReturn('product_model_code');
        $category->getCode()->willReturn('category');

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $jobInstanceRepository->findOneByIdentifier('compute_product_models_descendants')
            ->willReturn($jobInstance);

        $jobLauncher->launch($jobInstance, $user, ['product_model_codes' => ['product_model_code']])
            ->shouldBeCalled();

        $this->bulkComputeProductModelDescendantsCompleteness($event);
    }

    function it_does_not_launch_a_job_if_it_is_not_a_product_model(
        $jobLauncher,
        GenericEvent $event,
        \stdClass $wrongObject
    ) {
        $event->getSubject()->willReturn([$wrongObject]);

        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();

        $this->bulkComputeProductModelDescendantsCompleteness($event);
    }
}
