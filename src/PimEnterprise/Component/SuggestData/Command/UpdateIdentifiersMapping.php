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

namespace PimEnterprise\Component\SuggestData\Command;

use PimEnterprise\Component\SuggestData\Exception\DuplicatedMappingAttributeException;

/**
 * Command that holds and validates the raw values of the identifiers mapping
 */
class UpdateIdentifiersMapping
{
    private $identifiersMapping;

    /**
     * @param array $identifiersMapping
     */
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
