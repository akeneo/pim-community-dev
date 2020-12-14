<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Connector\Processor\Denormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductModelProcessor;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;

class ProductModelProcessorIntegration extends TestCase
{
    public function testWarningOnImportContainsProductModelCode(): void
    {
        $jobParameters = new JobParameters([
            'enabledComparison' => false,
        ]);
        $jobExecution = new JobExecution();
        $jobExecution->setJobParameters($jobParameters);
        $stepExecution = new StepExecution('test', $jobExecution);
        /** @var ProductModelProcessor $processor */
        $processor = $this->get('pim_connector.processor.denormalization.root_product_model');
        $processor->setStepExecution($stepExecution);

        $standardProductModel = [
            'code' => 'a_product_model_code',
            'brand' => 'foo',
        ];

        try {
            $processor->process($standardProductModel);
        } catch (InvalidItemException $exception){
            $this->assertEquals($standardProductModel, $exception->getItem()->getInvalidData());

            return;
        }

        throw new \RuntimeException('An exception should have been thrown');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
