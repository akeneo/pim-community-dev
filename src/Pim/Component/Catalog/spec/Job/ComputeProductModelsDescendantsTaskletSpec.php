<?php

namespace spec\Pim\Component\Catalog\Job;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;

class ComputeProductModelsDescendantsTaskletSpec extends ObjectBehavior
{
    function let(
        ProductModelRepositoryInterface $productModelRepository,
        SaverInterface $productModelDescendantsSaver
    ) {
        $this->beConstructedWith($productModelRepository, $productModelDescendantsSaver);
    }

    function it_saves_the_product_model_descendants_on_execute(
        $productModelRepository,
        $productModelDescendantsSaver,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('product_model_codes')->willReturn(['tshirt_root', 'another_model']);

        $productModelRepository->findByIdentifiers(['tshirt_root', 'another_model'])->willReturn([$productModel1, $productModel2]);
        $productModelDescendantsSaver->save($productModel1)->shouldBeCalled();
        $productModelDescendantsSaver->save($productModel2)->shouldBeCalled();

        $this->execute();
    }
}
