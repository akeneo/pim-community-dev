<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\Subscription;
use PhpSpec\ObjectBehavior;

class SubscriptionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith([
            'id' => '86b7a527-9531-4a46-bc5c-02d89dcbc7eb',
            'identifiers' => [
                'upc' => '606449099812',
            ],
            'attributes' => [
                'Memory' => 'RAM (Installed): 256 MB',
            ],
            'tracker_id' => 42
        ]);
    }

    public function it_is_subscription()
    {
        $this->shouldHaveType(Subscription::class);
    }

    public function it_returns_a_subscription_id()
    {
        $this->getSubscriptionId()->shouldReturn('86b7a527-9531-4a46-bc5c-02d89dcbc7eb');
    }

    public function it_returns_all_attributes_combined()
    {
        $this->getAttributes()->shouldReturn([
            'upc' => '606449099812',
            'Memory' => 'RAM (Installed): 256 MB',
        ]);
    }

    public function it_throws_an_exception_if_the_validation_fails()
    {
        $this->beConstructedWith([]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
