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

namespace Akeneo\Pim\Automation\SuggestData\Application\Proposal\Factory;

use Akeneo\Pim\Automation\SuggestData\Application\Normalizer\Standard\SuggestedDataNormalizer;
use Akeneo\Pim\Automation\SuggestData\Domain\Proposal\ValueObject\ProposalSuggestedData;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProposalSuggestedDataFactory
{
    /** @var SuggestedDataNormalizer */
    private $normalizer;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param SuggestedDataNormalizer $normalizer
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(SuggestedDataNormalizer $normalizer, ProductRepositoryInterface $productRepository)
    {
        $this->normalizer = $normalizer;
        $this->productRepository = $productRepository;
    }

    /**
     * @param ProductSubscription $subscription
     *
     * @return ProposalSuggestedData|null
     */
    public function fromSubscription(ProductSubscription $subscription): ?ProposalSuggestedData
    {
        $product = $this->productRepository->find($subscription->getProductId());

        // TODO APAI-244: remove this test
        if (0 === count($product->getCategoryCodes())) {
            return null;
        }

        $suggestedValues = $this->getSuggestedValues(
            $subscription->getSuggestedData(),
            $product->getFamily()
        );

        if (empty($suggestedValues)) {
            // TODO APAI-244: handle error
            return null;
        }

        return new ProposalSuggestedData(
            $suggestedValues,
            $product
        );
    }

    /**
     * @param SuggestedData $suggestedData
     * @param FamilyInterface $family
     *
     * @return array
     */
    private function getSuggestedValues(SuggestedData $suggestedData, FamilyInterface $family): array
    {
        try {
            $normalizedData = $this->normalizer->normalize($suggestedData);
        } catch (\InvalidArgumentException $e) {
            // TODO APAI-244: handle error
            return [];
        }

        $availableAttributeCodes = $family->getAttributeCodes();

        // TODO APAI-244: add comment
        return array_filter(
            $normalizedData,
            function ($attributeCode) use ($availableAttributeCodes) {
                return in_array($attributeCode, $availableAttributeCodes);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
