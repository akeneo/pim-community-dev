<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Event;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeDeactivatedEvent
{
    public function __construct(
        private readonly TemplateUuid $templateUuid,
        private readonly AttributeUuid $attributeUuid,
    ) {
    }

    public function getTemplateUuid(): TemplateUuid
    {
        return $this->templateUuid;
    }

    public function getAttributeUuid(): AttributeUuid
    {
        return $this->attributeUuid;
    }
}
