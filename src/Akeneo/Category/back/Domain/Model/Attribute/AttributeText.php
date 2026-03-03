<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Model\Attribute;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

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
        AttributeIsRequired $isRequired,
        AttributeIsScopable $isScopable,
        AttributeIsLocalizable $isLocalizable,
        LabelCollection $labelCollection,
        TemplateUuid $templateUuid,
        AttributeAdditionalProperties $additionalProperties,
    ) {
        parent::__construct(
            $uuid,
            $code,
            $type,
            $order,
            $isRequired,
            $isScopable,
            $isLocalizable,
            $labelCollection,
            $templateUuid,
            $additionalProperties,
        );
    }

    public static function create(
        AttributeUuid $uuid,
        AttributeCode $code,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeIsScopable $isScopable,
        AttributeIsLocalizable $isLocalizable,
        LabelCollection $labelCollection,
        TemplateUuid $templateUuid,
        AttributeAdditionalProperties $additionalProperties,
    ): AttributeText {
        return new self(
            $uuid,
            $code,
            new AttributeType(AttributeType::TEXT),
            $order,
            $isRequired,
            $isScopable,
            $isLocalizable,
            $labelCollection,
            $templateUuid,
            $additionalProperties,
        );
    }

    /**
     * @return array{
     *     uuid: string,
     *     code: string,
     *     type: string,
     *     order: int,
     *     is_required: bool,
     *     is_localizable: bool,
     *     is_scopable: bool,
     *     labels: array<string, string>,
     *     template_uuid: string,
     *     additional_properties: array<string, mixed>
     * }
     */
    public function normalize(): array
    {
        return array_merge(
            parent::normalize(),
            [],
        );
    }
}
