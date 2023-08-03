<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Pim\Enrichment\Bundle\Command\RestoreSortedAssetCollectionDueToIncidentCommand;
use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractProduct;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddAssetValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveAssetValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetAssetValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Webmozart\Assert\Assert;

final class RestoreSortedAssetCollectionDueToIncidentCommandEndToEnd extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The Product Service API requires a loggedin user to create/update products
        $this->createUser('system');
        $this->logAs('system');

        $this->get('feature_flags')->enable('asset_manager');

        $AM = self::getContainer()->get('akeneo_assetmanager.common.helper.fixtures_loader');
        $AM->assetFamily('brand')->load();
        $AM->asset('brand', 'main_asset')->load();
        $AM->asset('brand', 'additional_asset_01')->load();
        $AM->asset('brand', 'additional_asset_02')->load();
        $AM->asset('brand', 'additional_asset_03')->load();

        $this->createAttribute([
            'code' => 'brands',
            'type' => 'pim_catalog_asset_collection',
            'scopable' => false,
            'localizable' => true,
            'reference_data_name' => 'brand',
        ]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_loses_asset_order_without_the_migration_with_a_product_created_before_incident()
    {
        $this->createProductWithAssetCodes(['main_asset', 'additional_asset_01', 'additional_asset_02'], 'before_incident');
        $this->orderAssetCodesAlphabeticallyLikeTheBug('during_incident');

        $this->assertAssetCodesOrder(['additional_asset_01', 'additional_asset_02', 'main_asset']);
    }

    public function test_it_loses_asset_order_without_the_migration_with_a_product_created_during_incident()
    {
        $this->createProductWithAssetCodes(['main_asset', 'additional_asset_01', 'additional_asset_02'], 'during_incident');
        $this->orderAssetCodesAlphabeticallyLikeTheBug('during_incident');

        $this->assertAssetCodesOrder(['additional_asset_01', 'additional_asset_02', 'main_asset']);
    }
    public function test_it_restores_assets_order_with_a_product_created_before_incident()
    {
        $this->createProductWithAssetCodes(['main_asset', 'additional_asset_01', 'additional_asset_02'], 'before_incident');
        $this->orderAssetCodesAlphabeticallyLikeTheBug('during_incident');

        $this->executeCommand();

        $this->assertNumberLineInTrackingTable(1);
        $this->assertAssetCodesOrder(['main_asset', 'additional_asset_01', 'additional_asset_02']);
    }

    public function test_it_restores_assets_order_with_a_product_created_during_incident()
    {
        $this->createProductWithAssetCodes(['main_asset', 'additional_asset_01', 'additional_asset_02'], 'during_incident');
        $this->orderAssetCodesAlphabeticallyLikeTheBug('during_incident');

        $this->executeCommand();

        $this->assertNumberLineInTrackingTable(1);
        $this->assertAssetCodesOrder(['main_asset', 'additional_asset_01', 'additional_asset_02']);
    }

    public function test_it_tracks_assets_to_restore_without_restoring_them_in_dry_run()
    {
        $this->createProductWithAssetCodes(['main_asset', 'additional_asset_01', 'additional_asset_02'], 'during_incident');
        $this->orderAssetCodesAlphabeticallyLikeTheBug('during_incident');

        $this->executeCommand(withDryRun: true);

        $this->assertNumberLineInTrackingTable(1);
        $this->assertAssetCodesOrder(['additional_asset_01', 'additional_asset_02', 'main_asset', ]);
    }

    public function test_it_does_not_restore_assets_order_when_asset_value_was_added_into_collection()
    {
        $this->createProductWithAssetCodes(['main_asset', 'additional_asset_01', 'additional_asset_02'], 'during_incident');
        $this->orderAssetCodesAlphabeticallyLikeTheBug('during_incident');
        $this->addAssetCode('additional_asset_03', 'during_incident');

        $this->executeCommand();

        $this->assertNumberLineInTrackingTable(0);
        $this->assertAssetCodesOrder(['additional_asset_01', 'additional_asset_02', 'main_asset', 'additional_asset_03']);
    }

    public function test_it_does_not_restore_assets_order_when_asset_value_was_removed_from_collection()
    {
        $this->createProductWithAssetCodes(['main_asset', 'additional_asset_01', 'additional_asset_02'], 'during_incident');
        $this->orderAssetCodesAlphabeticallyLikeTheBug('during_incident');
        $this->removeAssetCode('additional_asset_01', 'during_incident');

        $this->executeCommand();

        $this->assertNumberLineInTrackingTable(0);
        $this->assertAssetCodesOrder(['additional_asset_02', 'main_asset']);
    }

    public function test_it_does_not_restore_assets_order_when_some_were_reordered_during_incident()
    {
        $this->createProductWithAssetCodes(['main_asset', 'additional_asset_01', 'additional_asset_02'], 'during_incident');
        $this->orderAssetCodesAlphabeticallyLikeTheBug('during_incident');
        $this->reorderAssetCodes(['main_asset', 'additional_asset_02', 'additional_asset_01'], 'during_incident');

        $this->executeCommand();

        $this->assertNumberLineInTrackingTable(0);
        $this->assertAssetCodesOrder(['main_asset', 'additional_asset_02', 'additional_asset_01']);
    }

    public function test_it_does_not_restore_assets_order_when_it_was_rdered_due_to_incident_but_reordered_after_that_manually()
    {
        $this->createProductWithAssetCodes(['main_asset', 'additional_asset_01', 'additional_asset_02'], 'before_incident');
        $this->orderAssetCodesAlphabeticallyLikeTheBug('during_incident');
        $this->reorderAssetCodes(['main_asset', 'additional_asset_02', 'additional_asset_01'], 'after_incident');

        $this->executeCommand();

        $this->assertNumberLineInTrackingTable(0);
        $this->assertAssetCodesOrder(['main_asset', 'additional_asset_02', 'additional_asset_01']);
    }

    /**
     * The product is created (or updated) during incident but not on asset.
     * Then the product is reordered on asset in alphabetical order, but after the incident.
     * We don't expect the asset to change in that case.
     */
    public function test_it_does_not_restore_assets_order_when_it_was_updated_during_incident_and_asset_collection_ordered_after_incident()
    {
        $this->createProductWithAssetCodes(['main_asset', 'additional_asset_01', 'additional_asset_02'], 'before_incident');

        $this->createOrUpdateProduct(Uuid::fromString('a999edd3-87aa-421f-baea-7685dec8db9f'), [new SetCategories(['master'])], 'during_incident');
        $this->reorderAssetCodes(['additional_asset_01', 'additional_asset_02', 'main_asset'], 'after_incident');

        $this->executeCommand();

        $this->assertNumberLineInTrackingTable(0);
        $this->assertAssetCodesOrder(['additional_asset_01', 'additional_asset_02', 'main_asset']);
    }

    protected function assertAssetCodesOrder(array $codes): void
    {
        $this->assertEquals($codes, $this->getPersistedAssetCodes());
    }

    protected function createProductWithAssetCodes(array $codes, string $timing): void
    {

        $this->createOrUpdateProduct(Uuid::fromString('a999edd3-87aa-421f-baea-7685dec8db9f'), [
            new SetAssetValue(
                attributeCode: 'brands',
//                channelCode: 'ecommerce',
                channelCode: null,
                localeCode: 'en_US',
                assetCodes: $codes,
            ),
        ], $timing);
    }

    protected function addAssetCode(string $code, string $timing): void
    {
        $this->createOrUpdateProduct(Uuid::fromString('a999edd3-87aa-421f-baea-7685dec8db9f'), [
            new AddAssetValue(
                attributeCode: 'brands',
//                channelCode: 'ecommerce',
                channelCode: null,
                localeCode: 'en_US',
                assetCodes: [$code],
            ),
        ], $timing);
    }

    protected function removeAssetCode(string $code, string $timing): void
    {
        $this->createOrUpdateProduct(Uuid::fromString('a999edd3-87aa-421f-baea-7685dec8db9f'), [
            new RemoveAssetValue(
                attributeCode: 'brands',
//                channelCode: 'ecommerce',
                channelCode: null,
                localeCode: 'en_US',
                assetCodes: [$code],
            ),
        ], $timing);
    }

    protected function reorderAssetCodes(array $codes, string $timing): void
    {
        $this->createOrUpdateProduct(Uuid::fromString('a999edd3-87aa-421f-baea-7685dec8db9f'), [
            new SetAssetValue(
                attributeCode: 'brands',
//                channelCode: 'ecommerce',
                channelCode: null,
                localeCode: 'en_US',
                assetCodes: $codes,
            ),
        ], $timing);
    }

    protected function getPersistedAssetCodes(): array
    {
        $product = $this->getProduct(Uuid::fromString('a999edd3-87aa-421f-baea-7685dec8db9f'));

        return array_map(fn($code) => (string) $code,
            $product->getValue(
                attributeCode: 'brands',
                localeCode: 'en_US',
//                scopeCode: 'ecommerce',
                scopeCode: null,
            )->getData());
    }

    protected function orderAssetCodesAlphabeticallyLikeTheBug(string $timing): void
    {
        $codes = $this->getPersistedAssetCodes();
        sort($codes);

        $this->createOrUpdateProduct(Uuid::fromString('a999edd3-87aa-421f-baea-7685dec8db9f'), [
            new SetAssetValue(
                attributeCode: 'brands',
//                channelCode: 'ecommerce',
                channelCode: null,
                localeCode: 'en_US',
                assetCodes: $codes,
            ),
        ], $timing);
    }

    protected function createAttribute(array $data): void
    {
        $data['group'] ??= 'other';

        $attribute = self::getContainer()->get('pim_catalog.factory.attribute')->create();
        self::getContainer()->get('pim_catalog.updater.attribute')->update($attribute, $data);
        self::getContainer()->get('pim_catalog.saver.attribute')->save($attribute);

        self::getContainer()->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function createOrUpdateProduct(
        UuidInterface $uuid,
        array $intents = [],
        string $timing
    ): AbstractProduct {
        $ids = $this->get('database_connection')->executeQuery('SELECT id FROM pim_versioning_version')->fetchFirstColumn();

        $bus = self::getContainer()->get('pim_enrich.product.message_bus');

        $user = self::getContainer()->get('security.token_storage')->getToken()?->getUser();
        \assert($user instanceof UserInterface);
        $userId = $user->getId();

        Assert::notNull($userId);

        $command = UpsertProductCommand::createWithUuid(
            $userId,
            ProductUuid::fromUuid($uuid),
            $intents,
        );

        $bus->dispatch($command);

        self::getContainer()->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();


        if ($timing === 'before_incident') {
            $date = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, RestoreSortedAssetCollectionDueToIncidentCommand::START_INCIDENT_DATE);
            $updatedDateVersioning = $date->modify('- 1 hour');
        } else if ($timing === 'during_incident') {
            $date = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, RestoreSortedAssetCollectionDueToIncidentCommand::START_INCIDENT_DATE);
            $updatedDateVersioning = $date->modify('+ 1 hour');
        } else if ($timing === 'after_incident') {
            $date = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, RestoreSortedAssetCollectionDueToIncidentCommand::END_INCIDENT_DATE);
            $updatedDateVersioning = $date->modify('+ 1 hour');
        } else {
            throw new \LogicException('Invalid timing parameter.');
        }

        $this->get('database_connection')->executeQuery(
            'UPDATE pim_versioning_version SET logged_at = :logged_at where id NOT IN (:ids)',
            [
                'logged_at' =>  $updatedDateVersioning,
                'ids' => $ids
            ],
            [
                'ids' => Connection::PARAM_INT_ARRAY,
                'logged_at' => Types::DATETIME_IMMUTABLE
            ]
        );


        return self::getContainer()->get('pim_catalog.repository.product')->findOneByUuid($uuid);
    }

    protected function getProduct(UuidInterface $uuid): AbstractProduct
    {
        self::getContainer()->get('pim_connector.doctrine.cache_clearer')->clear();

        return self::getContainer()->get('pim_catalog.repository.product')->findOneByUuid($uuid);
    }

    protected function logAs(string $username): TokenInterface
    {
        $user = self::getContainer()->get('pim_user.repository.user')->findOneByIdentifier($username);
        Assert::notNull($user);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        self::getContainer()->get('security.token_storage')->setToken($token);

        return $token;
    }

    protected function createUser(string $username, ?array $groups = null, ?array $roles = null): UserInterface
    {
        $userPayload = [
            'username' => $username,
            'password' => \random_int(0, \mt_getrandmax()),
            'first_name' => 'firstname_'.\random_int(0, \mt_getrandmax()),
            'last_name' => 'lastname_'.\random_int(0, \mt_getrandmax()),
            'email' => \sprintf('%s@example.com', $username),
        ];

        if (null !== $groups) {
            $userPayload['groups'] = $groups;
        }

        if (null !== $roles) {
            $userPayload['roles'] = $roles;
        }

        $user = self::getContainer()->get('pim_user.factory.user')->create();
        self::getContainer()->get('pim_user.updater.user')->update($user, $userPayload);

        $violations = self::getContainer()->get('validator')->validate($user);
        Assert::count($violations, 0);

        self::getContainer()->get('pim_user.saver.user')->save($user);

        return $user;
    }

    private function executeCommand(bool $withDryRun = false): void
    {
        $kernel = self::$kernel;
        $application = new Application($kernel);
        $command = $application->find('pim:restore-sorted-assets-incident');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--dry-run' => $withDryRun]);
    }

    private function assertNumberLineInTrackingTable(int $expectedNumberLines) : void
    {
        $numberLines =(int) $this->get('database_connection')->fetchOne(
            'select count(*) from incident_product_asset_ordering_table'
        );
        $this->assertEquals($expectedNumberLines, $numberLines);
    }
}
