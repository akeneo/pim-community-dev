<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UndefinedAttributeException extends \RuntimeException
{
    private function __construct(private readonly string $attributeCode)
    {
        parent::__construct(\sprintf('The %s attribute does not exist', $this->attributeCode));
    }

    public static function withAttributeCode(string $attributeCode): self
    {
        return new self($attributeCode);
    }
}
