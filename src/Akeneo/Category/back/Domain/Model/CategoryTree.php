<?php

namespace Akeneo\Category\Domain\Model;

use Akeneo\Category\Domain\ValueObject\CategoryCode;
use Akeneo\Category\Domain\ValueObject\CategoryIdentifier;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\TemplateIdentifier;

class CategoryTree
{
    public function __construct(
        private CategoryIdentifier $identifier,
        private CategoryCode $code,
        private LabelCollection $labels,
        private TemplateIdentifier $templateIdentifier,
    ) {

    }
    public function normalize(): array
    {
        return [
            'identifier' => (string) $this->identifier,
            'code' => (string) $this->code,
            'labels' => $this->labels->normalize(),
            'template_identifier' => (string) $this->templateIdentifier,
        ];
    }
}
