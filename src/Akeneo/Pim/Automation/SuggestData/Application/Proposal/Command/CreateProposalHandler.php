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

namespace Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Normalizer\Standard\SuggestedDataNormalizer;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProposalAuthor;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalHandler
{
    /** @var SuggestedDataNormalizer */
    private $suggestedDataNormalizer;

    /** @var ProposalUpsertInterface */
    private $proposalUpsert;

    /** @var ProductSubscriptionRepositoryInterface */
    private $productSubscriptionRepository;

    /**
     * @param SuggestedDataNormalizer $suggestedDataNormalizer
     * @param ProposalUpsertInterface $proposalUpsert
     * @param ProductSubscriptionRepositoryInterface $productSubscriptionRepository
     */
    public function __construct(
        SuggestedDataNormalizer $suggestedDataNormalizer,
        ProposalUpsertInterface $proposalUpsert,
        ProductSubscriptionRepositoryInterface $productSubscriptionRepository
    ) {
        $this->suggestedDataNormalizer = $suggestedDataNormalizer;
        $this->proposalUpsert = $proposalUpsert;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
    }

    /**
     * @param CreateProposalCommand $command
     */
    public function handle(CreateProposalCommand $command): void
    {
        $subscription = $command->getProductSubscription();
        $product = $subscription->getProduct();
        if (0 === count($product->getCategoryCodes())) {
            // TODO APAI-244: handle error
            return;
        }

        $suggestedValues = $this->getSuggestedValues(
            $subscription->getSuggestedData(),
            $product->getFamily()
        );

        if (empty($suggestedValues)) {
            // TODO APAI-244: handle error
            return;
        }

        $this->proposalUpsert->process($product, $suggestedValues, ProposalAuthor::USERNAME);

        $subscription->emptySuggestedData();
        $this->productSubscriptionRepository->save($subscription);
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
            $normalizedData = $this->suggestedDataNormalizer->normalize($suggestedData);
        } catch (\InvalidArgumentException $e) {
            // TODO APAI-244: handle error
            return [];
        }

        $availableAttributeCodes = $family->getAttributeCodes();

        return array_filter(
            $normalizedData,
            function ($attributeCode) use ($availableAttributeCodes) {
                return in_array($attributeCode, $availableAttributeCodes);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
