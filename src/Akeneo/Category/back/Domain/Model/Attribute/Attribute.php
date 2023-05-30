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
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @phpstan-import-type LocalizedLabels from LabelCollection
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

    public static function fromType(
        AttributeType $type,
        AttributeUuid $uuid,
        AttributeCode $code,
        AttributeOrder $order,
        AttributeIsRequired $isRequired,
        AttributeIsScopable $isScopable,
        AttributeIsLocalizable $isLocalizable,
        LabelCollection $labelCollection,
        TemplateUuid $templateUuid,
        AttributeAdditionalProperties $additionalProperties,
    ): Attribute {
        return match ((string) $type) {
            AttributeType::RICH_TEXT => new AttributeRichText($uuid, $code, $type, $order, $isRequired, $isScopable, $isLocalizable, $labelCollection, $templateUuid, $additionalProperties),
            AttributeType::TEXT => new AttributeText($uuid, $code, $type, $order, $isRequired, $isScopable, $isLocalizable, $labelCollection, $templateUuid, $additionalProperties),
            AttributeType::IMAGE => new AttributeImage($uuid, $code, $type, $order, $isRequired, $isScopable, $isLocalizable, $labelCollection, $templateUuid, $additionalProperties),
            AttributeType::TEXTAREA => new AttributeTextArea($uuid, $code, $type, $order, $isRequired, $isScopable, $isLocalizable, $labelCollection, $templateUuid, $additionalProperties),
            default => throw new \LogicException(sprintf('Type not recognized: "%s"', $type)),
        };
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
        return $this->getCode().AbstractValue::SEPARATOR.$this->getUuid();
    }

    public function getAdditionalProperties(): AttributeAdditionalProperties
    {
        return $this->additionalProperties;
    }

    /**
     * @param array{
     *      uuid: string,
     *      code: string,
     *      attribute_type: string,
     *      attribute_order: int,
     *      is_required: bool,
     *      is_scopable: bool,
     *      is_localizable: bool,
     *      labels: string|null,
     *      category_template_uuid: string,
     *      additional_properties: string|null
     * } $result
     */
    public static function fromDatabase(array $result): self
    {
        $id = AttributeUuid::fromString($result['uuid']);
        $code = new AttributeCode($result['code']);
        $type = new AttributeType($result['attribute_type']);
        $order = AttributeOrder::fromInteger((int) $result['attribute_order']);
        $isRequired = AttributeIsRequired::fromBoolean((bool) $result['is_required']);
        $isScopable = AttributeIsScopable::fromBoolean((bool) $result['is_scopable']);
        $isLocalizable = AttributeIsLocalizable::fromBoolean((bool) $result['is_localizable']);
        $labelCollection = $result['labels'] ?
            LabelCollection::fromArray(
                json_decode($result['labels'], true, 512, JSON_THROW_ON_ERROR),
            ) : null;
        $templateUuid = TemplateUuid::fromString($result['category_template_uuid']);
        $additionalProperties = $result['additional_properties'] ?
            AttributeAdditionalProperties::fromArray(
                json_decode($result['additional_properties'], true, 512, JSON_THROW_ON_ERROR),
            ) : null;

        return Attribute::fromType($type, $id, $code, $order, $isRequired, $isScopable, $isLocalizable, $labelCollection, $templateUuid, $additionalProperties);
    }

    /**
     * @param LocalizedLabels $labels
     */
    public function update(?bool $isRichTextArea, ?array $labels): void
    {
        if ($isRichTextArea !== null) {
            $validTypes = [AttributeType::TEXTAREA, AttributeType::RICH_TEXT];
            Assert::inArray((string) $this->getType(), $validTypes);
            $this->type = new AttributeType(($isRichTextArea) ? AttributeType::RICH_TEXT : AttributeType::TEXTAREA);
        }

        if ($labels !== null) {
            $labels = LabelCollection::fromArray($labels);

            foreach ($labels->getIterator() as $local => $label) {
                $this->labelCollection->setTranslation($local, $label);
            }
        }
    }
}
