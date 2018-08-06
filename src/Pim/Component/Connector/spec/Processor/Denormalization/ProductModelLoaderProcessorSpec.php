<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Pim\Component\Connector\Processor\Denormalization\ProductModelLoaderProcessor;
use PhpSpec\ObjectBehavior;

class ProductModelLoaderProcessorSpec extends ObjectBehavior
{
    function let(StepExecution $stepExecution, ProductModelRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
        $this->setStepExecution($stepExecution);
        $repository->getIdentifierProperties()->willReturn(['code']);
    }
    
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelLoaderProcessor::class);
    }

    function it_load_a_product_model($repository, ProductModelInterface $productModel)
    {
        $repository->findOneByIdentifier('foobar')->willReturn($productModel);

        $this->process(['code' => 'foobar']);
    }
}
