<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionExceptionSpec extends ObjectBehavior
{
    public function it_is_a_product_subscription_exception(): void
    {
        $this->shouldBeAnInstanceOf(ProductSubscriptionException::class);
    }

    public function it_is_an_exception(): void
    {
        $this->shouldBeAnInstanceOf(\Exception::class);
    }

    public function it_throws_an_inactive_connection_message(): void
    {
        $this->beConstructedThrough('inactiveConnection');
        $this->getMessage()->shouldReturn(
            'akeneo_franklin_insights.entity.product_subscription.constraint.inactive_connection'
        );
    }

    public function it_throws_an_insufficient_credits_message(): void
    {
        $this->beConstructedThrough('insufficientCredits');
        $this
            ->getMessage()
            ->shouldReturn('akeneo_franklin_insights.entity.product_subscription.constraint.insufficient_credits');
    }

    public function it_throws_an_invalid_identifiers_mapping_message(): void
    {
        $this->beConstructedThrough('invalidIdentifiersMapping');
        $this
            ->getMessage()
            ->shouldReturn('akeneo_franklin_insights.entity.product_subscription.constraint.no_identifiers_mapping');
    }

    public function it_throws_a_family_required_message(): void
    {
        $this->beConstructedThrough('familyRequired');
        $this
            ->getMessage()
            ->shouldReturn('akeneo_franklin_insights.entity.product_subscription.constraint.family_required');
    }

    public function it_throws_an_invalid_mapped_values_message(): void
    {
        $this->beConstructedThrough('invalidMappedValues');
        $this
            ->getMessage()
            ->shouldReturn('akeneo_franklin_insights.entity.product_subscription.constraint.invalid_mapped_values');
    }

    public function it_throws_an_already_subscribed_product_message(): void
    {
        $this->beConstructedThrough('alreadySubscribedProduct');
        $this->getMessage()->shouldReturn(
            'akeneo_franklin_insights.entity.product_subscription.constraint.already_subscribed_product'
        );
    }

    public function it_throws_an_variant_product_message(): void
    {
        $this->beConstructedThrough('variantProduct');
        $this->getMessage()->shouldReturn(
            'akeneo_franklin_insights.entity.product_subscription.constraint.variant_product'
        );
    }
}
