<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotScopableAttributeException extends \LogicException implements
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
    }

    public static function withCode(string $attributeCode): self
    {
        return new self(
            new TemplatedErrorMessage(
                'The {attribute_code} attribute does not require a value per channel.',
                ['attribute_code' => $attributeCode]
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
