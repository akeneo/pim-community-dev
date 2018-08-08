<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;

class ComputeProductModelsDescendantsTaskletSpec extends ObjectBehavior
{
    function let(
        ProductModelRepositoryInterface $productModelRepository,
        SaverInterface $productModelDescendantsSaver,
        EntityManagerClearerInterface $cacheClearer,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($productModelRepository, $productModelDescendantsSaver, $cacheClearer);
        $this->setStepExecution($stepExecution);
    }

    function it_saves_the_product_model_descendants_on_execute(
        $productModelRepository,
        $productModelDescendantsSaver,
        $cacheClearer,
        $stepExecution,
        JobParameters $jobParameters,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('product_model_codes')->willReturn(['tshirt_root', 'another_model']);

        $productModelRepository->findOneByIdentifier('tshirt_root')->willReturn($productModel1);
        $productModelDescendantsSaver->save($productModel1)->shouldBeCalled();

        $productModelRepository->findOneByIdentifier('another_model')->willReturn($productModel2);
        $productModelDescendantsSaver->save($productModel2)->shouldBeCalled();

        $cacheClearer->clear()->shouldBeCalled();

        $this->execute();
    }
}
