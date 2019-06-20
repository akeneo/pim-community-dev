<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FranklinAttributeType
{
    const AVAILABLE_TYPES = [
        self::BOOLEAN_TYPE,
        self::METRIC_TYPE,
        self::MULTI_SELECT_TYPE,
        self::SELECT_TYPE,
        self::NUMBER_TYPE,
        self::TEXT_TYPE,
    ];

    const BOOLEAN_TYPE = 'boolean';
    const METRIC_TYPE = 'metric';
    const MULTI_SELECT_TYPE = 'multiselect';
    const SELECT_TYPE = 'select';
    const NUMBER_TYPE = 'number';
    const TEXT_TYPE = 'text';

    /** @var string */
    private $type;

    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        if (empty($type)) {
            throw new \InvalidArgumentException('Franklin attribute type cannot be an empty string');
        }
        if (!in_array($type, self::AVAILABLE_TYPES)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Franklin attribute type is not valid "%s". Allowed values [%s]',
                    $type,
                    implode(', ', self::AVAILABLE_TYPES)
                )
            );
        }

        $this->type = $type;
    }

    public function convertToPimAttributeType(): AttributeType
    {
        if (self::METRIC_TYPE === $this->type) {
            return new AttributeType(AttributeTypes::TEXT);
        }

        return new AttributeType(
            array_search($this->type, AttributeMapping::AUTHORIZED_ATTRIBUTE_TYPE_MAPPINGS)
        );
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->type;
    }
}
