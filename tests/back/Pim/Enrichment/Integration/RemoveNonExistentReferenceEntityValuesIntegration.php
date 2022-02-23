<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Enrichment\Integration;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\ReferenceEntity\Application\Record\DeleteRecords\DeleteRecordsCommand;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Pim\TableAttribute\Helper\EntityBuilderTrait;
use PHPUnit\Framework\Assert;

class RemoveNonExistentReferenceEntityValuesIntegration extends TestCase
{
    use EntityBuilderTrait;

    private const REMOVE_NON_EXISTENT_VALUES_JOB = 'remove_non_existing_product_values';

    /** @test */
    public function it_removes_deleted_reference_entity_record_from_product_values(): void
    {
        $this->assertReferenceEntityValues('sunglasses', 'black');
        $this->assertCompleteness('sunglasses', 100);

        $this->deleteRecord('color', 'black');

        $jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        while ($jobLauncher->hasJobInQueue()) {
            $process = $jobLauncher->launchConsumerOnceInBackground();
            $process->wait();
        }

        $this->assertReferenceEntityValues('sunglasses', '');
        $this->assertCompleteness('sunglasses', 33);
    }

    /** @test */
    public function it_removes_records_from_product_values_after_all_records_are_deleted(): void
    {
        $this->assertReferenceEntityValues('sunglasses', 'black');
        $this->assertCompleteness('sunglasses', 100);

        ($this->get('akeneo_referenceentity.application.record.delete_records_handler'))(
            new DeleteRecordsCommand('color', ['white', 'black'])
        );

        $jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        while ($jobLauncher->hasJobInQueue()) {
            $process = $jobLauncher->launchConsumerOnceInBackground();
            $process->wait();
        }

        $this->assertReferenceEntityValues('sunglasses', '');
        $this->assertCompleteness('sunglasses', 33);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures();

        $jobInstance = $this->get('pim_enrich.repository.job_instance')
            ->findOneBy(['code' => self::REMOVE_NON_EXISTENT_VALUES_JOB]);
        $jobExecutions = $jobInstance->getJobExecutions();
        foreach ($jobExecutions as $jobExecution) {
            $jobInstance->removeJobExecution($jobExecution);
        }
        $this->get('akeneo_batch.saver.job_instance')->save($jobInstance);
        $this->get('akeneo_integration_tests.launcher.job_execution_observer')->purge(
            self::REMOVE_NON_EXISTENT_VALUES_JOB
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function loadFixtures(): void
    {
        $this->createReferenceEntity('color');
        $this->createRecord('color', 'black', ['en_US' => 'Black']);
        $this->createRecord('color', 'white', ['en_US' => 'White']);

        $this->createAttribute(
            [
                'code' => 'fabric_color',
                'type' => AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT,
                'group' => 'other',
                'localizable' => false,
                'scopable' => false,
                'labels' => ['en_US' => 'Fabric color'],
                'reference_data_name' => 'color'
            ]
        );
        $this->createAttribute(
            [
                'code' => 'name',
                'type' => AttributeTypes::TEXT,
                'group' => 'other',
                'localizable' => false,
                'scopable' => false,
                'labels' => ['en_US' => 'Name'],
            ]
        );
        $this->createAttribute(
            [
                'code' => 'palette',
                'type' => AttributeTypes::REFERENCE_ENTITY_COLLECTION,
                'group' => 'other',
                'localizable' => false,
                'scopable' => false,
                'labels' => ['en_US' => 'Palette'],
                'reference_data_name' => 'color',
            ]
        );

        $this->createFamily([
            'code' => 'marketing',
            'attributes' => ['fabric_color', 'palette'],
            'attribute_requirements' => [
                'ecommerce' => ['fabric_color', 'palette'],
            ]
        ]);

        $this->createProduct('sunglasses', [
            'categories' => ['master'],
            'family' => 'marketing',
            'values' => [
                'name' => [[
                    "locale" => null,
                    "scope" => null,
                    "data" => 'Sunglasses'
                ]],
                'fabric_color' => [[
                    "locale" => null,
                    "scope" => null,
                    "data" => 'black'
                ]],
                'palette' => [[
                    "locale" => null,
                    "scope" => null,
                    "data" => ['black']
                ]]
            ],
        ]);
    }

    private function assertReferenceEntityValues(string $identifier, string $value): void
    {
        $res = $this->get('database_connection')->executeQuery(
            'SELECT raw_values FROM pim_catalog_product WHERE identifier = :identifier',
            ['identifier' => $identifier]
        )->fetch();
        Assert::assertNotFalse($res);

        $rawValues = \json_decode($res['raw_values'], true);

        Assert::assertEqualsCanonicalizing(
            $value,
            $rawValues['fabric_color']['<all_channels>']['<all_locales>'] ?? ''
        );
    }

    private function assertCompleteness(string $identifier, int $expectedRatio): void
    {
        $res = $this->get('database_connection')->executeQuery(<<<SQL
SELECT completeness.missing_count, completeness.required_count FROM pim_catalog_completeness completeness
INNER JOIN pim_catalog_product product ON product.id = completeness.product_id
INNER JOIN pim_catalog_locale locale ON completeness.locale_id = locale.id
INNER JOIN pim_catalog_channel channel ON completeness.channel_id = channel.id
WHERE product.identifier = :identifier
AND channel.code = 'ecommerce'
AND locale.code = 'en_US'
SQL,
            ['identifier' => $identifier]
        )->fetch();

        Assert::assertNotFalse($res);
        $actualRatio = (int)floor(100 * ($res['required_count'] - $res['missing_count']) / $res['required_count']);

        Assert::assertSame($expectedRatio, $actualRatio);
    }
}
