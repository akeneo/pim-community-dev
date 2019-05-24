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

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FranklinAttributeType
{
    /** @var string */
    private $type;

    const AVAILABLE_TYPES = [
        'boolean',
        'metric',
        'multiselect',
        'number',
        'select',
        'text',
    ];

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
