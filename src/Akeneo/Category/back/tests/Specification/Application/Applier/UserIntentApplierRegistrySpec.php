<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Applier;


use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Application\Applier\UserIntentApplier;
use PhpSpec\ObjectBehavior;

class UserIntentApplierRegistrySpec extends ObjectBehavior
{
    function let(UserIntentApplier $setLabelApplier) {
        $setLabelApplier->getSupportedUserIntents()->willReturn([SetLabel::class]);

        $this->beConstructedWith([$setLabelApplier]);
    }

    function it_returns_the_applier_of_a_user_intent(UserIntentApplier $setLabelApplier) {
        $this->getApplier(new SetLabel('en_US', 'The label'))->shouldReturn($setLabelApplier);
    }
}
