<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Model;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\TemplateCode;
use Akeneo\Category\Domain\ValueObject\TemplateId;

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
}
