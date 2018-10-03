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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Exception;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 */
class AttributeMappingException extends \Exception
{
    /** @var string */
    private const CONSTRAINT_KEY = 'akeneo_suggest_data.entity.attributes_mapping.constraint.%s';
    /** @var array */
    private $messageParams;

    /**
     * @param string $message
     * @param array $messageParams
     */
    public function __construct(string $message, array $messageParams = [])
    {
        parent::__construct($message);

        $this->messageParams = $messageParams;
    }

    /**
     * Thrown exception when target attribute type and PIM attribute type are not compatible.
     *
     * @param string $targetAttributeType
     * @param string $pimAttributeType
     *
     * @return AttributeMappingException
     */
    public static function incompatibleAttributeTypeMapping(string $targetAttributeType, string $pimAttributeType): self
    {
        $message = sprintf(static::CONSTRAINT_KEY, 'invalid_attribute_type_mapping');

        return new static(
            $message,
            ['targetType' => $targetAttributeType, 'pimType' => $pimAttributeType]
        );
    }

    /**
     * @return array
     */
    public function getMessageParams(): array
    {
        return $this->messageParams;
    }
}
