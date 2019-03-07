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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Normalizer\Standard\SuggestedValue;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedValue;

/**
 * Normalizes a suggested value to Akeneo standard format for multi-select attribute type.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class MultiSelectNormalizer
{
    /** @var AttributeOptionRepositoryInterface */
    private $attributeOptionRepository;

    /**
     * @param AttributeOptionRepositoryInterface $attributeOptionRepository
     */
    public function __construct(AttributeOptionRepositoryInterface $attributeOptionRepository)
    {
        $this->attributeOptionRepository = $attributeOptionRepository;
    }

    /**
     * @param SuggestedValue $suggestedValue
     *
     * @return array
     */
    public function normalize(SuggestedValue $suggestedValue): array
    {
        $attributeCode = $suggestedValue->pimAttributeCode();
        $providedOptionCodes = $suggestedValue->value();

        if (is_string($providedOptionCodes)) {
            $providedOptionCodes = explode(',', $providedOptionCodes);
        }

        $existingOptionCodes = $this->attributeOptionRepository->findCodesByIdentifiers(
            $attributeCode,
            $providedOptionCodes
        );

        if (empty($existingOptionCodes)) {
            return [];
        }

        return [
            $attributeCode => [[
                'scope' => null,
                'locale' => null,
                'data' => $existingOptionCodes,
            ]],
        ];
    }
}
