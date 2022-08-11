<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject\Attribute;

use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\TemplateId;

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
        protected AttributeIsLocalizable $isLocalizable,
        protected LabelCollection $labelCollection,
        protected TemplateId $templateId
    ) {
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

    public function isLocalizable(): AttributeIsLocalizable
    {
        return $this->isLocalizable;
    }

    public function getLabelCollection(): LabelCollection
    {
        return $this->labelCollection;
    }

    public function getTemplateId(): TemplateId
    {
        return $this->templateId;
    }
}
