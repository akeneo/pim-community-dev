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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception;

/**
 * Exception thrown when trying to save a not valid mapping.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
final class InvalidMappingException extends \Exception
{
    /** @var string */
    private const ATTRIBUTE_MAPPING_CONSTRAINT_KEY = 'akeneo_franklin_insights.entity.attributes_mapping.constraint.%s';

    /** @var string */
    private $className;

    /** @var string */
    private $path;

    /** @var array */
    private $messageParams;

    /**
     * @param string $className
     * @param string $message
     * @param array $messageParams
     * @param string|null $path
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct(
        ?string $className,
        string $message = '',
        array $messageParams = [],
        string $path = null,
        int $code = 400,
        ?\Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->className = $className;
        $this->messageParams = $messageParams;
        $this->path = $path;
    }

    /**
     * @return InvalidMappingException
     */
    public static function expectedTargetKey()
    {
        $message = sprintf(static::ATTRIBUTE_MAPPING_CONSTRAINT_KEY, 'missing_target_key');

        return new self(null, $message, [], '', 400);
    }

    /**
     * @return InvalidMappingException
     */
    public static function emptyMapping()
    {
        $message = sprintf(static::ATTRIBUTE_MAPPING_CONSTRAINT_KEY, 'empty_mapping');

        return new self(null, $message, [], '', 400);
    }

    /**
     * @param mixed $targetKey
     * @param mixed $expectedKey
     *
     * @return InvalidMappingException
     */
    public static function expectedKey($targetKey, $expectedKey)
    {
        $message = sprintf(static::ATTRIBUTE_MAPPING_CONSTRAINT_KEY, 'missing_attribute_key');

        return new self(null, $message, ['expectedKey' => $expectedKey, 'targetKey' => $targetKey], '', 400);
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return array
     */
    public function getMessageParams(): array
    {
        return $this->messageParams;
    }

    /**
     * @return null|string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}
