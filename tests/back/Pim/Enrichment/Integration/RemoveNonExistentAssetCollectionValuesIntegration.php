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

namespace AkeneoTestEnterprise\Pim\Enrichment\Integration;

use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetCommand;
use Akeneo\AssetManager\Application\Asset\DeleteAsset\DeleteAssetCommand;
use Akeneo\AssetManager\Application\Asset\DeleteAssets\DeleteAssetsCommand;
use Akeneo\AssetManager\Application\AssetFamily\CreateAssetFamily\CreateAssetFamilyCommand;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetAssetValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

final class RemoveNonExistentAssetCollectionValuesIntegration extends TestCase
{
    private const REMOVE_NON_EXISTENT_VALUES_JOB = 'remove_non_existing_product_values';

    /** @test */
    public function it_removes_deleted_assets_from_product_values(): void
    {
        $this->assertAssetCollectionValues('test1', ['packshot1', 'packshot2']);
        $this->assertCompleteness('test1', 100);
        $this->assertAssetCollectionValues('test2', ['packshot2']);
        $this->assertCompleteness('test2', 100);

        // delete "packshot2" asset
        ($this->get('akeneo_assetmanager.application.asset.delete_asset_handler'))(
            new DeleteAssetCommand('packshot2', 'packshot')
        );
        $jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        while ($jobLauncher->hasJobInQueue()) {
            $process = $jobLauncher->launchConsumerOnceInBackground();
            $process->wait();
        }

        $this->assertAssetCollectionValues('test1', ['packshot1']);
        $this->assertCompleteness('test1', 100);
        $this->assertAssetCollectionValues('test2', []);
        $this->assertCompleteness('test2', 50);
    }

    /** @test */
    public function it_removes_assets_from_product_values_after_all_assets_are_deleted(): void
    {
        $this->assertAssetCollectionValues('test1', ['packshot1', 'packshot2']);
        $this->assertAssetCollectionValues('test2', ['packshot2']);

        // delete all assets from "packshot" asset family
        ($this->get('akeneo_assetmanager.application.asset.delete_assets_handler'))(
            new DeleteAssetsCommand('packshot', ['packshot1', 'packshot2'])
        );
        $jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        while ($jobLauncher->hasJobInQueue()) {
            $process = $jobLauncher->launchConsumerOnceInBackground();
            $process->wait();
        }

        $this->assertAssetCollectionValues('test1', []);
        $this->assertCompleteness('test1', 50);
        $this->assertAssetCollectionValues('test2', []);
        $this->assertCompleteness('test2', 50);
    }

    protected function setUp(): void
    {
        parent::setUp();
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
        $this->get('feature_flags')->enable('asset_manager');
        // create a packshot asset family
        ($this->get('akeneo_assetmanager.application.asset_family.create_asset_family_handler'))(
            new CreateAssetFamilyCommand('packshot', ['en_US' => 'Packshot'])
        );
        // create two packshot assets
        ($this->get('akeneo_assetmanager.application.asset.create_asset_handler'))(
            new CreateAssetCommand('packshot', 'packshot1', ['en_US' => 'Packshot 1'])
        );
        ($this->get('akeneo_assetmanager.application.asset.create_asset_handler'))(
            new CreateAssetCommand('packshot', 'packshot2', ['en_US' => 'Packshot 2'])
        );

        // create an asset collection attribute
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update(
            $attribute,
            [
                'code' => 'packshot_attr',
                'type' => AssetCollectionType::ASSET_COLLECTION,
                'group' => 'other',
                'reference_data_name' => 'packshot',
                'localizable' => false,
                'scopable' => false,
                'useable_as_grid_filter' => true,
            ]
        );
        $attributeViolations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $attributeViolations, \sprintf('The attribute is invalid: %s', $attributeViolations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        // create a family with asset collection attribute
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update(
            $family,
            [
                'code' => 'test',
                'attributes' => ['sku', 'packshot_attr'],
                'attribute_requirements' => [
                    'ecommerce' => ['sku', 'packshot_attr'],
                ],
            ]
        );
        $familyViolations = $this->get('validator')->validate($family);
        Assert::assertCount(0, $familyViolations, \sprintf('The family is not valid: %s', $familyViolations));
        $this->get('pim_catalog.saver.family')->save($family);

        // create products with asset values
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $this->createProduct(
            'test1',
            [
                new SetFamily('test'),
                new SetAssetValue('packshot_attr', null, null, ['packshot1', 'packshot2'])
            ]
        );

        $this->createProduct(
            'test2',
            [
                new SetFamily('test'),
                new SetAssetValue('packshot_attr', null, null, ['packshot2'])
            ]
        );
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function createProduct(string $identifier, array $userIntents): void
    {
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
    }

    private function assertAssetCollectionValues(string $identifier, array $values): void
    {
        $res = $this->get('database_connection')->executeQuery(
            'SELECT raw_values FROM pim_catalog_product WHERE identifier = :identifier',
            ['identifier' => $identifier]
        )->fetch();
        Assert::assertNotFalse($res);

        $rawValues = \json_decode($res['raw_values'], true);

        Assert::assertEqualsCanonicalizing(
            $values,
            $rawValues['packshot_attr']['<all_channels>']['<all_locales>'] ?? []
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
        Assert::assertNotFalse($id);

        return \intval($id);
    }
}
