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

namespace Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command;

use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;

/**
 * Command that holds and validates the raw values of the identifiers mapping.
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class UpdateIdentifiersMappingCommand
{
    /** @var array */
    private $identifiersMapping;

    /**
     * @param array $identifiersMapping
     */
    public function __construct(array $identifiersMapping)
    {
        $this->validateIdentifiers($identifiersMapping);

        $this->identifiersMapping = $identifiersMapping;
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
     *
     * @throws InvalidMappingException
     */
    private function validateIdentifiers(array $identifiersMapping): void
    {
        $expectedKeys = IdentifiersMapping::PIM_AI_IDENTIFIERS;

        $mappingKeys = array_keys($identifiersMapping);
        sort($mappingKeys);
        sort($expectedKeys);

        if ($expectedKeys !== $mappingKeys) {
            throw InvalidMappingException::missingOrInvalidIdentifiersInMapping(
                $expectedKeys,
                $mappingKeys,
                static::class
            );
        }

        $this->ensureAttributesAreMappedOnlyOneTime($identifiersMapping);
    }

    /**
     * @param array $identifiersMapping
     *
     * @throws InvalidMappingException
     */
    private function ensureAttributesAreMappedOnlyOneTime(array $identifiersMapping): void
    {
        $filteredMapping = array_filter($identifiersMapping, function ($attributeCode) {
            return null !== $attributeCode;
        });

        $values = array_count_values($filteredMapping);
        foreach ($values as $attributeCode => $frequency) {
            if ($frequency > 1) {
                throw InvalidMappingException::duplicateAttributeCode(
                    $frequency,
                    $attributeCode,
                    static::class,
                    array_search($attributeCode, $filteredMapping)
                );
            }
        }
    }
}
