<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Connector\Writer\MassEdit;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Cache\CacheClearerInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Bundle\EnrichBundle\Connector\Writer\MassEdit\ProductAndProductModelWriter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProductAndProductModelWriterSpec extends ObjectBehavior
{
    function let(
        VersionManager $versionManager,
        BulkSaverInterface $productSaver,
        BulkSaverInterface $productModelSaver,
        CacheClearerInterface $cacheClearer,
        AuthorizationCheckerInterface $authorizationChecker,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $productSaver,
            $productModelSaver,
            $versionManager,
            $cacheClearer,
            $authorizationChecker
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
        ProductInterface $product1,
        ProductInterface $product2,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);

        $items = [$product1, $productModel1, $product2, $productModel2];

        $product1->getId()->willReturn('45');
        $product2->getId()->willReturn(null);
        $productModel1->getId()->willReturn('12');
        $productModel2->getId()->willReturn(null);

        $stepExecution->incrementSummaryInfo('proposal')->shouldBeCalledTimes(0);
        $stepExecution->incrementSummaryInfo('process')->shouldBeCalledTimes(4);

        $authorizationChecker->isGranted('OWN_RESOURCE', $product1)->willReturn(true);
        $authorizationChecker->isGranted('OWN_RESOURCE', $product2)->shouldNotBeCalled();
        $authorizationChecker->isGranted('OWN_RESOURCE', $productModel1)->willReturn(true);
        $authorizationChecker->isGranted('OWN_RESOURCE', $productModel2)->willReturn(true);

        $productSaver->saveAll([0 => $product1, 2 => $product2])->shouldBeCalled();
        $productModelSaver->saveAll([1 => $productModel1, 3 => $productModel2])->shouldBeCalled();

        $this->write($items);
    }

    function it_increments_summary_info_with_permission(
        $authorizationChecker,
        $stepExecution,
        TokenStorageInterface $tokenStorage,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductInterface $product4,
        ProductInterface $productModel1,
        ProductInterface $productModel2,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('realTimeVersioning')->willReturn(true);

        $product1->getId()->willReturn('45');
        $tokenStorage->getToken()->willReturn('token');
        $product2->getId()->willReturn(null);
        $product3->getId()->willReturn('42');
        $product4->getId()->willReturn(null);
        $productModel1->getId()->willReturn('1');
        $productModel2->getId()->willReturn(null);

        $authorizationChecker->isGranted('OWN_RESOURCE', Argument::any())->willReturn(false);

        $stepExecution->incrementSummaryInfo('process')->shouldBeCalledTimes(3);
        $stepExecution->incrementSummaryInfo('proposal')->shouldBeCalledTimes(3);

        $this->write([$product1, $product2, $productModel2, $product3, $product4, $productModel1]);
    }
}
