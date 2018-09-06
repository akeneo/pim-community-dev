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

use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Normalizer\Standard\SuggestedDataNormalizer;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalHandler
{
    /** @var SuggestedDataNormalizer */
    private $suggestedDataNormalizer;

    /**
     * @param SuggestedDataNormalizer $suggestedDataNormalizer
     */
    public function __construct(SuggestedDataNormalizer $suggestedDataNormalizer)
    {
        $this->suggestedDataNormalizer = $suggestedDataNormalizer;
    }

    /**
     * @param CreateProposalCommand $command
     */
    public function handle(CreateProposalCommand $command): void
    {
        $product = $command->getProductSubscription()->getProduct();
        $suggestedValues = $this->getSuggestedValues(
            $command->getProductSubscription()->getSuggestedData(),
            $product->getFamily()
        );

        if (empty($suggestedValues)) {
            // TODO APAI-244: handle error?
            return;
        }

        // TODO APAI-249: create proposal from standard values
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
        $normalizedData = $this->suggestedDataNormalizer->normalize($suggestedData);
        $availableAttributes = $family->getAttributeCodes();

        return array_filter(
            $normalizedData,
            function ($attributeCode) use ($availableAttributes) {
                return in_array($attributeCode, $availableAttributes);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
