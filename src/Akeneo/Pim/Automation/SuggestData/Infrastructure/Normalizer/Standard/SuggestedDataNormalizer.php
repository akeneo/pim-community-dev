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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Normalizer\Standard;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\SuggestedData;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SuggestedDataNormalizer
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
     * Returns suggested values in standard format
     *
     * @param SuggestedData $suggestedData
     *
     * @return array
     */
    public function normalize(SuggestedData $suggestedData): array
    {
        $normalized = [];
        $suggestedValues = $suggestedData->getValues();
        $attributeTypes = $this->attributeRepository->getAttributeTypeByCodes(array_keys($suggestedValues));

        foreach ($suggestedValues as $attrCode => $value) {
            if (!isset($attributeTypes[$attrCode])) {
                throw new \InvalidArgumentException(sprintf('Attribute with code "%s" does not exist', $attrCode));
            }

            $normalized[$attrCode] = $this->normalizeValue($attributeTypes[$attrCode], $value);
        }

        return $normalized;
    }

    /**
     * @param string $attributeType
     * @param mixed $value
     *
     * @return array
     */
    private function normalizeValue(string $attributeType, $value): array
    {
        $data = null;

        switch ($attributeType) {
            case AttributeTypes::IDENTIFIER:
            case AttributeTypes::TEXT:
            case AttributeTypes::TEXTAREA:
            case AttributeTypes::NUMBER:
            case AttributeTypes::OPTION_SIMPLE_SELECT:
            case AttributeTypes::DATE:
                $data = $value;
                break;
            case AttributeTypes::BOOLEAN:
                if (in_array($value, ['1', '0'])) {
                    $data = (bool)$value;
                } elseif ('' !== $value) {
                    $data = $value;
                } else {
                    $data = null;
                }
                break;
            case AttributeTypes::OPTION_MULTI_SELECT:
                if (is_array($value)) {
                    $data = $value;
                } else {
                    $data = array_filter(explode(',', $value));
                    array_walk($data, 'trim');
                }
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf('Unsupported attribute type "%s"', $attributeType)
                );
        }

        return [
            [
                'scope' => null,
                'locale' => null,
                'data' => '' === $data ? null : $data,
            ],
        ];
    }
}
