<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Channel\Integration\Channel\Doctrine\Query;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class GetChannelCategoryCodeIntegration extends TestCase
{
    public function test_that_it_gets_an_existing_channel_category_code(): void
    {
        $categoryCode = $this->get('akeneo_channel.query.get_channel_category_code')
            ->execute('ecommerce');

        self::assertEquals('master', $categoryCode);
    }

    public function test_that_it_returns_null_if_the_channel_does_not_exist(): void
    {
        $categoryCode = $this->get('akeneo_channel.query.get_channel_category_code')
            ->execute('wrong_channel_code');

        self::assertNull($categoryCode);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
