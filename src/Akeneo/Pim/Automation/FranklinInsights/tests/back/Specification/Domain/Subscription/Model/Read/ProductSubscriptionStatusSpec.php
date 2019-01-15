<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionStatus;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ProductSubscriptionStatusSpec extends ObjectBehavior
{
    public function it_is_a_product_subscription_status(): void
    {
        $this->beConstructedWith(
            Argument::type(ConnectionStatus::class),
            Argument::type('bool'),
            Argument::type('bool'),
            Argument::type('bool'),
            Argument::type('bool')
        );

        $this->beAnInstanceOf(ProductSubscriptionStatus::class);
    }

    public function it_indicates_that_the_product_can_be_subscribed(): void
    {
        $connectionStatus = new ConnectionStatus(true, true, true, 42);
        $this->beConstructedWith($connectionStatus, false, true, true, false);

        $this->getConnectionStatus()->shouldReturn($connectionStatus);
        $this->isSubscribed()->shouldReturn(false);
        $this->hasFamily()->shouldReturn(true);
        $this->isMappingFilled()->shouldReturn(true);
        $this->isProductVariant()->shouldReturn(false);

        $this->shouldNotThrow(ProductSubscriptionException::class)->during('validate');
    }

    public function it_indicates_that_a_product_already_subscribed_cannot_be_subscribed_again(): void
    {
        $connectionStatus = new ConnectionStatus(true, true, true, 42);
        $this->beConstructedWith($connectionStatus, true, true, true, false);

        $this->isSubscribed()->shouldReturn(true);

        $this->shouldThrow(ProductSubscriptionException::alreadySubscribedProduct())->during('validate');
    }

    public function it_indicates_that_a_product_without_family_cannot_be_subscribed(): void
    {
        $connectionStatus = new ConnectionStatus(true, true, true, 42);
        $this->beConstructedWith($connectionStatus, false, false, true, false);

        $this->hasFamily()->shouldReturn(false);

        $this->shouldThrow(ProductSubscriptionException::familyRequired())->during('validate');
    }

    public function it_indicates_that_a_product_cannot_be_subscribed_if_there_is_no_filled_identifiers_mapping(): void
    {
        $connectionStatus = new ConnectionStatus(true, true, true, 42);
        $this->beConstructedWith($connectionStatus, false, true, false, false);

        $this->isMappingFilled()->shouldReturn(false);

        $this->shouldThrow(ProductSubscriptionException::invalidIdentifiersMapping())->during('validate');
    }

    public function it_indicates_that_a_variant_product_cannot_be_subscribed(): void
    {
        $connectionStatus = new ConnectionStatus(true, true, true, 42);
        $this->beConstructedWith($connectionStatus, false, true, true, true);

        $this->isProductVariant()->shouldReturn(true);

        $this->shouldThrow(ProductSubscriptionException::variantProduct())->during('validate');
    }

    public function it_indicates_that_a_product_cannot_be_subscribed_if_connection_is_not_active(): void
    {
        $connectionStatus = new ConnectionStatus(false, true, true, 42);
        $this->beConstructedWith($connectionStatus, false, true, true, false);

        $this->getConnectionStatus()->shouldReturn($connectionStatus);

        $this->shouldThrow(ProductSubscriptionException::inactiveConnection())->during('validate');
    }
}
