<?php

namespace spec\Pim\Bundle\EnrichBundle\Connector\Writer\MassEdit;

use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Connector\Writer\MassEdit\ProductAndProductModelWriter;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $versionManager,
            $productSaver,
            $productModelSaver,
            $tokenStorage,
            $jobLauncher,
            $jobInstanceRepository,
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
        $tokenStorage,
        $jobLauncher,
        $jobInstanceRepository,
        ProductInterface $product1,
        ProductModelInterface $productModel1,
        ProductInterface $product2,
        JobParameters $jobParameters,
        TokenInterface $token,
        UserInterface $user,
        JobInstance $jobInstance
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);

        $items = [$product1, $productModel1, $product2];
        $productModel1->getId()->willReturn(1);
        $productModel1->getCode()->willReturn('product_model');

        $products = $items;
        unset($products[1]);
        $productModels = $items;
        unset($productModels[0]);
        unset($productModels[2]);

        $productSaver->saveAll($products)->shouldBeCalled();
        $productModelSaver->saveAll($productModels)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalled();

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobInstanceRepository->findOneByIdentifier('compute_product_model_descendant')->willReturn($jobInstance);

        $jobLauncher->launch($jobInstance, $user, ['product_model_codes' => [1 => 'product_model']])->shouldBeCalled();

        $this->write($items);
    }

    function it_increments_summary_info(
        $stepExecution,
        $productSaver,
        $productModelSaver,
        $tokenStorage,
        $jobLauncher,
        $jobInstanceRepository,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductModelInterface $productModel1,
        JobParameters $jobParameters,
        TokenInterface $token,
        UserInterface $user,
        JobInstance $jobInstance
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);

        $items = [$product1, $productModel1, $product2];

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);
        $productModel1->getId()->willReturn('89');
        $productModel1->getCode()->willReturn('product_model');

        $productSaver->saveAll(Argument::any())->shouldBeCalled();
        $productModelSaver->saveAll(Argument::any())->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('create')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalledTimes(2);

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobInstanceRepository->findOneByIdentifier('compute_product_model_descendant')->willReturn($jobInstance);

        $jobLauncher->launch($jobInstance, $user, ['product_model_codes' => [1 => 'product_model']])->shouldBeCalled();

        $this->write($items);
    }
}
