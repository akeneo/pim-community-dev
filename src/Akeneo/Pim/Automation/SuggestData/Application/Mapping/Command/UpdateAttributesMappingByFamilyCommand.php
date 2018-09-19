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

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributesMapping;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class UpdateAttributesMappingByFamilyCommand
{
    /** @var string */
    private $familyCode;

    /** @var AttributesMapping */
    private $attributesMapping;

    /**
     * @param string $familyCode
     * @param array $mapping
     */
    public function __construct(string $familyCode, array $mapping)
    {
        $this->attributesMapping = new AttributesMapping();
        $this->validate($mapping);

        $this->familyCode = $familyCode;
    }

    /**
     * @return string
     */
    public function getFamilyCode()
    {
        return $this->familyCode;
    }

    /**
     * @return AttributesMapping
     */
    public function getAttributesMapping()
    {
        return $this->attributesMapping;
    }

    /**
     *
     * Validates data and creates AttributesMapping
     *
     * Format is:
     * [
     *     [
     *          "color" => [
     *              "pim_ai_attribute" => [
     *                  "label" => "Color",
     *                  "type" => "multiselect"
     *              ],
     *              "attribute" => "tshirt_style",
     *              "status" => 1
     *          ]
     * ]
     *
     * @param array $mapping
     */
    private function validate(array $mapping): void
    {
        foreach ($mapping as $targetKey => $mappingRow) {
            if (!is_string($targetKey)) {
                throw new \InvalidArgumentException('Target key expected');
            }

            if (!array_key_exists('attribute', $mappingRow)) {
                throw new \InvalidArgumentException('Missing PIM attribute');
            }

            if (!array_key_exists('status', $mappingRow)) {
                throw new \InvalidArgumentException('Missing status key');
            }

            $this->attributesMapping->addAttributeMapping(
                new AttributeMapping($targetKey, $mappingRow['status'], $mappingRow['attribute'])
            );
        }
    }
}
