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

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\LabelCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\DeleteRecord\DeleteRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\DeleteRecords\DeleteRecordsCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class RemoveNonExistentReferenceEntityValuesIntegration extends TestCase
{
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

        $this->get('feature_flags')->enable('reference_entity');
        $this->get('akeneo_referenceentity.infrastructure.persistence.query.channel.find_channels')
            ->setChannels([
                new Channel('ecommerce', ['en_US'], LabelCollection::fromArray(['en_US' => 'Ecommerce', 'de_DE' => 'Ecommerce', 'fr_FR' => 'Ecommerce']), ['USD'])
            ]);
        $this->createAdminUser();
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
            'attributes' => ['sku', 'fabric_color', 'palette'],
            'attribute_requirements' => [
                'ecommerce' => ['sku', 'fabric_color', 'palette'],
            ]
        ]);

        $this->createProduct(
            identifier: 'sunglasses',
            userIntents: [
                new SetFamily('marketing'),
                new SetCategories(['master']),
                new SetTextValue('name', null, null, 'Sunglasses'),
                new SetSimpleReferenceEntityValue('fabric_color', null, null, 'black'),
                new SetMultiReferenceEntityValue('palette', null, null, ['black'])
            ]
        );
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
INNER JOIN pim_catalog_product product ON product.uuid = completeness.product_uuid
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

    protected function createAttribute(array $data): AttributeInterface
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);

        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, \sprintf('The attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    protected function createFamily(array $data): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);

        $constraints = $this->get('validator')->validate($family);
        Assert::assertCount(0, $constraints, (string) $constraints);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    /**
     * @param string $identifier
     * @param array<UserIntent> $userIntents
     * @return void
     */
    protected function createProduct(string $identifier, array $userIntents): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier($identifier),
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
    }

    protected function createReferenceEntity(string $referenceEntityIdentifier): void
    {
        $createCommand = new CreateReferenceEntityCommand($referenceEntityIdentifier, []);
        $violations = $this->get('validator')->validate($createCommand);
        Assert::assertCount(0, $violations, (string) $violations);
        $handler = $this->get('akeneo_referenceentity.application.reference_entity.create_reference_entity_handler');
        $handler($createCommand);
    }

    protected function createRecord(string $referenceEntityIdentifier, string $code, array $labels): void
    {
        $createCommand = new CreateRecordCommand($referenceEntityIdentifier, $code, $labels);

        $violations = $this->get('validator')->validate($createCommand);
        self::assertCount(0, $violations, (string)$violations);

        $handler = $this->get('akeneo_referenceentity.application.record.create_record_handler');
        ($handler)($createCommand);
    }

    protected function deleteRecord(string $referenceEntityIdentifier, string $code): void
    {
        $deleteCommand = new DeleteRecordCommand($code, $referenceEntityIdentifier);

        $violations = $this->get('validator')->validate($deleteCommand);
        self::assertCount(0, $violations, (string)$violations);

        $handler = $this->get('akeneo_referenceentity.application.record.delete_record_handler');
        ($handler)($deleteCommand);
    }

    private function clearDoctrineUoW(): void
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    private function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        Assert::assertNotNull($id);

        return \intval($id);
    }
}
