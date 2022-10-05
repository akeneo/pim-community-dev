<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InvalidEntityCodeException extends InvalidPropertyException implements
    DomainErrorInterface,
    TemplatedErrorMessageInterface
{
    private TemplatedErrorMessage $templatedErrorMessage;

    public function __construct(
        string $propertyName,
        string $key,
        string $because,
        string $className,
        string $propertyValue
    ) {
        $this->templatedErrorMessage = new TemplatedErrorMessage(
            'Property "{property_name}" expects a valid {key}. {because}, "{property_value}" given.',
            [
                'property_name' => $propertyName,
                'key' => $key,
                'because' => $because,
                'property_value' => $propertyValue,
            ]
        );

        parent::__construct(
            $propertyName,
            $propertyValue,
            $className,
            (string) $this->templatedErrorMessage,
            self::VALID_ENTITY_CODE_EXPECTED_CODE
        );
    }

    public function getTemplatedErrorMessage(): TemplatedErrorMessage
    {
        return $this->templatedErrorMessage;
    }
}
