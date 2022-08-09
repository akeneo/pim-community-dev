<?php

declare(strict_types=1);

namespace Akeneo\Category\Api\Model\Category;

use Akeneo\Category\Domain\ValueObject\CategoryId as CategoryIdFromDomain;
use Webmozart\Assert\Assert;

/**
 * This model represents a category ID as exposed to the outside of the category bounded context
 * It resembles the eponymous internal domain model but can drift in the future
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryId
{
    public static function fromDomainModel(CategoryIdFromDomain $cId): CategoryId {
        return new CategoryId(
            $cId->getId(),
        );
    }

    public function __construct(private int $id)
    {
        Assert::greaterThan($id, 0);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return (string)$this->id;
    }
}
