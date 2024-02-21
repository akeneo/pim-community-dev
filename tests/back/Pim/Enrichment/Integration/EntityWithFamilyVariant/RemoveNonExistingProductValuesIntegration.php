<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\EntityWithFamilyVariant;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Jobs\JobExecutionObserver;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class RemoveNonExistingProductValuesIntegration extends TestCase
{
    private const JOB_NAME = 'remove_non_existing_product_values';

    /** @var JobExecutionObserver */
    private $jobExecutionObserver;

    /** @var JobLauncher */
    private $jobLauncher;

    public function test_it_removes_the_non_existing_values_from_product()
    {
        // Create option and add it to a product
        $attributeOption = new AttributeOption();
        $attributeOption->setCode('akeneo');
        $attributeOption->setAttribute($this->get('pim_api.repository.attribute')->findOneByIdentifier('brand'));
        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: 'hiking_shoes',
            userIntents: [
                new SetFamily('shoes'),
                new SetSimpleSelectValue('brand', null, null, 'akeneo')
            ]
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->get('doctrine.orm.default_entity_manager')->clear();
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $this->removeOption('brand', 'akeneo');
        $this->assertNotNull($this->getDataValueForProduct('hiking_shoes', 'brand'));

        $this->jobLauncher->launchConsumerUntilQueueIsEmpty();

        $this->get('doctrine.orm.default_entity_manager')->clear();

        $this->assertNull($this->getDataValueForProduct('hiking_shoes', 'brand'));
    }

    public function test_it_removes_the_non_existing_values_from_product_model()
    {
        $this->assertNotNull($this->getDataValueForProductModel('brogueshoe', 'collection'));

        $this->removeOption('collection', 'summer_2016');
        $this->jobLauncher->launchConsumerUntilQueueIsEmpty();

        $this->get('doctrine.orm.default_entity_manager')->clear();

        $this->assertNull($this->getDataValueForProductModel('brogueshoe', 'collection'));
    }

    private function removeOption($attributeCode, $attributeOptionCode)
    {
        $attributeOption = $this->get('pim_catalog.repository.attribute_option')->findOneByIdentifier(
            sprintf('%s.%s', $attributeCode, $attributeOptionCode)
        );
        $this->assertNotNull($attributeOption);
        $attributeOption->getAttribute()->removeOption($attributeOption);
        $this->get('pim_catalog.remover.attribute_option')->remove($attributeOption);
    }

    private function getDataValueForProduct(
        string $productIdentifier,
        string $attributeCode,
        string $localeCode = null,
        string $scopeCode = null
    ) {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($productIdentifier);
        $value = $product->getValue($attributeCode, $localeCode, $scopeCode);
        if (null === $value) {
            return null;
        }

        return $value->getData();
    }

    private function getDataValueForProductModel(
        string $productModelIdentifier,
        string $attributeCode,
        string $localeCode = null,
        string $scopeCode = null
    ) {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier(
            $productModelIdentifier
        );
        $value = $productModel->getValue($attributeCode, $localeCode, $scopeCode);
        if (null === $value) {
            return null;
        }

        return $value->getData();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeJobExecutions(static::JOB_NAME);
        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->jobExecutionObserver = $this->get(
            'akeneo_integration_tests.launcher.job_execution_observer'
        );
        $this->jobExecutionObserver->purge(static::JOB_NAME);
        $this->jobLauncher->flushJobQueue();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * Purges all the job executions for a job name.
     *
     * @param string $jobName
     */
    private function purgeJobExecutions(string $jobName): void
    {
        $jobInstance = $this->get('pim_enrich.repository.job_instance')
            ->findOneBy(['code' => $jobName]);

        $jobExecutions = $jobInstance->getJobExecutions();
        foreach ($jobExecutions as $jobExecution) {
            $jobInstance->removeJobExecution($jobExecution);
        }

        $this->get('akeneo_batch.saver.job_instance')->save($jobInstance);
    }
}
