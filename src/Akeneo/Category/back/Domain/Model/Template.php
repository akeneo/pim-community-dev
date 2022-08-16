<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Model;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateId;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Template
{
    public function __construct(
        private TemplateId $id,
        private TemplateCode $code,
        private LabelCollection $labelCollection,
        private ?CategoryId $categoryTreeId,
        private AttributeCollection $attributeCollection
    ) {
    }

    public function getId(): TemplateId
    {
        return $this->id;
    }

    public function getCode(): TemplateCode
    {
        return $this->code;
    }

    public function getLabelCollection(): LabelCollection
    {
        return $this->labelCollection;
    }

    public function getCategoryTreeId(): ?CategoryId
    {
        return $this->categoryTreeId;
    }

    public function getAttributeCollection(): AttributeCollection
    {
        return $this->attributeCollection;
    }

    /**
     * @return array{
     *     identifier: string,
     *     code: string,
     *     labels: array<string, string>,
     *     category_tree_identifier: ?int,
     *     attributes: array<array<string, mixed>>
     * }
     */
    public function normalize(): array
    {
        return [
            'identifier' => (string) $this->id,
            'code' => (string) $this->code,
            'labels' => $this->labelCollection->normalize(),
            'category_tree_identifier' => $this->categoryTreeId?->getId(),
            'attributes' => $this->attributeCollection->normalize()
        ];

    }
}
