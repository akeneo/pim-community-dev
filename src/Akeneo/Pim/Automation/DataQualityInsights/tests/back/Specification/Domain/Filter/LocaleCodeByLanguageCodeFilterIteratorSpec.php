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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Filter;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use PhpSpec\ObjectBehavior;

class LocaleCodeByLanguageCodeFilterIteratorSpec extends ObjectBehavior
{
    public function it_accepts_with_prefixed_language_code(
        \Iterator $localesCollection
    ) {
        $this->beConstructedWith($localesCollection, new LanguageCode('en'));

        $localesCollection->current()->willReturn(new LocaleCode('en_US'));
        $this->accept()->shouldBe(true);

        $localesCollection->current()->willReturn(new LocaleCode('en_GB'));
        $this->accept()->shouldBe(true);

        $localesCollection->current()->willReturn(new LocaleCode('fr_FR'));
        $this->accept()->shouldBe(false);
    }

    public function it_accepts_with_portuguese_language(
        \Iterator $localesCollection
    ) {
        $this->beConstructedWith($localesCollection, new LanguageCode('pt_BR'));

        $localesCollection->current()->willReturn(new LocaleCode('en_US'));
        $this->accept()->shouldBe(false);

        $localesCollection->current()->willReturn(new LocaleCode('en_GB'));
        $this->accept()->shouldBe(false);

        $localesCollection->current()->willReturn(new LocaleCode('pt_BR'));
        $this->accept()->shouldBe(true);

        // Portuguese from Portugal is not supported yet
        $localesCollection->current()->willReturn(new LocaleCode('pt_PT'));
        $this->accept()->shouldBe(false);
    }
}
