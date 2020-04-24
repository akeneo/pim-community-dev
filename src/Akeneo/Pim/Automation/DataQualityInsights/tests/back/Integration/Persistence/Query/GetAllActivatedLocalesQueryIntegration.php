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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\CachedGetAllActivatedLocalesQuery;
use Akeneo\Test\Integration\TestCase;

class GetAllActivatedLocalesQueryIntegration extends TestCase
{
    public function test_it_returns_all_activated_locales()
    {
        $expectedLocales = new LocaleCollection([
            new LocaleCode('de_DE'),
            new LocaleCode('en_US'),
            new LocaleCode('fr_FR'),
            new LocaleCode('zh_CN'),
        ]);

        $result = $this
            ->get(CachedGetAllActivatedLocalesQuery::class)
            ->execute();

        $this->assertEquals($expectedLocales, $result);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
