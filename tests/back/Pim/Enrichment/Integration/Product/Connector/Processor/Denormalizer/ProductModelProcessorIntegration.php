<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Connector\Processor\Denormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductModelProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;

class ProductModelProcessorIntegration extends TestCase
{
    public function testWarningOnImportContainsProductModelCode(): void
    {
        $this->createProductModel([
            'code' => 'a_product_model_code',
            'family_variant' => 'familyVariantA2',
        ]);
        $stepExecution = $this->createStepExecution();

        /** @var ProductModelProcessor $processor */
        $processor = $this->get('pim_connector.processor.denormalization.root_product_model');
        $processor->setStepExecution($stepExecution);

        $standardProductModel = [
            'code' => 'a_product_model_code',
            'family_variant' => 'foo',
        ];

        $expectedWarningItem = [
            'code' => 'a_product_model_code',
            'family_variant' => 'foo',
        ];

        try {
            $processor->process($standardProductModel);
        } catch (InvalidItemException $exception) {
            $this->assertEquals($expectedWarningItem, $exception->getItem()->getInvalidData());

            return;
        }

        throw new \RuntimeException('An exception should have been thrown');
    }

    private function createStepExecution(): StepExecution
    {
        $jobParameters = new JobParameters([
            'enabledComparison' => true,
        ]);
        $jobExecution = new JobExecution();
        $jobExecution->setJobParameters($jobParameters);

        return new StepExecution('test', $jobExecution);
    }

    private function createProductModel(array $data): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
