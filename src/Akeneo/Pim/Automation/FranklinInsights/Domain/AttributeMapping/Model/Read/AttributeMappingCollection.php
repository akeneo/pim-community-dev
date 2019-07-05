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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeMappingCollection implements \IteratorAggregate
{
    private $attributes;

    public function __construct()
    {
        $this->attributes = [];
    }

    public function addAttribute(AttributeMapping $attribute): self
    {
        $this->attributes[$attribute->getTargetAttributeCode()] = $attribute;

        $this->sortByStatus();

        return $this;
    }

    public function hasPimAttribute(AttributeCode $attributeCode): bool
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->getPimAttributeCode() === (string) $attributeCode) {
                return true;
            }
        }

        return false;
    }

    public function getPendingAttributesFranklinLabels(): array
    {
        $attributes = array_filter($this->attributes, function (AttributeMapping $attributeMapping) {
            return AttributeMappingStatus::ATTRIBUTE_PENDING === $attributeMapping->getStatus();
        });

        return array_map(function (AttributeMapping $attributeMapping) {
            return $attributeMapping->getTargetAttributeLabel();
        }, array_values($attributes));
    }

    public function isEmpty(): bool
    {
        return 0 === count($this->attributes);
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->attributes);
    }

    public function applyExactMatchOnAttribute(string $targetAttributeCode, ?string $pimAttributeCode)
    {
        $attributeMapping = $this->attributes[$targetAttributeCode];

        $newAttributeMapping = new AttributeMapping(
            $attributeMapping->getTargetAttributeCode(),
            $attributeMapping->getTargetAttributeLabel(),
            $attributeMapping->getTargetAttributeType(),
            $pimAttributeCode,
            $pimAttributeCode === null ? AttributeMappingStatus::ATTRIBUTE_PENDING : AttributeMappingStatus::ATTRIBUTE_ACTIVE,
            $attributeMapping->getSummary()
        );

        $this->attributes[$targetAttributeCode] = $newAttributeMapping;

        $this->sortByStatus();
    }

    public function normalize()
    {
        $mapping = [];
        foreach ($this as $attributeMapping) {
            $mapping[$attributeMapping->getTargetAttributeCode()] = [
                'franklinAttribute' => [
                    'type' => $attributeMapping->getTargetAttributeType(),
                ],
                'attribute' => $attributeMapping->getPimAttributeCode(),
                'status' => $attributeMapping->getStatus(),
            ];
        }

        return $mapping;
    }

    private function sortByStatus(): void
    {
        uasort($this->attributes, function (AttributeMapping $a, AttributeMapping $b) {
            $inactive = $a->isInactive() <=> $b->isInactive();
            if ($inactive !== 0) {
                return $inactive;
            }

            $isMapped = $a->isMapped() <=> $b->isMapped();
            if ($isMapped !== 0) {
                return $isMapped;
            }

            return $a->getTargetAttributeLabel() <=> $b->getTargetAttributeLabel();
        });
    }
}
