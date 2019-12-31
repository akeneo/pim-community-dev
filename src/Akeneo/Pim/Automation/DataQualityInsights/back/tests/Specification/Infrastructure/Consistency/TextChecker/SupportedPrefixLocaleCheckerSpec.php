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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use PhpSpec\ObjectBehavior;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class SupportedPrefixLocaleCheckerSpec extends ObjectBehavior
{
    public function it_supports_locale()
    {
        $this->isSupported(new LocaleCode('en_US'))->shouldBe(true);
        $this->isSupported(new LocaleCode('fr_FR'))->shouldBe(true);
        $this->isSupported(new LocaleCode('es_ES'))->shouldBe(true);
        $this->isSupported(new LocaleCode('de_DE'))->shouldBe(true);

        $this->isSupported(new LocaleCode('en_GB'))->shouldBe(true);
        $this->isSupported(new LocaleCode('fr_CA'))->shouldBe(true);
        $this->isSupported(new LocaleCode('es_AR'))->shouldBe(true);
        $this->isSupported(new LocaleCode('de_CH'))->shouldBe(true);
    }

    public function it_does_not_support_locale()
    {
        $this->isSupported(new LocaleCode('it_IT'))->shouldBe(false);
        $this->isSupported(new LocaleCode('fi_FI'))->shouldBe(false);

        $this->isSupported(new LocaleCode('fr'))->shouldBe(false);
        $this->isSupported(new LocaleCode('frFR'))->shouldBe(false);
        $this->isSupported(new LocaleCode('fr-FR'))->shouldBe(false);
    }
}
