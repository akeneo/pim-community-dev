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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\AttributesMapping;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\AttributesMapping;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributesMappingFake implements AttributesMappingApiInterface
{
    /** @var array */
    private $mappings = [];

    /**
     * @param string $familyCode
     *
     * @return AttributesMapping
     */
    public function fetchByFamily(string $familyCode): AttributesMapping
    {
        $filename = sprintf('attributes-mapping-family-%s.json', $familyCode);

        return new AttributesMapping(
            json_decode(
                file_get_contents(
                    sprintf(__DIR__ . '/../resources/%s', $filename)
                ),
                true
            )
        );
    }

    /**
     * @param string $familyCode
     *
     * @param array $mapping
     */
    public function update(string $familyCode, array $mapping): void
    {
        $this->mappings[$familyCode] = $mapping;
    }
}
