<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Model\Enrichment;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Template
{
    public function __construct(
        private TemplateUuid $uuid,
        private TemplateCode $code,
        private LabelCollection $labelCollection,
        private CategoryId $categoryTreeId,
        private AttributeCollection $attributeCollection,
    ) {
    }

    public function getUuid(): TemplateUuid
    {
        return $this->uuid;
    }

    public function getCode(): TemplateCode
    {
        return $this->code;
    }

    public function getLabelCollection(): LabelCollection
    {
        return $this->labelCollection;
    }

    public function getCategoryTreeId(): CategoryId
    {
        return $this->categoryTreeId;
    }

    public function getAttributeCollection(): AttributeCollection
    {
        return $this->attributeCollection;
    }

    /**
     * @return array{
     *     uuid: string,
     *     code: string,
     *     labels: array<string, string>,
     *     category_tree_identifier: ?int,
     *     attributes: array<array<string, mixed>>
     * }
     */
    public function normalize(): array
    {
        return [
            'uuid' => (string) $this->uuid,
            'code' => (string) $this->code,
            'labels' => $this->labelCollection->normalize(),
            'category_tree_identifier' => $this->categoryTreeId?->getValue(),
            'attributes' => $this->attributeCollection->normalize(),
        ];
    }
}
