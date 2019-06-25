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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class SaveAttributesMappingByFamilyCommand
{
    /** @var string */
    private $familyCode;

    /** @var array */
    private $mapping = [];

    public function __construct(FamilyCode $familyCode, array $mapping)
    {
        $this->validate($mapping);
        $this->mapping = $mapping;

        $this->familyCode = $familyCode;
    }

    /**
     * @return FamilyCode
     */
    public function getFamilyCode(): FamilyCode
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
            throw AttributeMappingException::emptyAttributesMapping();
        }

        foreach ($mapping as $targetKey => $mappingRow) {
            if (!is_string($targetKey)) {
                throw InvalidMappingException::expectedTargetKey();
            }

            if (!array_key_exists('attribute', $mappingRow)) {
                throw InvalidMappingException::expectedKey($targetKey, 'attribute');
            }

            if (!array_key_exists('status', $mappingRow)) {
                throw InvalidMappingException::expectedKey($targetKey, 'status');
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
        $mapping = array_filter($mapping, function ($value) {
            return null !== $value['attribute'];
        });

        $pimAttributeCodes = array_map(function ($mapping) {
            return $mapping['attribute'];
        }, $mapping);

        if (count($pimAttributeCodes) !== count(array_unique($pimAttributeCodes))) {
            throw AttributeMappingException::duplicatedPimAttribute();
        }
    }
}
