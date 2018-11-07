<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Bundle\MassEdit\Writer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Permission\Bundle\MassEdit\Writer\ProductAndProductModelWriter;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductAndProductModelWriterSpec extends ObjectBehavior
{
    function let(
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        TokenStorageInterface $tokenStorage,
        JobLauncherInterface $jobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $productSaver,
            $productModelSaver,
            $versionManager,
            $tokenStorage,
            $jobLauncher,
            $jobInstanceRepository,
            $authorizationChecker,
            'compute_product_model_descendant'
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAndProductModelWriter::class);
    }

    function it_is_an_item_writer()
    {
        $this->shouldHaveType(ItemWriterInterface::class);
    }

    function it_is_step_execution_aware()
    {
        $this->shouldHaveType(StepExecutionAwareInterface::class);
    }

    function it_saves_items(
        $productSaver,
        $productModelSaver,
        $stepExecution,
        $authorizationChecker,
        $tokenStorage,
        $jobLauncher,
        $jobInstanceRepository,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        JobParameters $jobParameters,
        TokenInterface $token,
        UserInterface $user,
        JobInstance $jobInstance
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);

        $items = [$product1, $productModel1, $product2, $productModel2];

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);
        $productModel1->getId()->willReturn('12');
        $productModel1->getCode()->willReturn('product_model_1');
        $productModel2->getId()->willReturn(null);
        $productModel2->getCode()->willReturn('product_model_2');

        $stepExecution->incrementSummaryInfo('proposal')->shouldBeCalledTimes(0);
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalledTimes(4);

        $authorizationChecker->isGranted('OWN_RESOURCE', $product1)->willReturn(true);
        $authorizationChecker->isGranted('OWN_RESOURCE', $product2)->shouldNotBeCalled();
        $authorizationChecker->isGranted('OWN_RESOURCE', $productModel1)->willReturn(true);
        $authorizationChecker->isGranted('OWN_RESOURCE', $productModel2)->willReturn(true);

        $productSaver->saveAll([0 => $product1, 2 => $product2])->shouldBeCalled();
        $productModelSaver->saveAll([1 => $productModel1, 3 => $productModel2])->shouldBeCalled();

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobInstanceRepository->findOneByIdentifier('compute_product_model_descendant')->willReturn($jobInstance);

        $productModel1->getCode()->willReturn('code1');
        $productModel2->getCode()->willReturn('code2');

        $jobLauncher->launch(
            $jobInstance,
            $user,
            [
                'product_model_codes' => [
                    1 => 'code1',
                    3 => 'code2',
                ],
            ]
        )->shouldBeCalled();

        $this->write($items);
    }

    function it_increments_summary_info_with_permission(
        $authorizationChecker,
        $stepExecution,
        $tokenStorage,
        $jobLauncher,
        $jobInstanceRepository,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductInterface $product4,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        JobParameters $jobParameters,
        TokenInterface $token,
        UserInterface $user,
        JobInstance $jobInstance
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);
        $product3->getId()->willReturn('42');
        $product4->getId()->willReturn(null);
        $productModel1->getId()->willReturn('1');
        $productModel1->getCode()->willReturn('product_model_1');
        $productModel2->getId()->willReturn(null);
        $productModel2->getCode()->willReturn('product_model_2');

        $authorizationChecker->isGranted('OWN_RESOURCE', Argument::any())->willReturn(false);

        $stepExecution->incrementSummaryInfo('process')->shouldBeCalledTimes(4);
        $stepExecution->incrementSummaryInfo('proposal')->shouldBeCalledTimes(2);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobInstanceRepository->findOneByIdentifier('compute_product_model_descendant')->willReturn($jobInstance);

        $jobLauncher->launch($jobInstance, $user, [
            'product_model_codes' => [2 => 'product_model_2', 5 => 'product_model_1'],
        ])->shouldBeCalled();

        $this->write([$product1, $product2, $productModel2, $product3, $product4, $productModel1]);
    }
}
