<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Locales;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryLocalesSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith([
            'en_US' => 58,
            'fr_FR' => 90,
        ]);
    }

    public function it_gets_a_locale_id_from_its_id(): void
    {
        $this->getIdByCode('fr_FR')->shouldReturn(90);
    }

    public function it_gets_a_locale_code_from_its_id(): void
    {
        $this->getCodeById(90)->shouldReturn('fr_FR');
    }

    public function it_returns_null_if_the_locale_does_not_exist(): void
    {
        $this->getIdByCode('fo_BA')->shouldReturn(null);
        $this->getCodeById(999)->shouldReturn(null);
    }
}
