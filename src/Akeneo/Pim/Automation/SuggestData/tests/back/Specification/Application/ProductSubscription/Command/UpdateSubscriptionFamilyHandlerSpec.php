<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UpdateSubscriptionFamilyHandler;
use PhpSpec\ObjectBehavior;

class UpdateSubscriptionFamilyHandlerSpec extends ObjectBehavior
{
    public function it_is_an_update_subscription_family_handler(): void
    {
        $this->shouldHaveType(UpdateSubscriptionFamilyHandler::class);
    }
}
