<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Product\Component\Product\UseCase\DuplicateProduct;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use LogicException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class DuplicateProductResponse
{
    /** @var ProductInterface */
    private $duplicatedProduct;

    /** @var array */
    private $uniqueAttributeValues;

    /** @var ConstraintViolationListInterface */
    private $constraintViolationList;

    private function __construct(
        ?ProductInterface $duplicatedProduct,
        ?array $uniqueAttributeValues,
        ?ConstraintViolationListInterface $constraintViolationList
    ) {
        $this->duplicatedProduct = $duplicatedProduct;
        $this->uniqueAttributeValues = $uniqueAttributeValues;
        $this->constraintViolationList = $constraintViolationList;
    }

    public static function ok(ProductInterface $duplicatedProduct, array $uniqueAttributeValues)
    {
        return new self($duplicatedProduct, $uniqueAttributeValues, null);
    }

    public static function error(ConstraintViolationListInterface $constraintViolationList)
    {
        return new self(null, null, $constraintViolationList);
    }

    public function isOk(): bool
    {
        return $this->constraintViolationList === null;
    }

    public function duplicatedProduct(): ProductInterface
    {
        if (!$this->isOk()) {
            throw new LogicException("DuplicateProductResponse is not valid. You cannot get the duplicated product value.");
        }

        return $this->duplicatedProduct;
    }

    public function uniqueAttributeValues(): array
    {
        if (!$this->isOk()) {
            throw new LogicException("DuplicateProductResponse is not valid. You cannot get the unique attribute values.");
        }

        return $this->uniqueAttributeValues;
    }

    public function constraintViolationList(): ConstraintViolationListInterface
    {
        if ($this->isOk()) {
            throw new LogicException("DuplicateProductResponse is valid. You cannot get the constraint violation list.");
        }

        return $this->constraintViolationList;
    }
}
