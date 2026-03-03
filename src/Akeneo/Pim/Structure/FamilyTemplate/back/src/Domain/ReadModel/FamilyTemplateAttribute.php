<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\FamilyTemplate\Domain\ReadModel;

class FamilyTemplateAttribute
{
    public function __construct(
        public string $attributeId,
        public string $type,
        public bool $scopable,
        public bool $localizable,
    ) {
    }
}
