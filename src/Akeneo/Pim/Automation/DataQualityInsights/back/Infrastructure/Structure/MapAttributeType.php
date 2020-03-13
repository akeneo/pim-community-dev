<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Structure\Component\AttributeTypes as PimStructureAttributeType;

class MapAttributeType
{
    /** @var AttributeType[] */
    private $fromPimStructureToDQIMapping;

    /** @var string[] */
    private $fromDQIToPimStructureMapping;

    public function __construct()
    {
        $this->fromPimStructureToDQIMapping = [
            PimStructureAttributeType::TEXT => AttributeType::text(),
            PimStructureAttributeType::TEXTAREA => AttributeType::textarea(),
        ];

        $this->fromDQIToPimStructureMapping = [
            AttributeType::TEXT => PimStructureAttributeType::TEXT,
            AttributeType::TEXTAREA => PimStructureAttributeType::TEXTAREA,
        ];
    }

    public function fromPimStructure(string $type): AttributeType
    {
        if (!isset($this->fromPimStructureToDQIMapping[$type])) {
            throw new \InvalidArgumentException(sprintf('Attribute type "%s" is not supported.', $type));
        }

        return $this->fromPimStructureToDQIMapping[$type];
    }

    public function fromStringToPimStructure(string $type): string
    {
        if (!isset($this->fromDQIToPimStructureMapping[$type])) {
            throw new \InvalidArgumentException(sprintf('Attribute type "%s" is not supported.', $type));
        }

        return $this->fromDQIToPimStructureMapping[$type];
    }

    public function fromArrayStringToPimStructure(array $types): array
    {
        return array_map(function (string $type) {
            return $this->fromStringToPimStructure($type);
        }, $types);
    }
}
