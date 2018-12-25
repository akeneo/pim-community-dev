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

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class SaveAttributesMappingByFamilyCommand
{
    /** @var string */
    private $familyCode;

    /** @var AttributeMapping[] */
    private $attributesMapping = [];

    /**
     * @param string $familyCode
     * @param array $mapping
     *
     * @throws InvalidMappingException
     */
    public function __construct(string $familyCode, array $mapping)
    {
        $this->attributesMapping = [];
        $this->validate($mapping);

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
     * @return AttributeMapping[]
     */
    public function getAttributesMapping(): array
    {
        return $this->attributesMapping;
    }

    /**
     * Validates data and creates AttributesMapping.
     *
     * Format is:
     * [
     *      "color" => [
     *          "franklinAttribute" => [
     *              "label" => "Color",
     *              "type" => "multiselect"
     *          ],
     *          "attribute" => "tshirt_style",
     *      ]
     * ]
     *
     * @param array $mapping
     *
     * @throws InvalidMappingException
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

            $this->attributesMapping[] = new AttributeMapping(
                $targetKey,
                $mappingRow['franklinAttribute']['type'],
                $mappingRow['attribute']
            );
        }
    }
}
