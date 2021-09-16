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
class NotLocalizableAndScopableAttributeException extends InvalidAttributeException implements
    DomainErrorInterface,
    TemplatedErrorMessageInterface
{
    private TemplatedErrorMessage $templatedMessage;
    private string $attributeCode;

    private function __construct(TemplatedErrorMessage $templatedMessage, string $attributeCode)
    {
        parent::__construct($attributeCode, null, null, (string) $templatedMessage);
        $this->templatedMessage = $templatedMessage;
        $this->attributeCode = $attributeCode;
        $this->propertyName = 'attribute';
    }

    public static function fromAttributeChannelAndLocale(
        string $attributeCode,
        ?string $channelCode,
        ?string $localeCode
    ): self {
        return new self(
            new TemplatedErrorMessage(
                'The {attribute_code} attribute requires a value per channel ({channel_code} was detected)' .
                ' but does not require a value per locale ({locale_code} was detected).',
                [
                    'attribute_code' => $attributeCode,
                    'channel_code' => $channelCode ?? 'nothing',
                    'locale_code' => $localeCode ?? 'nothing',
                ]
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
