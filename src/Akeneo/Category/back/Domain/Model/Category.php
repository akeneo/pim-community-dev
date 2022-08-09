<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Model;

use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Category
{
    public function __construct(
        private CategoryId $id,
        private Code $code,
        private LabelCollection $labelCollection,
        private ?CategoryId $parentId,
    ) {
    }

    public function getId(): CategoryId
    {
        return $this->id;
    }

    public function getCode(): Code
    {
        return $this->code;
    }

    public function getLabelCollection(): LabelCollection
    {
        return $this->labelCollection;
    }

    public function getParentId(): ?CategoryId
    {
        return $this->parentId;
    }

    public function setLabel(string $localeCode, string $label): void
    {
        $this->labelCollection->setLabel($localeCode, $label);
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(): array
    {
        return [
            'id' => (string) $this->getId(),
            'code' => (string) $this->getCode(),
            'labels' => $this->getLabelCollection()->normalize(),
            'parent' => (string) $this->getParentId(),
        ];
    }
}
