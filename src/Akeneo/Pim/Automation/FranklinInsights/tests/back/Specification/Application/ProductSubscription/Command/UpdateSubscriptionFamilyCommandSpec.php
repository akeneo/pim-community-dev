<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Command\UpdateSubscriptionFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use PhpSpec\ObjectBehavior;

class UpdateSubscriptionFamilyCommandSpec extends ObjectBehavior
{
    public function it_is_an_update_subscription_family_command(): void
    {
        $this->beConstructedWith(new ProductId(42), new FamilyCode('a_family'));
        $this->shouldHaveType(UpdateSubscriptionFamilyCommand::class);
    }

    public function it_holds_a_product_id(): void
    {
        $productId = new ProductId(42);
        $this->beConstructedWith($productId, new FamilyCode('a_family'));
        $this->productId()->shouldReturn($productId);
    }

    public function it_holds_a_family_code(): void
    {
        $familyCode = new FamilyCode('a_family');
        $this->beConstructedWith(new ProductId(42), $familyCode);

        $this->familyCode()->shouldReturn($familyCode);
    }
}
