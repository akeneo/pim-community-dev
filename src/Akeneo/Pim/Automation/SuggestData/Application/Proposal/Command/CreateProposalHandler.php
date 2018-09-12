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
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Service\CreateProposalInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProposalAuthor;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalHandler
{
    /** @var SuggestedDataNormalizer */
    private $suggestedDataNormalizer;

    /** @var CreateProposalInterface */
    private $createProposal;

    /**
     * @param SuggestedDataNormalizer $suggestedDataNormalizer
     * @param CreateProposalInterface $createProposal
     */
    public function __construct(
        SuggestedDataNormalizer $suggestedDataNormalizer,
        CreateProposalInterface $createProposal
    ) {
        $this->suggestedDataNormalizer = $suggestedDataNormalizer;
        $this->createProposal = $createProposal;
    }

    /**
     * @param CreateProposalCommand $command
     */
    public function handle(CreateProposalCommand $command): void
    {
        $product = $command->getProductSubscription()->getProduct();
        if (0 === count($product->getCategoryCodes())) {
            // TODO APAI-244: handle error
            return;
        }

        $suggestedValues = $this->getSuggestedValues(
            $command->getProductSubscription()->getSuggestedData(),
            $product->getFamily()
        );

        if (empty($suggestedValues)) {
            // TODO APAI-244: handle error
            return;
        }

        $this->createProposal->fromSuggestedData($product, $suggestedValues, ProposalAuthor::USERNAME);
        // TODO APAI-240: empty suggested data from subscription
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
