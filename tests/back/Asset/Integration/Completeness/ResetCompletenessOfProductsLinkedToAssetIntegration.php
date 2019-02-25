<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Asset\Integration\Completeness;

use Akeneo\Asset\Bundle\AttributeType\AttributeTypes;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ResetCompletenessOfProductsLinkedToAssetIntegration extends TestCase
{
    /** @var JobLauncher */
    private $jobLauncher;

    public function test_that_product_completeness_is_reset_when_a_linked_asset_is_updated(): void
    {
        $asset = $this->createAsset('my_asset_code');
        static::assertFalse($this->jobLauncher->hasJobInQueue());
        $product = $this->createProduct(
            'some_sku',
            'family_with_assets',
            [
                'my_assets' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => ['my_asset_code'],
                    ],
                ],
            ]
        );
        // waiting ES indexation
        sleep(2);

        static::assertEquals(50, $this->getCompletenessForProduct($product->getId(), 'ecommerce', 'en_US'));

        $this->get('pimee_product_asset.updater.asset')->update(
            $asset,
            ['description' => 'Lorem ipsum dolor sit amet']
        );
        $this->get('pimee_product_asset.saver.asset')->save($asset);

        static::assertTrue($this->jobLauncher->hasJobInQueue());
        $this->jobLauncher->launchConsumerOnce();

        static::assertNull($this->getCompletenessForProduct($product->getId(), 'ecommerce', 'en_US'));
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->jobLauncher = new JobLauncher(static::$kernel);
        $this->loadFixtures();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function loadFixtures(): void
    {
        $assets = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update(
            $assets,
            [
                'code' => 'my_assets',
                'type' => AttributeTypes::ASSETS_COLLECTION,
                'group' => AttributeGroup::DEFAULT_GROUP_CODE,
                'reference_data_name' => 'assets',
            ]
        );
        $this->get('pim_catalog.saver.attribute')->save($assets);

        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update(
            $family,
            [
                'code' => 'family_with_assets',
                'attributes' => ['sku', 'my_assets'],
                'attribute_requirements' => [
                    'ecommerce' => ['sku', 'my_assets'],
                ],
            ]
        );
        $this->get('pim_catalog.saver.family')->save($family);
    }

    /**
     * @param string $assetCode
     *
     * @return AssetInterface
     */
    private function createAsset(string $assetCode): AssetInterface
    {
        $asset = $this->get('pimee_product_asset.factory.asset')->create();
        $this->get('pimee_product_asset.updater.asset')->update($asset, ['code' => $assetCode]);
        $this->get('pimee_product_asset.saver.asset')->save($asset);

        return $asset;
    }

    private function createProduct(string $identifier, string $familyCode, array $data): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $familyCode);
        $this->get('pim_catalog.updater.product')->update($product, ['values' => $data]);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    /**
     * @param int $productId
     * @param string $channelCode
     * @param string $localeCode
     *
     * @return array|null
     */
    private function getCompletenessForProduct(int $productId, string $channelCode, string $localeCode): ?int
    {
        $sql = <<<SQL
SELECT c.ratio
FROM pim_catalog_completeness c
INNER JOIN pim_catalog_locale l on c.locale_id = l.id
INNER JOIN pim_catalog_channel ch on c.channel_id = ch.id
WHERE c.product_id = :productId
AND ch.code = :channelCode
AND l.code = :localeCode;
SQL;
        $statement = $this->get('database_connection')->executeQuery(
            $sql,
            [
                'productId' => $productId,
                'channelCode' => $channelCode,
                'localeCode' => $localeCode,
            ]
        );
        $result = $statement->fetch();

        return false !== $result ? (int)$result['ratio'] : null;
    }
}
