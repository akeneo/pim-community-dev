<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\IdentifiableDomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductDomainErrorIdentifiers;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UnknownFamilyException extends InvalidPropertyException implements IdentifiableDomainErrorInterface
{
    public static function unknownFamily($propertyName, $propertyValue, $className)
    {
        $message = 'Property "%s" expects a valid family code. The family does not exist, "%s" given.';

        return new static(
            $propertyName,
            $propertyValue,
            $className,
            sprintf($message, $propertyName, $propertyValue),
            self::VALID_ENTITY_CODE_EXPECTED_CODE
        );
    }

    public function getErrorIdentifier(): string
    {
        return ProductDomainErrorIdentifiers::UNKNOWN_FAMILY;
    }
}
