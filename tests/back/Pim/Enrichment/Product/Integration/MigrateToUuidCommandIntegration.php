<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Enrichment\Product\Integration;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidAddTriggers;
use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidStep;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use AkeneoTest\Pim\Enrichment\Integration\Product\UuidMigration\AbstractMigrateToUuidTestCase;
use PHPUnit\Framework\Assert;

final class MigrateToUuidCommandIntegration extends AbstractMigrateToUuidTestCase
{
    /** @test */
    public function it_migrates_the_database_to_use_uuid(): void
    {
        $this->connection = $this->get('database_connection');
        $this->clean();
        $this->loadFixtures();

        $this->launchMigrationCommand();

        $this->assertTriggersExistAndWork();
    }

    private function loadFixtures(): void
    {
        $adminUser = $this->createAdminUser();
        foreach (range(1, 10) as $i) {
            $this->get('pim_enrich.product.message_bus')->dispatch(new UpsertProductCommand(
                userId: $adminUser->getId(),
                productIdentifier: 'identifier' . $i
            ));
        }

        $this->createAttribute(['code' => 'a_text', 'type' => 'pim_catalog_text']);
        $this->createFamily(['code' => 'familyA', 'attributes' => ['sku']]);
    }

    private function assertTriggersExistAndWork(): void
    {
        foreach (\array_keys(MigrateToUuidStep::TABLES) as $tableName) {
            if ($tableName === 'pim_catalog_product') {
                continue;
            }

            $insertTriggerName = MigrateToUuidAddTriggers::getInsertTriggerName($tableName);
            Assert::assertTrue($this->triggerExists($insertTriggerName), \sprintf('The %s trigger does not exist', $insertTriggerName));
            $updateTriggerName = MigrateToUuidAddTriggers::getUpdateTriggerName($tableName);
            Assert::assertTrue($this->triggerExists($updateTriggerName), \sprintf('The %s trigger does not exist', $updateTriggerName));
        }

        $identifier1Product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('identifier1');
        $productUuid = $this->getProductUuid('identifier1');

        // pimee_workflow_product_draft
        $this->createProductDraft('admin', $identifier1Product, EntityWithValuesDraftInterface::IN_PROGRESS);
        Assert::assertSame(
            $productUuid,
            $this->connection->executeQuery('SELECT BIN_TO_UUID(product_uuid) FROM pimee_workflow_product_draft WHERE product_id = ?', [$identifier1Product->getId()])->fetchOne()
        );

        // pimee_workflow_published_product
        $this->get('feature_flags')->enable('published_product');
        $this->get('pimee_workflow.manager.published_product')->publish($identifier1Product);
        Assert::assertSame(
            $productUuid,
            $this->connection->executeQuery('SELECT BIN_TO_UUID(original_product_uuid) FROM pimee_workflow_published_product WHERE original_product_id = ?', [$identifier1Product->getId()])->fetchOne()
        );

        // pimee_teamwork_assistant_project_product and pimee_teamwork_assistant_completeness_per_attribute_group
        $productWithFamily = $this->get('pim_catalog.builder.product')->createProduct('product_with_family');
        $this->get('pim_catalog.updater.product')->update($productWithFamily, [
            'family' => 'familyA',
            'identifier' => 'product_with_family',
        ]);
        $violations = $this->get('pim_catalog.validator.product')->validate($productWithFamily);
        Assert::assertCount(0, $violations, sprintf('validation failed: %s', $violations));
        $this->get('pim_catalog.saver.product')->save($productWithFamily);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->createProject('Test project', 'admin', 'en_US', 'ecommerce', [
            [
                'field'    => 'sku',
                'operator' => 'IN',
                'value'    => ['product_with_family'],
            ],
        ]);
        Assert::assertSame(
            $this->getProductUuid($productWithFamily->getIdentifier()),
            $this->connection->executeQuery('SELECT BIN_TO_UUID(product_uuid) FROM pimee_teamwork_assistant_project_product WHERE product_id = ?', [$productWithFamily->getId()])->fetchOne()
        );
        Assert::assertSame(
            $this->getProductUuid($productWithFamily->getIdentifier()),
            $this->connection->executeQuery('SELECT BIN_TO_UUID(product_uuid) FROM pimee_teamwork_assistant_completeness_per_attribute_group WHERE product_id = ?', [$productWithFamily->getId()])->fetchOne()
        );
    }

    private function createProductDraft(string $userName, ProductInterface $product, int $draftStatus) : EntityWithValuesDraftInterface
    {
        $this->get('pim_catalog.updater.product')->update($product, ['values' => [
            'a_text' => [['data' => 'an edited text', 'locale' => null, 'scope' => null]],
        ]]);

        $user = $this->get('pim_user.provider.user')->loadUserByUsername($userName);
        $productDraft = $this->get('pimee_workflow.product.builder.draft')->build(
            $product,
            $this->get(PimUserDraftSourceFactory::class)->createFromUser($user)
        );

        if (EntityWithValuesDraftInterface::READY === $draftStatus) {
            $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
            $productDraft->markAsReady();
        }
        $this->get('pimee_workflow.saver.product_draft')->save($productDraft);

        return $productDraft;
    }

    protected function createProject(string $label, string $owner, string $locale, string $channel, array $filters): void
    {
        $dueDate = (new \DateTime())->modify('+1 month');
        $projectData = array_merge([
            'label'           => $label,
            'locale'          => $locale,
            'owner'           => $owner,
            'channel'         => $channel,
            'product_filters' => $filters,
            'description'     => 'An awesome description',
            'due_date'        => $dueDate->format('Y-m-d'),
            'datagrid_view'   => ['filters' => '', 'columns' => 'sku,label,family'],
        ]);

        if (isset($projectData['product_filters'])) {
            foreach ($projectData['product_filters'] as $key => $filter) {
                $projectData['product_filters'][$key] = array_merge($filter, [
                    'context'  => ['locale' => $projectData['locale'], 'scope' => $projectData['channel']],
                ]);
            }
        }

        $project = $this->get('pimee_teamwork_assistant.factory.project')->create($projectData);
        $violations = $this->get('validator')->validate($project);
        Assert::assertCount(0, $violations, (string) $violations);
        $this->get('pimee_teamwork_assistant.saver.project')->save($project);

        $this->calculateProject($project);
    }

    /**
     * Run the project calculation
     */
    protected function calculateProject(ProjectInterface $project): void
    {
        $numberOfExecutedJob = $this->findJobExecutionCount();
        $this->get('pimee_teamwork_assistant.launcher.job.project_calculation')->launch($project);
        $this->get('akeneo_integration_tests.launcher.job_launcher')->launchConsumerOnce();

        $this->isCompleteJobExecution($numberOfExecutedJob);
    }

    /**
     * Check if the project calculation is complete before the timeout.
     */
    private function isCompleteJobExecution(int $numberOfExecutedJob): void
    {
        $countOfJobExecution = $timeout = 0;
        while ($numberOfExecutedJob >= $countOfJobExecution) {
            $countOfJobExecution = $this->findJobExecutionCount();

            if (50 === $timeout) {
                throw new \RuntimeException('The job does not finished before timeout');
            }

            $timeout++;
            sleep(1);
        }
    }

    /**
     * Find the number of execution for a project calculation job.
     */
    private function findJobExecutionCount(): int
    {
        $sql = <<<SQL
        SELECT count(`execution`.`id`)
        FROM `akeneo_batch_job_execution` AS `execution`
            LEFT JOIN `akeneo_batch_job_instance` AS `instance` ON `execution`.`job_instance_id` = `instance`.`id`
        WHERE `instance`.`code` = :project_calculation
            AND `execution`.`exit_code` = 'COMPLETED'
        SQL;

        return (int) $this->connection->executeQuery($sql, [
            'project_calculation' => $this->getParameter('pimee_teamwork_assistant.project_calculation.job_name'),
        ])->fetchOne();
    }
}
