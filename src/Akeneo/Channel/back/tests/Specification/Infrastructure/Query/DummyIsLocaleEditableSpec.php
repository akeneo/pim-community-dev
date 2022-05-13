<?php

declare(strict_types=1);

namespace Specification\Akeneo\Channel\Infrastructure\Query;

use Akeneo\Channel\API\Query\IsLocaleEditable;
use Akeneo\Channel\Infrastructure\Query\DummyIsLocaleEditable;
use PhpSpec\ObjectBehavior;

class DummyIsLocaleEditableSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(DummyIsLocaleEditable::class);
        $this->shouldImplement(IsLocaleEditable::class);
    }

    public function it_returns_always_true()
    {
        foreach (['en_US', 'fr_FR', 'de_DE'] as $localeCode) {
            $this->forUserId($localeCode, 1)->shouldReturn(true);
            $this->forUserId($localeCode, 4638765483)->shouldReturn(true);
            $this->forUserId($localeCode, 0)->shouldReturn(true);
        }
    }
}
