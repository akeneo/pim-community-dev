<?php

declare(strict_types=1);

namespace Akeneo\Category\Api\Model\Category;

use Akeneo\Category\Domain\Model\Category as CategoryFromDomain;

/**
 * This model represents the core information about a category as exposed to the outside of the category bounded context
 * It resembles the eponymous internal domain model but can drift in the future.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Category
{
    public static function fromDomainModel(CategoryFromDomain $c): Category
    {
        return new Category(
            CategoryId::fromDomainModel($c->getId()),
            Code::fromDomainModel($c->getCode()),
            LabelCollection::fromDomainModel($c->getLabelCollection()),
            CategoryId::fromDomainModel($c->getParentId()),
        );
    }

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
