<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Channel\Integration\Channel\Query\Sql;

use Akeneo\Channel\Infrastructure\Query\Sql\SqlGetChannelCodeWithLocaleCodes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SqlGetChannelCodeWithLocaleCodesIntegration extends TestCase
{
    /** @var SqlGetChannelCodeWithLocaleCodes */
    private $sqlGetChannelCodeWithLocaleCodes;

    public function setUp(): void
    {
        parent::setUp();

        $this->sqlGetChannelCodeWithLocaleCodes = $this->get(
            'pim_channel.query.sql.get_channel_code_with_locale_codes'
        );
    }

    public function test_it_returns_all_channels_with_bound_locales(): void
    {
        $results = $this->sqlGetChannelCodeWithLocaleCodes->findAll();

        $this->assertIsArray($results);
        $this->assertCount(3, $results);
        $channelCodes = array_column($results, 'channelCode');
        $this->assertCount(3, $channelCodes);
        $this->assertContains('ecommerce', $channelCodes);
        $this->assertContains('mobile', $channelCodes);
        $this->assertContains('print', $channelCodes);

        $mobileChannel = array_values(array_filter($results, function ($channel) {
            return $channel['channelCode'] === 'mobile';
        }))[0];
        $localeCodesBoundToMobile = $mobileChannel['localeCodes'];
        $this->assertCount(3, $localeCodesBoundToMobile);
        $this->assertContains('en_US', $localeCodesBoundToMobile);
        $this->assertContains('fr_FR', $localeCodesBoundToMobile);
        $this->assertContains('de_DE', $localeCodesBoundToMobile);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
