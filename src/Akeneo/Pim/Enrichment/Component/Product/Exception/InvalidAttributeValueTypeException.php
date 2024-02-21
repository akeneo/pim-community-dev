<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidAttributeValueTypeException extends InvalidPropertyTypeException implements
    DomainErrorInterface,
    TemplatedErrorMessageInterface
{
    /** @var TemplatedErrorMessage */
    private $templatedErrorMessage;

    public function __construct(
        string $attributeCode,
        $attributeValue,
        string $className,
        TemplatedErrorMessage $templatedErrorMessage,
        int $code = 0,
        \Exception $previous = null
    ) {
        $this->templatedErrorMessage = $templatedErrorMessage;

        parent::__construct(
            $attributeCode,
            $attributeValue,
            $className,
            (string) $this->templatedErrorMessage,
            $code,
            $previous
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function stringExpected($attributeCode, $className, $attributeValue)
    {
        $message = 'The {attribute} attribute requires a string, a {invalid} was detected.';
        $templatedErrorMessage = new TemplatedErrorMessage(
            $message,
            [
                'attribute' => $attributeCode,
                'invalid' => gettype($attributeValue),
            ]
        );

        return new static(
            $attributeCode,
            $attributeValue,
            $className,
            $templatedErrorMessage,
            self::STRING_EXPECTED_CODE
        );
    }

    public function getTemplatedErrorMessage(): TemplatedErrorMessage
    {
        return $this->templatedErrorMessage;
    }
}
