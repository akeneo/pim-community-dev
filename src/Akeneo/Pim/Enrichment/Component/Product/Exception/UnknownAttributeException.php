<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;

/**
 * Exception thrown when performing an action on an unknown attribute.
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UnknownAttributeException extends PropertyException implements
    DomainErrorInterface,
    TemplatedErrorMessageInterface
{
    /** @var TemplatedErrorMessage */
    private $templatedErrorMessage;

    public function __construct(string $attributeCode, ?\Exception $previous = null)
    {
        $this->templatedErrorMessage = new TemplatedErrorMessage(
            'The {attribute_code} attribute does not exist in your PIM.',
            ['attribute_code' => $attributeCode]
        );

        parent::__construct((string) $this->templatedErrorMessage, 0, $previous);
        $this->propertyName = $attributeCode;
    }

    public function getTemplatedErrorMessage(): TemplatedErrorMessage
    {
        return $this->templatedErrorMessage;
    }
}
