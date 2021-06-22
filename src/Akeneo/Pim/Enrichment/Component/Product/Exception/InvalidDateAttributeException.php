<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InvalidDateAttributeException extends PropertyException implements
    DomainErrorInterface,
    TemplatedErrorMessageInterface
{
    private TemplatedErrorMessage $templatedMessage;
    private string $attributeCode;

    private function __construct(TemplatedErrorMessage $templatedMessage, string $attributeCode)
    {
        parent::__construct((string) $templatedMessage);
        $this->templatedMessage = $templatedMessage;
        $this->attributeCode = $attributeCode;
        $this->propertyName = $attributeCode;
    }

    public static function withCode(string $attributeCode, string $dateFormat): self
    {
        return new self(
            new TemplatedErrorMessage(
                'The {attribute_code} attribute requires a valid date. Please use the following format {date_format} for dates.',
                ['attribute_code' => $attributeCode, 'date_format' => $dateFormat]
            ),
            $attributeCode
        );
    }

    public function getTemplatedErrorMessage(): TemplatedErrorMessage
    {
        return $this->templatedMessage;
    }

    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }
}
