<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Import;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Model\Warning;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractImportProductTestCase extends TestCase
{
    final protected const UUID_SKU1 = 'a7bcb820-e93f-4ecc-9c4d-69549278bd3a';
    final protected const UUID_SKU2 = '105721c9-773b-441c-9a15-f2363d5187be';
    final protected const UUID_SKU3 = '3d600e51-9fc8-4869-822f-33bf2c82db8f';
    final protected const UUID_EMPTY_IDENTIFIER = 'b988bcfd-bb4d-4ddb-a2a2-d1cf926ab88c';

    private readonly int $adminUserId;
    private readonly JobLauncher $jobLauncher;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUserId = (int)$this->get('database_connection')->fetchOne(
            'SELECT id FROM oro_user WHERE username = :username',
            ['username' => 'admin']
        );
        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->jobLauncher->flushJobQueue();
        $this->get('akeneo_integration_tests.helper.authenticator')->login('admin');
        $this->loadFixtures();
    }

    /**
     * @param UserIntent[] $userIntents
     */
    protected function createProductWithUuid(UuidInterface $uuid, array $userIntents): ProductInterface
    {
        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithUuid(
                $this->adminUserId,
                ProductUuid::fromUuid($uuid),
                $userIntents
            )
        );

        return $this->get('pim_catalog.repository.product')->findOneByUuid($uuid);
    }

    final protected function getProductRepository(): ProductRepositoryInterface
    {
        return $this->get('pim_catalog.repository.product');
    }

    final protected function lauchImport($content): void
    {
        $this->jobLauncher->launchImport('csv_product_import', $content);
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    final protected function assertImportedProduct(int $created, int $updated, int $skipeed, array $expectedWarnings = []): void
    {
        /** @var JobExecution $jobExecution */
        $jobExecution = $this->get('akeneo_batch.job.job_instance_repository')->findOneByIdentifier('csv_product_import')
                    ->getJobExecutions()->last();
        /** @var StepExecution $importStepExecution */
        $importStepExecution = $jobExecution->getStepExecutions()->filter(
            static fn (StepExecution $stepExecution): bool => 'import' === $stepExecution->getStepName()
        )->first();

        Assert::assertSame($created, $importStepExecution->getSummaryInfo('create', 0));
        Assert::assertSame($updated, $importStepExecution->getSummaryInfo('update', 0));
        Assert::assertSame($skipeed, $importStepExecution->getSummaryInfo('skip', 0));
        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');
        $warnings = $importStepExecution->getWarnings()->map(
            static fn (Warning $warning): string => $translator->trans($warning->getReason(), $warning->getReasonParameters())
        )->getValues();
        Assert::assertSame($expectedWarnings, $warnings);
    }

    private function loadFixtures(): void
    {
        $this->createProductWithUuid(
            Uuid::fromString(self::UUID_SKU1),
            [
                new SetIdentifierValue('sku', 'sku1')
            ]
        );
        $this->createProductWithUuid(
            Uuid::fromString(self::UUID_SKU2),
            [
                new SetIdentifierValue('sku', 'sku2')
            ]
        );
        $this->createProductWithUuid(
            Uuid::fromString(self::UUID_SKU3),
            [
                new SetIdentifierValue('sku', 'sku3'),
                new AssociateProducts('X_SELL', ['sku1', 'sku2'])
            ]
        );
        $this->createProductWithUuid(
            Uuid::fromString(self::UUID_EMPTY_IDENTIFIER),
            []
        );
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
    }
}
