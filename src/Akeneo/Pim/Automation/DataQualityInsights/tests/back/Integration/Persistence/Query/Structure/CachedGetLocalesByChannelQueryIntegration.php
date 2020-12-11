<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\CachedGetLocalesByChannelQuery;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CachedGetLocalesByChannelQueryIntegration extends TestCase
{
    public function test_it_returns_all_locales_by_channel()
    {
        $expectedLocalesAndChanels = [
            'ecommerce' => ['en_US'],
            'ecommerce_china' => ['en_US', 'zh_CN'],
            'tablet' => ['de_DE', 'en_US', 'fr_FR'],
        ];

        $result = $this
            ->get(CachedGetLocalesByChannelQuery::class)
            ->getArray();

        $this->assertSame($expectedLocalesAndChanels, $result);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
