<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\UpdateSubscriptionFamilyCommand;
use PhpSpec\ObjectBehavior;

class UpdateSubscriptionFamilyCommandSpec extends ObjectBehavior
{
    public function it_is_an_update_subscription_family_command(): void
    {
        $this->shouldHaveType(UpdateSubscriptionFamilyCommand::class);
    }
}
