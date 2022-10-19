<?php

namespace Akeneo\Category\Application\Template;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

interface TemplateAttributeRepository
{
    public function insert(TemplateUuid $templateUuid, AttributeCollection $attributeCollection);

    public function update(TemplateUuid $templateUuid, AttributeCollection $attributeCollection);
}
