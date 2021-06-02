<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Channel;

use Akeneo\AssetManager\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindActivatedLocalesPerChannelsTest extends SqlIntegrationTestCase
{
    private FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels;

    public function setUp(): void
    {
        parent::setUp();

        $this->findActivatedLocalesPerChannels = $this->get('akeneo_assetmanager.infrastructure.search.elasticsearch.asset.query.find_activated_locales_per_channels');
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_generates_an_empty_list(): void
    {
        $this->removeAllLocalesForAllChannels();
        Assert::assertEmpty($this->findActivatedLocalesPerChannels->findAll());
    }

    /**
     * @test
     */
    public function it_generates_the_matrix(): void
    {
        $localesPerChannels = $this->findActivatedLocalesPerChannels->findAll();
//      [
//          'ecommerce' => ['en_US', 'fr_FR'],
//          'mobile'    => ['de_DE'],
//          'print'     => ['en_US'],
//      ]
        Assert::assertArrayHasKey('ecommerce', $localesPerChannels);
        Assert::assertCount(2, $localesPerChannels['ecommerce']);
        Assert::assertContains('fr_FR', $localesPerChannels['ecommerce']);
        Assert::assertContains('en_US', $localesPerChannels['ecommerce']);

        Assert::assertArrayHasKey('mobile', $localesPerChannels);
        Assert::assertCount(1, $localesPerChannels['mobile']);
        Assert::assertContains('de_DE', $localesPerChannels['mobile']);

        Assert::assertArrayHasKey('print', $localesPerChannels);
        Assert::assertCount(1, $localesPerChannels['print']);
        Assert::assertContains('en_US', $localesPerChannels['print']);
    }

    private function removeAllLocalesForAllChannels()
    {
        $this->get('database_connection')->executeUpdate('DELETE FROM pim_catalog_channel_locale;');
    }
}
