<?php

namespace Akeneo\Category\API;

use Akeneo\Category\Domain\Model\Template;
use Akeneo\Category\Domain\ValueObject\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\CategoryIdentifier;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\TemplateCode;
use Akeneo\Category\Domain\ValueObject\TemplateIdentifier;

class UpsertCategoryTemplateCommand
{
    private Template $template;

    public function __construct(
        private bool $isCreation,
        private TemplateIdentifier $templateIdentifier,
        private TemplateCode $templateCode,
        private CategoryIdentifier $categoryTreeIdentifier,
        private LabelCollection $labels,
        private AttributeCollection $attributeCollection,
    ) {
        $this->setTemplate();
    }

    public function getAttributeCollection(): AttributeCollection
    {
        return $this->attributeCollection;
    }

    public function getCategoryTreeIdentifier(): CategoryIdentifier
    {
        return $this->categoryTreeIdentifier;
    }

    public function getLabels(): LabelCollection
    {
        return $this->labels;
    }

    public function getTemplateCode(): TemplateCode
    {
        return $this->templateCode;
    }

    public function getTemplateIdentifier(): ?TemplateIdentifier
    {
        return $this->templateIdentifier;
    }

    public function isCreation(): bool
    {
        return $this->isCreation;
    }

    public function getTemplate(): Template
    {
        return $this->template;
    }

    private function setTemplate(): void
    {
        $this->template = new Template(
            $this->templateIdentifier,
            $this->getTemplateCode(),
            $this->getLabels(),
            $this->categoryTreeIdentifier,
            $this->attributeCollection
        );
    }
}
