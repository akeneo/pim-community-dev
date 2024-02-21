<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UnknownFamilyException extends InvalidPropertyException implements
    DomainErrorInterface,
    TemplatedErrorMessageInterface
{
    /** @var TemplatedErrorMessage */
    private $templatedErrorMessage;

    public function __construct(string $propertyName, string $propertyValue, string $className)
    {
        $this->templatedErrorMessage = new TemplatedErrorMessage(
            'The {family_code} family does not exist in your PIM.',
            ['family_code' => $propertyValue]
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
