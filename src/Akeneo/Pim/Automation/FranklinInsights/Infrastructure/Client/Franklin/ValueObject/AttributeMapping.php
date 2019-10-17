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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping as AttributeMappingModel;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeMapping
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    private $attributeData;

    /**
     * @param array $attributeData
     */
    public function __construct(array $attributeData)
    {
        $this->validateAttribute($attributeData);

        $this->attributeData = $attributeData;
    }

    /**
     * @return string
     */
    public function getTargetAttributeCode(): string
    {
        return (string) $this->attributeData['from']['id'];
    }

    /**
     * @return string|null
     */
    public function getTargetAttributeLabel(): ?string
    {
        if (isset($this->attributeData['from']['label'])) {
            return current($this->attributeData['from']['label']);
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getPimAttributeCode(): ?string
    {
        if (isset($this->attributeData['to']['id'])) {
            return (string) $this->attributeData['to']['id'];
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getTargetAttributeType(): ?string
    {
        return $this->attributeData['from']['type'];
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->attributeData['status'];
    }

    /**
     * @return array|null
     */
    public function getSummary(): ?array
    {
        if (isset($this->attributeData['summary'])) {
            return $this->attributeData['summary'];
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function getSuggestions(): array
    {
        return $this->attributeData['suggestions'] ?? [];
    }

    /**
     * @param array $attribute
     */
    private function validateAttribute(array $attribute): void
    {
        $this->checkMandatoryKeys($attribute);

        $this->validateAttributeType((string) $attribute['from']['type']);
        $this->validateStatus($attribute['status']);

        if (!isset($attribute['from']['id'])) {
            throw new \InvalidArgumentException('Missing "id" key in target attribute code data');
        }

        if (!empty($attribute['to']) && !isset($attribute['to']['id'])) {
            throw new \InvalidArgumentException('Missing "id" key in pim attribute code data');
        }

        if (isset($attribute['suggestions'])) {
            $this->validateSuggestions($attribute['suggestions']);
        }
    }

    /**
     * @param string $status
     */
    private function validateStatus(string $status): void
    {
        $allowedStatus = [
            self::STATUS_PENDING,
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
        ];

        if (!in_array($status, $allowedStatus)) {
            throw new \InvalidArgumentException(sprintf('The attribute status "%s" is invalid', $status));
        }
    }

    private function validateAttributeType(string $franklinAttributeType): void
    {
        if (!in_array($franklinAttributeType, AttributeMappingModel::AUTHORIZED_ATTRIBUTE_TYPE_MAPPINGS)) {
            throw new \InvalidArgumentException(sprintf('The franklin attribute type "%s" is not handled', $franklinAttributeType));
        }
    }

    /**
     * @param array $attribute
     */
    private function checkMandatoryKeys(array $attribute): void
    {
        $mandatoryKeys = [
            'from',
            'to',
            'status',
        ];

        foreach ($mandatoryKeys as $mandatoryKey) {
            if (!array_key_exists($mandatoryKey, $attribute)) {
                throw new \InvalidArgumentException(sprintf(
                    'Missing key "%s" in attribute',
                    $mandatoryKey
                ));
            }
        }
    }

    private function validateSuggestions($suggestions): void
    {
        if (!is_array($suggestions)) {
            throw new \InvalidArgumentException('The property "suggestions" is not an array');
        }

        foreach ($suggestions as $suggestion) {
            if (!is_string($suggestion)) {
                throw new \InvalidArgumentException('The property "suggestions" is malformed');
            }
        }
    }
}
