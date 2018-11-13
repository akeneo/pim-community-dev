<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\Subscription;
use PhpSpec\ObjectBehavior;

class SubscriptionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith([
            'id' => '86b7a527-9531-4a46-bc5c-02d89dcbc7eb',
            'identifiers' => [
                'upc' => '606449099812',
            ],
            'attributes' => [
                'Memory' => 'RAM (Installed): 256 MB',
            ],
            'extra' => [
                'tracker_id' => '42',
                'family' => [
                    'code' => 'laptop',
                    'label' => ['en_US' => 'Laptop'],
                ],
            ],
            'misses_mapping' => false,
        ]);
    }

    public function it_is_subscription(): void
    {
        $this->shouldHaveType(Subscription::class);
    }

    public function it_returns_a_subscription_id(): void
    {
        $this->getSubscriptionId()->shouldReturn('86b7a527-9531-4a46-bc5c-02d89dcbc7eb');
    }

    public function it_returns_all_attributes_combined(): void
    {
        $this->getAttributes()->shouldReturn([
            'upc' => '606449099812',
            'Memory' => 'RAM (Installed): 256 MB',
        ]);
    }

    public function it_returns_the_tracker_id_as_an_integer(): void
    {
        $this->getTrackerId()->shouldReturn(42);
    }

    public function it_throws_an_exception_if_the_validation_fails(): void
    {
        $this->beConstructedWith([]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_missing_mapping_if_the_information_exists(): void
    {
        $this->beConstructedWith([
            'id' => '86b7a527-9531-4a46-bc5c-02d89dcbc7eb',
            'identifiers' => [],
            'attributes' => [],
            'extra' => [
                'tracker_id' => '42',
            ],
            'misses_mapping' => true,
        ]);

        $this->isMappingMissing()->shouldReturn(true);
    }

    public function it_handles_missing_mapping_if_the_information_does_not_exist(): void
    {
        $this->beConstructedWith([
            'id' => '86b7a527-9531-4a46-bc5c-02d89dcbc7eb',
            'identifiers' => [],
            'attributes' => [],
            'extra' => [
                'tracker_id' => '42',
            ],
            'misses_mapping' => false,
        ]);

        $this->isMappingMissing()->shouldReturn(false);
    }
}
