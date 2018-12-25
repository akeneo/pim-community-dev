<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\Subscription;
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
            'mapped_identifiers' => [
                [
                    'name' => 'ean',
                    'value' => '606449099812',
                ],
            ],
            'mapped_attributes' => [
                [
                    'name' => 'ram',
                    'value' => '256 MEGABYTE',
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

    public function it_combines_mapped_identifiers_and_mapped_attributes(): void
    {
        $this->getAttributes()->shouldReturn(
            [
                [
                    'name' => 'ean',
                    'value' => '606449099812',
                ],
                [
                    'name' => 'ram',
                    'value' => '256 MEGABYTE',
                ],
            ]
        );
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

    public function it_throws_an_exception_if_keys_are_missing_in_suggested_data(): void
    {
        $this->beConstructedWith(
            [
                'id' => '86b7a527-9531-4a46-bc5c-02d89dcbc7eb',
                'identifiers' => [],
                'attributes' => [],
                'extra' => [
                    'tracker_id' => '42',
                ],
                'misses_mapping' => true,
                'mapped_identifiers' => [
                    [
                        'test' => 'test',
                    ],
                ],
                'mapped_attributes' => [],
            ]
        );
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
            'mapped_identifiers' => [],
            'mapped_attributes' => [],
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
            'mapped_identifiers' => [],
            'mapped_attributes' => [],
        ]);

        $this->isMappingMissing()->shouldReturn(false);
    }

    public function it_marks_the_subscription_as_cancelled(): void
    {
        $this->beConstructedWith([
            'id' => '86b7a527-9531-4a46-bc5c-02d89dcbc7eb',
            'identifiers' => [],
            'attributes' => [],
            'extra' => [
                'tracker_id' => '42',
            ],
            'misses_mapping' => true,
            'mapped_identifiers' => [],
            'mapped_attributes' => [],
            'is_cancelled' => true,
        ]);

        $this->isCancelled()->shouldReturn(true);
    }

    public function it_marks_the_subscription_as_not_cancelled(): void
    {
        $this->beConstructedWith([
            'id' => '86b7a527-9531-4a46-bc5c-02d89dcbc7eb',
            'identifiers' => [],
            'attributes' => [],
            'extra' => [
                'tracker_id' => '42',
            ],
            'misses_mapping' => true,
            'mapped_identifiers' => [],
            'mapped_attributes' => [],
            'is_cancelled' => false,
        ]);

        $this->isCancelled()->shouldReturn(false);
    }

    public function it_marks_the_subscription_as_not_cancelled_if_there_is_no_info_about_it(): void
    {
        $this->beConstructedWith([
            'id' => '86b7a527-9531-4a46-bc5c-02d89dcbc7eb',
            'identifiers' => [],
            'attributes' => [],
            'extra' => [
                'tracker_id' => '42',
            ],
            'misses_mapping' => true,
            'mapped_identifiers' => [],
            'mapped_attributes' => [],
        ]);

        $this->isCancelled()->shouldReturn(false);
    }
}
