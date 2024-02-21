<?php

namespace Akeneo\Category\Domain\Query;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

interface DeleteTemplateAttribute
{
    public function __invoke(TemplateUuid $templateUuid, AttributeUuid $attributeUuid): void;
}
