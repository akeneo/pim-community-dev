<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\Attributes;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributesInterface
{
    /**
     * @param array<int> $attributesIds
     * @return array<string>
     */
    public function getCodesByIds(array $attributesIds): array;

    /**
     * @param array<string> $attributesCodes
     * @return array<int>
     */
    public function getIdsByCodes(array $attributesCodes): array;
}
