<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute;

use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\TemplateId;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeText extends Attribute
{
    protected function __construct(
        AttributeUuid $uuid,
        AttributeCode $code,
        AttributeType $type,
        AttributeOrder $order,
        AttributeIsLocalizable $isLocalizable,
        LabelCollection $labelCollection,
        TemplateId $templateId
    ) {
        parent::__construct(
            $uuid,
            $code,
            $type,
            $order,
            $isLocalizable,
            $labelCollection,
            $templateId
        );
    }

    public static function createText(
        AttributeUuid $uuid,
        AttributeCode $code,
        AttributeOrder $order,
        AttributeIsLocalizable $isLocalizable,
        LabelCollection $labelCollection,
        TemplateId $templateId
    ): AttributeText {
        return new self(
            $uuid,
            $code,
            new AttributeType(AttributeType::TEXT),
            $order,
            $isLocalizable,
            $labelCollection,
            $templateId
        );
    }

    public static function createTextArea(
        AttributeUuid $uuid,
        AttributeCode $code,
        AttributeOrder $order,
        AttributeIsLocalizable $isLocalizable,
        LabelCollection $labelCollection,
        TemplateId $templateId
    ): AttributeText {
        return new self(
            $uuid,
            $code,
            new AttributeType(AttributeType::TEXTAREA),
            $order,
            $isLocalizable,
            $labelCollection,
            $templateId
        );
    }

    public static function createRichText(
        AttributeUuid $uuid,
        AttributeCode $code,
        AttributeOrder $order,
        AttributeIsLocalizable $isLocalizable,
        LabelCollection $labelCollection,
        TemplateId $templateId
    )
    {
        return new self(
            $uuid,
            $code,
            new AttributeType(AttributeType::TEXTAREA),
            $order,
            $isLocalizable,
            $labelCollection,
            $templateId
        );
    }
}
