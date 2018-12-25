<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UpdateSubscriptionFamilyCommand;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PhpSpec\ObjectBehavior;

class UpdateSubscriptionFamilyCommandSpec extends ObjectBehavior
{
    public function let(FamilyInterface $family): void
    {
        $this->beConstructedWith(42, $family);
    }

    public function it_is_an_update_subscription_family_command(): void
    {
        $this->shouldHaveType(UpdateSubscriptionFamilyCommand::class);
    }

    public function it_holds_a_product_id(): void
    {
        $this->productId()->shouldReturn(42);
    }

    public function it_holds_a_family($family): void
    {
        $this->family()->shouldReturn($family);
    }
}
