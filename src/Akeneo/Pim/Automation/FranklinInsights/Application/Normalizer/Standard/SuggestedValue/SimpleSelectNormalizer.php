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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Normalizer\Standard\SuggestedValue;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedValue;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;

/**
 * Normalizes a suggested value to Akeneo standard format for simple-select attribute type.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class SimpleSelectNormalizer
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
        $optionCode = $suggestedValue->value();

        if (!$this->optionExistsForAttribute($attributeCode, $optionCode)) {
            return [];
        }

        return [
            $attributeCode => [[
                'scope' => null,
                'locale' => null,
                'data' => $optionCode,
            ]],
        ];
    }

    /**
     * Checks that an option exists for a given attribute.
     *
     * @param string $attributeCode
     * @param string $optionCode
     *
     * @return bool
     */
    private function optionExistsForAttribute(string $attributeCode, string $optionCode): bool
    {
        return null !== $this->attributeOptionRepository->findOneByIdentifier(sprintf(
            '%s.%s',
            $attributeCode,
            $optionCode
        ));
    }
}
