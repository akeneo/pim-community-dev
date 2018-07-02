<?php

declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\Command;

use PimEnterprise\Component\SuggestData\Exception\DuplicatedMappingAttributeException;

class UpdateIdentifiersMapping
{
    private $identifiersMapping;

    public function __construct(array $identifiersMapping)
    {
        $this->validateIdentifiers($identifiersMapping);

        $this->identifiersMapping = array_map('trim', $identifiersMapping);
    }

    /**
     * @return array
     */
    public function getIdentifiersMapping(): array
    {
        return $this->identifiersMapping;
    }

    /**
     * @param array $identifiersMapping
     */
    private function validateIdentifiers(array $identifiersMapping): void
    {
        $expectedKeys = [
            'asin',
            'brand',
            'mpn',
            'upc',
        ];

        $mappingKeys = array_keys($identifiersMapping);
        sort($mappingKeys);

        if ($expectedKeys !== $mappingKeys) {
            throw new \InvalidArgumentException('Some identifiers mapping are missing or invalid');
        }

        $this->ensureAttributesAreMappedOnlyOneTime($identifiersMapping);
    }

    /**
     * @param array $identifiersMapping
     */
    private function ensureAttributesAreMappedOnlyOneTime(array $identifiersMapping): void
    {
        if (count($identifiersMapping) > count(array_unique($identifiersMapping))) {
            throw new DuplicatedMappingAttributeException('An attribute cannot be used more that 1 time');
        }
    }
}
