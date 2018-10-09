<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\SqlFindActivatedLocalesPerChannels;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindActivatedLocalesPerChannelsTest extends SqlIntegrationTestCase
{
    /** @var SqlFindActivatedLocalesPerChannels */
    private $findActivatedLocalesPerChannels;

    public function setUp()
    {
        parent::setUp();

        $this->findActivatedLocalesPerChannels = $this->get('akeneo_referenceentity.infrastructure.search.elasticsearch.find_activated_locales_per_channels');
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_generates_an_empty_list(): void
    {
        $this->removeAllLocalesForAllChannels();
        Assert::assertEmpty(($this->findActivatedLocalesPerChannels)());
    }

    /**
     * @test
     */
    public function it_generates_the_matrix(): void
    {
        Assert::assertSame(
            [
                'ecommerce' => ['fr_FR', 'en_US'],
                'mobile'    => ['de_DE'],
                'print'     => ['en_US'],
            ],
            ($this->findActivatedLocalesPerChannels)()
        );
    }

    private function removeAllLocalesForAllChannels()
    {
        $this->get('database_connection')->executeUpdate('DELETE FROM pim_catalog_channel_locale;');
    }
}
