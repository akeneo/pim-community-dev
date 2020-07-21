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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use PhpSpec\ObjectBehavior;

class SpellcheckResultByLocaleCollectionSpec extends ObjectBehavior
{
    public function it_returns_the_spellcheck_results_as_an_array_of_booleans()
    {
        $this->add(new LocaleCode('en_US'), SpellCheckResult::good());
        $this->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove());

        $this->toArrayBool()->shouldBeLike([
            'en_US' => false,
            'fr_FR' => true,
        ]);
    }

    public function it_indicates_if_the_spelling_should_be_improved_for_at_least_one_locale()
    {
        $this->isToImprove()->shouldReturn(null);

        $this->add(new LocaleCode('en_US'), SpellCheckResult::good());
        $this->isToImprove()->shouldReturn(false);

        $this->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove());
        $this->isToImprove()->shouldReturn(true);
    }

    public function it_gets_labels_to_improve_number()
    {
        $this->add(new LocaleCode('en_US'), SpellCheckResult::good());
        $this->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove());
        $this->add(new LocaleCode('de_DE'), SpellCheckResult::toImprove());
        $this->add(new LocaleCode('it_IT'), SpellCheckResult::toImprove());

        $this->getLabelsToImproveNumber()->shouldReturn(3);
    }

    public function it_returns_the_list_of_locales_to_improve()
    {
        $this->add(new LocaleCode('en_US'), SpellCheckResult::good());
        $this->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove());
        $this->add(new LocaleCode('de_DE'), SpellCheckResult::toImprove());

        $this->getLocalesToImprove()->shouldBeLike(['fr_FR', 'de_DE']);
    }
}
