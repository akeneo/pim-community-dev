<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\FamilyTemplate\Domain\ReadModel;

class FamilyTemplate
{
    /**
     * @param array<string> $categories
     * @param array<mixed> $attributes
     */
    public function __construct(
        public string $templateId,
        public string $displayName,
        public string $description,
        public array $categories,
        public string $iconPath,
        public array $attributes,
    ) {
        $this->attributes = array_map(function ($attribute) {
            return new FamilyTemplateAttribute(
                $attribute['attributeId'],
                $attribute['type'],
                $attribute['scopable'],
                $attribute['localizable'],
            );
        }, $attributes);
    }
}
