<?php

namespace Akeneo\Category\Application\Storage\Save\Saver;

use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

interface CategoryTemplateAttributeSaver
{
    public function insert(TemplateUuid $templateUuid, AttributeCollection $attributeCollection): void;

    public function update(Attribute $attribute): void;
}
