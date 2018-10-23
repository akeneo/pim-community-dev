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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\AttributeOptionsMapping;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\AttributeOptionsMapping;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class AttributeOptionsMappingFake implements AttributeOptionsMappingInterface
{
    /** @var array */
    private $mappings = [];

    /**
     * {@inheritdoc}
     */
    public function fetchByFamilyAndAttribute(string $familyCode, string $franklinAttributeId): AttributeOptionsMapping
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

        return new AttributeOptionsMapping(
            json_decode(file_get_contents($filepath), true)
        );
    }
}
