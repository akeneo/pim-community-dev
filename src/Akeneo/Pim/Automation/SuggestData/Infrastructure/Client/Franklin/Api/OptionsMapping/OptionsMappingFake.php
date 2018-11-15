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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\OptionsMapping;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\OptionsMapping;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class OptionsMappingFake implements OptionsMappingInterface
{
    /** @var array */
    private $mappings = [];

    /**
     * {@inheritdoc}
     */
    public function fetchByFamilyAndAttribute(string $familyCode, string $franklinAttributeId): OptionsMapping
    {
        $filename = sprintf('get_family_%s_attribute_%s.json', $familyCode, $franklinAttributeId);
        $filepath = sprintf(
            '%s/%s',
            realpath(__DIR__ . '/../../../../../tests/back/Resources/fake/franklin-api/attribute-options-mapping'),
            $filename
        );
        if (!file_exists($filepath)) {
            throw new \Exception(sprintf('File "%s" does not exist', $filepath));
        }

        return new OptionsMapping(
            json_decode(file_get_contents($filepath), true)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $familyCode, string $franklinAttributeId, array $attributeOptionsMapping): void
    {
        $this->mappings[$familyCode][$franklinAttributeId] = $attributeOptionsMapping;
    }

    /**
     * @param string $familyCode
     * @param string $franklinAttributeId
     *
     * @return array|null
     */
    public function getMappingByFamilyAndFranklinAttributeId(string $familyCode, string $franklinAttributeId): ?array
    {
        return $this->mappings[$familyCode][$franklinAttributeId] ?? $this->mappings[$familyCode][$franklinAttributeId];
    }

    /**
     * {@inheritdoc}
     */
    public function setToken(string $token): void
    {
    }
}
