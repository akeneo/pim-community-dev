<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\DocumentedExceptionInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;

/**
 * Exception thrown when performing an action on an unknown attribute.
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnknownAttributeException extends PropertyException implements DocumentedExceptionInterface
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

    public function getDocumentation(): array
    {
        return [
            [
                'message' => 'More information about attributes: %s %s.',
                'params' => [
                    [
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/what-is-an-attribute.html',
                        'title' => 'What is an attribute?',
                        'type' => 'href'
                    ],
                    [
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/manage-your-attributes.html',
                        'title' => 'Manage your attributes',
                        'type' => 'href'
                    ],
                ],
            ],
            [
                'message' => 'Please check your %s.',
                'params' => [
                    [
                        'route' => 'pim_enrich_attribute_index',
                        'params' => [],
                        'title' => 'Attributes settings',
                        'type' => 'route',
                    ],
                ],
            ],
        ];
    }
}
