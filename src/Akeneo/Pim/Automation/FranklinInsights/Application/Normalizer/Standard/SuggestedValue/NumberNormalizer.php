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
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

/**
 * Normalizes a suggested value to Akeneo standard format for a number attribute type.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class NumberNormalizer
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param SuggestedValue $suggestedValue
     *
     * @return array
     */
    public function normalize(SuggestedValue $suggestedValue): array
    {
        $attributeCode = $suggestedValue->pimAttributeCode();
        $value = $suggestedValue->value();

        if (!is_numeric($value)) {
            return [];
        }

        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        if (null === $attribute) {
            return [];
        }

        return [
            $attributeCode => [[
                'scope' => null,
                'locale' => null,
                'data' => $attribute->isDecimalsAllowed() ? (float) $value : (int) $value,
            ]],
        ];
    }
}
