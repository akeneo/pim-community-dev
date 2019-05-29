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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\LocaleCode;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectActiveLocaleCodesQueryManagerByFranklin;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SelectEnglishActiveLocaleCodesQueryIntegration extends TestCase
{


    public function test_it_returns_english_active_locale_codes()
    {
        $localeCodes = $this->getQuery()->execute();

        Assert::count($localeCodes, 1);
        Assert::isInstanceOf($localeCodes[0], LocaleCode::class);
        Assert::eq('en_US', $localeCodes[0]->__toString());
    }

    private function getQuery(): SelectActiveLocaleCodesQueryManagerByFranklin
    {
        return $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_english_active_locale_codes');
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
