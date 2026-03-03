<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Category\Integration;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Job\DuplicatedJobException;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderRegistry;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PHPUnit\Framework\Assert;

final class ConfigureCategoryTreeForExportJobsAfterChangingTheChannelCategoryTreeIntegration extends TestCase
{
    /** @test */
    public function exportJobsAreUpdatedAfterChangingTheChannelCategoryTree(): void
    {
        $this->registerJobName('csv_published_product_export');
        $this->registerJobName('xlsx_published_product_export');
        $this->registerJobName('akeneo_shared_catalog');

        $this->givenTheCustomChannelAttachedToMasterCategory();
        $jobIds = [
            $this->givenAnExportJobUsingChannel('custom', 'csv_product_export'),
            $this->givenAnExportJobUsingChannel('custom', 'xlsx_product_export'),
            $this->givenAnExportJobUsingChannel('custom', 'csv_product_model_export'),
            $this->givenAnExportJobUsingChannel('custom', 'xlsx_product_model_export'),
            $this->givenAnExportJobUsingChannel('custom', 'csv_published_product_export'),
            $this->givenAnExportJobUsingChannel('custom', 'xlsx_published_product_export'),
            $this->givenAnExportJobUsingChannel('custom', 'akeneo_shared_catalog'),
        ];

        foreach ($jobIds as $jobId) {
            $this->assertExportJobHasAFilterWithCateogry($jobId, 'master');
        }

        $otherCategory = $this->givenTheOtherCategory();
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('custom');
        $channel->setCategory($otherCategory);
        $errors = $this->get('validator')->validate($channel);
        Assert::assertCount(0, $errors);
        $this->get('pim_catalog.saver.channel')->save($channel);
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        foreach ($jobIds as $jobId) {
            $this->assertExportJobHasAFilterWithCateogry($jobId, 'other');
        }
    }

    private function givenTheCustomChannelAttachedToMasterCategory(): void
    {
        $channel = $this->get('pim_catalog.factory.channel')->create();
        $this->get('pim_catalog.updater.channel')->update(
            $channel,
            [
                'code'          => 'custom',
                'category_tree' => 'master',
                'currencies'    => ['USD'],
                'locales'       => ['fr_FR']
            ]
        );

        $errors = $this->get('validator')->validate($channel);
        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.channel')->save($channel);
    }

    private function givenTheOtherCategory(): Category
    {
        $category = $this->get('pim_catalog.factory.category')->create();
        $this->get('pim_catalog.updater.category')->update($category, ['code' => 'other']);
        $errors = $this->get('validator')->validate($category);
        Assert::assertCount(0, $errors);
        $this->get('pim_catalog.saver.category')->save($category);

        return $category;
    }

    private function givenAnExportJobUsingChannel(string $channelCode, string $jobName): int
    {
        $job = $this->get('pim_connector.factory.job_instance')->create()
            ->setCode(sprintf('an_export_for_%s', $jobName))
            ->setConnector('Akeneo CSV Connector')
            ->setType('export')
            ->setJobName($jobName)
            ->setRawParameters([
                'storage' => [
                    'type' => 'local',
                    'file_path' => '/tmp/export.csv',
                ],
                'delimiter' => ';',
                'withHeader' => true,
                'filters' => [
                    'structure' => ['scope' => $channelCode],
                    'data' => [
                        [
                            'field' => 'categories',
                            'operator' => Operators::IN_CHILDREN_LIST,
                            'value' => ['master'],
                        ],
                    ],
                ]
            ]);

        $this->get('akeneo_batch.saver.job_instance')->save($job);

        return $job->getId();
    }

    private function assertExportJobHasAFilterWithCateogry(int $jobId, string $categoryCode): void
    {
        /** @var JobInstance $jobInstance */
        $jobInstance = $this->get('pim_enrich.repository.job_instance')->findOneById($jobId);
        Assert::assertInstanceOf(JobInstance::class, $jobInstance);

        $parameters = $jobInstance->getRawParameters();
        Assert::assertSame([$categoryCode], $parameters['filters']['data'][0]['value']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function registerJobName(string $jobName): void
    {
        /** @var JobRegistry $jobRegistry */
        $jobRegistry = $this->get('akeneo_batch.job.job_registry');
        $jobRepository = $this->get('akeneo_batch.job_repository');
        try {
            $jobRegistry->register(new class($jobName, $jobRepository) implements JobInterface {
                public function __construct(private string $jobName, private JobRepositoryInterface $jobRepository)
                {
                }

                public function getName(): string
                {
                    return $this->jobName;
                }

                public function execute(JobExecution $execution): void
                {
                }

                public function getJobRepository(): JobRepositoryInterface
                {
                    return $this->jobRepository;
                }

            }, 'export', 'Akeneo CSV Connector');
        } catch (DuplicatedJobException $e) {
        }

        /** @var DefaultValuesProviderRegistry $defaultvaluesRegistry */
        $defaultvaluesRegistry = $this->get('akeneo_batch.job.job_parameters.default_values_provider_registry');
        $defaultvaluesRegistry->register(new class($jobName) implements DefaultValuesProviderInterface {
            private string $jobName;

            public function __construct(string $jobName)
            {
                $this->jobName = $jobName;
            }

            public function getDefaultValues()
            {
                return [];
            }

            public function supports(JobInterface $job)
            {
                return $job->getName() === $this->jobName;
            }

        });
    }
}
