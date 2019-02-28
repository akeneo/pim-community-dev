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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Factory;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard\SuggestedDataNormalizer;
use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Factory\ProposalSuggestedDataFactory;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProposalSuggestedDataFactorySpec extends ObjectBehavior
{
    public function let(SuggestedDataNormalizer $normalizer): void
    {
        $this->beConstructedWith($normalizer);
    }

    public function it_is_a_suggested_data_factory(): void
    {
        $this->shouldHaveType(ProposalSuggestedDataFactory::class);
    }

    public function it_returns_null_if_suggested_data_cannot_be_normalized($normalizer): void
    {
        $subscription = new ProductSubscription(42, new SubscriptionId('fake-id'), [['pimAttributeCode' => 'foo', 'value' => 'bar']]);
        $normalizer->normalize($subscription->getSuggestedData())->willReturn([]);

        $this->fromSubscription($subscription)->shouldReturn(null);
    }

    public function it_returns_null_if_suggested_values_are_empty($normalizer): void
    {
        $subscription = new ProductSubscription(42, new SubscriptionId('fake-id'), []);
        $normalizer->normalize($subscription->getSuggestedData())->willReturn([]);

        $this->fromSubscription($subscription)->shouldReturn(null);
    }
}
