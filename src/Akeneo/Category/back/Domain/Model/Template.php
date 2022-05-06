<?php

namespace Akeneo\Category\Domain\Model;

use Akeneo\Category\Domain\ValueObject\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\CategoryIdentifier;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\TemplateCode;
use Akeneo\Category\Domain\ValueObject\TemplateIdentifier;

class Template
{
    public function __construct(
        private TemplateIdentifier $identifier,
        private TemplateCode $code,
        private LabelCollection $labels,
        private CategoryIdentifier $categoryTreeIdentifier,
        private AttributeCollection $attributeCollection
    ) {

    }

    public function normalize(): array
    {
        return [
            'identifier' => (string) $this->identifier,
            'code' => (string) $this->code,
            'labels' => $this->labels->normalize(),
            'category_tree_identifier' => (string) $this->categoryTreeIdentifier,
            'attributes' => $this->attributeCollection->normalize(),
        ];
    }
}
