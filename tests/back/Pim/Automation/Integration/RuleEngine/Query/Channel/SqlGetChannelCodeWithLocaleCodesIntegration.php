<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Automation\Integration\RuleEngine\Query\Channel;

use Akeneo\Pim\Automation\RuleEngine\Bundle\Query\Sql\Channel\SqlGetChannelCodeWithLocaleCodes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class SqlGetChannelCodeWithLocaleCodesIntegration extends TestCase
{
    /** @var SqlGetChannelCodeWithLocaleCodes */
    private $sqlGetChannelCodeWithLocaleCodes;

    public function setUp(): void
    {
        parent::setUp();
        $this->sqlGetChannelCodeWithLocaleCodes = $this->get('pimee_catalog_rule.query.sql.get_channel_code_with_locale_codes');
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
