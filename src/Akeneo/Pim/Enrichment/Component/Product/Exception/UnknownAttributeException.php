<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;

/**
 * Exception thrown when performing an action on an unknown attribute.
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnknownAttributeException extends PropertyException
{
    public function __construct(string $attributeName, string $message = '', int $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->propertyName = $attributeName;
    }

    public static function unknownAttribute(string $attributeCode, \Exception $previous = null): self
    {
        return new static(
            $attributeCode,
            sprintf(
                'Attribute "%s" does not exist.',
                $attributeCode
            ),
            0,
            $previous
        );
    }
}
