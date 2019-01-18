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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\AttributeMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\InvalidMappingException;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class SaveAttributesMappingByFamilyCommand
{
    /** @var string */
    private $familyCode;

    /** @var array */
    private $mapping = [];

    /**
     * @param string $familyCode
     * @param array $mapping
     *
     * @throws AttributeMappingException
     * @throws InvalidMappingException
     */
    public function __construct(string $familyCode, array $mapping)
    {
        $this->validate($mapping);
        $this->mapping = $mapping;

        $this->familyCode = $familyCode;
    }

    /**
     * @return string
     */
    public function getFamilyCode(): string
    {
        return $this->familyCode;
    }

    /**
     * @return array
     */
    public function getMapping(): array
    {
        return $this->mapping;
    }

    /**
     * Validates data and creates AttributesMapping.
     *
     * Format is:
     * [
     *      "<franklin_attr_code>" => [
     *          "franklinAttribute" => [
     *              "label" => "<franklin_label>",
     *              "type" => "<franklin_type>"
     *          ],
     *          "attribute" => "<pim_attr_code>",
     *      ]
     * ]
     *
     * @param array $mapping
     *
     * @throws InvalidMappingException
     * @throws AttributeMappingException
     */
    private function validate(array $mapping): void
    {
        if (empty($mapping)) {
            throw InvalidMappingException::emptyMapping();
        }

        foreach ($mapping as $targetKey => $mappingRow) {
            if (!is_string($targetKey)) {
                throw InvalidMappingException::expectedTargetKey();
            }

            if (!array_key_exists('attribute', $mappingRow)) {
                throw InvalidMappingException::expectedKey($targetKey, 'attribute');
            }

            if (empty($mappingRow['attribute'])) {
                $mapping[$targetKey]['attribute'] = null;
            }
        }

        $this->validatePimAttributesAreNotUsedTwiceInTheSameMapping($mapping);
    }

    /**
     * @param array $mapping
     *
     * @throws AttributeMappingException
     */
    private function validatePimAttributesAreNotUsedTwiceInTheSameMapping(array $mapping): void
    {
        if (count($mapping) !== count(array_unique($mapping, SORT_REGULAR))) {
            throw AttributeMappingException::duplicatedPimAttribute();
        }
    }
}
