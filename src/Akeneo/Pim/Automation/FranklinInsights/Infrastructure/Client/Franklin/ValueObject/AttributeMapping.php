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
     * @return null|string
     */
    public function getTargetAttributeLabel(): ?string
    {
        if (isset($this->attributeData['from']['label'])) {
            return current($this->attributeData['from']['label']);
        }

        return null;
    }

    /**
     * @return null|string
     */
    public function getPimAttributeCode(): ?string
    {
        if (isset($this->attributeData['to']['id'])) {
            return (string) $this->attributeData['to']['id'];
        }

        return null;
    }

    /**
     * @return null|string
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
     * @param array $attribute
     */
    private function validateAttribute(array $attribute): void
    {
        $this->checkMandatoryKeys($attribute);
        $this->validateStatus($attribute['status']);

        if (!isset($attribute['from']['id'])) {
            throw new \InvalidArgumentException('Missing "id" key in target attribute code data');
        }

        if (!empty($attribute['to']) && !isset($attribute['to']['id'])) {
            throw new \InvalidArgumentException('Missing "id" key in pim attribute code data');
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
}
