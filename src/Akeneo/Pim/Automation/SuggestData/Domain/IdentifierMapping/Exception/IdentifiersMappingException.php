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

namespace Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Exception;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class IdentifiersMappingException extends \Exception
{
    private const IDENTIFIER_MAPPING_CONSTRAINT_KEY = 'akeneo_suggest_data.entity.identifier_mapping.constraint.%s';

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
     * @param string $className
     *
     * @return IdentifiersMappingException
     */
    public static function askFranklinServerIsDown(string $className): self
    {
        $message = sprintf(static::IDENTIFIER_MAPPING_CONSTRAINT_KEY, 'ask_franklin_down');

        return new static($className, $message);
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
