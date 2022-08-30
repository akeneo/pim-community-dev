<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UnknownAssociatedProductException extends PropertyException
{
    public const EXCEPTION_BY_IDENTIFIER = 'Property "associations" expects a valid product identifier. The product does not exist, "%s" given.';
    public const EXCEPTION_BY_UUID = 'Property "associations" expects a valid product uuid. The product does not exist, "%s" given.';

    private string $identifier;

    private function __construct(string $message, string $identifier)
    {
        parent::__construct($message);

        $this->propertyName = 'associations';
        $this->identifier = $identifier;
    }

    public static function byIdentifier(string $identifier): self
    {
        $message = sprintf(self::EXCEPTION_BY_IDENTIFIER, $identifier);

        return new self($message, $identifier);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
