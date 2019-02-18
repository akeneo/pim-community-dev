<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Asset\Integration\Persistence;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class DeleteVariationsForChannelIdFromDBIntegration extends TestCase
{
    public function test_it_deletes_non_localized_asset_variations_for_a_channel_id()
    {
        $this->addAssets(['asset1', 'asset2'], false);

        $mobileChannel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('mobile');
        $this
            ->get('pimee_product_asset.persistence.delete_variation_for_channel_id')
            ->execute($mobileChannel->getId());

        $this->assertVariationsForChannel(2, 'ecommerce');
        $this->assertVariationsForChannel(0, 'mobile');
    }

    public function test_it_deletes_localized_asset_variations_for_a_channel_id()
    {
        $this->addAssets(['asset1', 'asset2'], true);

        $mobileChannel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('mobile');
        $this
            ->get('pimee_product_asset.persistence.delete_variation_for_channel_id')
            ->execute($mobileChannel->getId());

        $this->assertVariationsForChannel(2, 'ecommerce');
        $this->assertVariationsForChannel(0, 'mobile');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->addChannel('mobile');
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param string $channelCode
     *
     * @throws \Exception
     */
    private function addChannel(string $channelCode): void
    {
        $channel = $this->get('pim_catalog.factory.channel')->create();

        $this->get('pim_catalog.updater.channel')->update($channel, [
            'code' => $channelCode,
            'currencies' => ['USD'],
            'locales' => ['fr_FR', 'en_US'],
            'category_tree' => 'master',
        ]);

        $violations = $this->get('validator')->validate($channel);
        if (0 !== $violations->count()) {
            throw new \Exception(sprintf(
                'Impossible to create a new channel "%s", %s',
                $channelCode,
                (string)$violations
            ));
        }

        $this->get('pim_catalog.saver.channel')->save($channel);
    }

    /**
     * @param array $assetCodes
     * @param bool  $localized
     *
     * @throws \Exception
     */
    private function addAssets(array $assetCodes, bool $localized): void
    {
        $assets = [];

        foreach ($assetCodes as $assetCode) {
            $asset = $this->get('pimee_product_asset.factory.asset')->create();

            $this->get('pimee_product_asset.updater.asset')->update($asset, [
                'code' => $assetCode,
            ]);

            $this->get('pimee_product_asset.factory.asset')->createReferences($asset, $localized);

            $violations = $this->get('validator')->validate($asset);
            if (0 !== $violations->count()) {
                throw new \Exception(sprintf(
                    'Impossible to create a new asset "%d", %s',
                    $assetCode,
                    (string)$violations
                ));
            }

            $assets[] = $asset;
        }

        $this->get('pimee_product_asset.saver.asset')->saveAll($assets);
    }

    /**
     * @param int    $count
     * @param string $channelCode
     */
    private function assertVariationsForChannel(int $count, string $channelCode): void
    {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier($channelCode);

        $sql = <<<SQL
SELECT COUNT(*) FROM pimee_product_asset_variation
WHERE channel_id = :channelId;
SQL;

        $statement = $this->get('database_connection')->executeQuery(
            $sql,
            ['channelId' => $channel->getId()],
            ['channelId' => \PDO:: PARAM_INT]
        );

        Assert::assertSame($count, (int) $statement->fetchColumn(0));
    }
}
