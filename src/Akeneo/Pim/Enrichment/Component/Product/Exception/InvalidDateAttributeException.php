<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InvalidDateAttributeException extends InvalidPropertyException implements
    DomainErrorInterface,
    TemplatedErrorMessageInterface
{
    private TemplatedErrorMessage $templatedMessage;

    public function __construct(string $propertyName, string $propertyValue, string $className)
    {
        $this->templatedMessage = new TemplatedErrorMessage(
            'The {attribute_code} attribute requires a valid date. Please use the following format {date_format} for dates.',
            ['attribute_code' => $propertyName, 'date_format' => $propertyValue]
        );
        parent::__construct(
            $propertyName,
            $propertyValue,
            $className,
            (string) $this->templatedMessage,
            self::DATE_EXPECTED_CODE
        );
    }

    public function getTemplatedErrorMessage(): TemplatedErrorMessage
    {
        return $this->templatedMessage;
    }
}
