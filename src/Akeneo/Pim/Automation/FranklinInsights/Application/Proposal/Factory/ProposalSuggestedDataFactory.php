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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Factory;

use Akeneo\Pim\Automation\FranklinInsights\Application\Normalizer\Standard\SuggestedDataNormalizer;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalSuggestedData;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProposalSuggestedDataFactory
{
    /** @var SuggestedDataNormalizer */
    private $normalizer;

    /**
     * @param SuggestedDataNormalizer $normalizer
     */
    public function __construct(SuggestedDataNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @param ProductSubscription $subscription
     *
     * @return ProposalSuggestedData|null
     */
    public function fromSubscription(ProductSubscription $subscription): ?ProposalSuggestedData
    {
        $suggestedValues = $this->normalizer->normalize($subscription->getSuggestedData());
        if (empty($suggestedValues)) {
            return null;
        }

        return new ProposalSuggestedData($subscription->getProductId(), $suggestedValues);
    }
}
