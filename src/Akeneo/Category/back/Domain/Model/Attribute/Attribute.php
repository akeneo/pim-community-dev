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
use Akeneo\Category\Domain\ValueObject\ValueCollection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class Attribute
{
    public function __construct(
        protected AttributeUuid $uuid,
        protected AttributeCode $code,
        protected AttributeType $type,
        protected AttributeOrder $order,
        protected AttributeIsRequired $isRequired,
        protected AttributeIsScopable $isScopable,
        protected AttributeIsLocalizable $isLocalizable,
        protected LabelCollection $labelCollection,
        protected TemplateUuid $templateUuid,
        protected AttributeAdditionalProperties $additionalProperties,
    ) {
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
        return [
            'uuid' => (string) $this->uuid,
            'code' => (string) $this->code,
            'type' => (string) $this->type,
            'order' => $this->order->intValue(),
            'is_required' => $this->isRequired->normalize(),
            'is_scopable' => $this->isScopable->normalize(),
            'is_localizable' => $this->isLocalizable->normalize(),
            'labels' => $this->labelCollection->normalize(),
            'template_uuid' => (string) $this->templateUuid,
            'additional_properties' => $this->additionalProperties->normalize(),
        ];
    }

    public function getUuid(): AttributeUuid
    {
        return $this->uuid;
    }

    public function getCode(): AttributeCode
    {
        return $this->code;
    }

    public function getType(): AttributeType
    {
        return $this->type;
    }

    public function getOrder(): AttributeOrder
    {
        return $this->order;
    }

    public function isRequired(): AttributeIsRequired
    {
        return $this->isRequired;
    }

    public function isScopable(): AttributeIsScopable
    {
        return $this->isScopable;
    }

    public function isLocalizable(): AttributeIsLocalizable
    {
        return $this->isLocalizable;
    }

    public function getLabelCollection(): LabelCollection
    {
        return $this->labelCollection;
    }

    public function getTemplateUuid(): TemplateUuid
    {
        return $this->templateUuid;
    }

    /**
     * @return string example: title|69e251b3-b876-48b5-9c09-92f54bfb528d
     */
    public function getIdentifier(): string
    {
        return $this->getCode().ValueCollection::SEPARATOR.$this->getUuid();
    }

    public function getAdditionalProperties(): AttributeAdditionalProperties
    {
        return $this->additionalProperties;
    }
}
