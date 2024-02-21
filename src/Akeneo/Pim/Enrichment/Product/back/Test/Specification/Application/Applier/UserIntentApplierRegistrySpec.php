<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use PhpSpec\ObjectBehavior;

class UserIntentApplierRegistrySpec extends ObjectBehavior
{
    function let(
        UserIntentApplier $setEnabledApplier,
        UserIntentApplier $setNumberValueApplier
    ) {
        $setEnabledApplier->getSupportedUserIntents()->willReturn([SetEnabled::class]);
        $setNumberValueApplier->getSupportedUserIntents()->willReturn([SetNumberValue::class]);

        $this->beConstructedWith([$setEnabledApplier, $setNumberValueApplier]);
    }

    function it_returns_the_applier_of_a_user_intent(
        UserIntentApplier $setEnabledApplier,
        UserIntentApplier $setNumberValueApplier
    ) {
        $this->getApplier(new SetEnabled(true))->shouldReturn($setEnabledApplier);
        $this->getApplier(new SetEnabled(false))->shouldReturn($setEnabledApplier);
        $this->getApplier(new SetNumberValue('attribute', null, null, '1'))->shouldReturn($setNumberValueApplier);
    }

    function it_returns_null_when_no_applier_is_found()
    {
        $this->getApplier(new SetTextValue('description', null, null, 'Lorem Ipsum'))->shouldReturn(null);
    }
}
