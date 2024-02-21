<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Event;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductValidationErrorEvent
{
    /** @var ConstraintViolationListInterface */
    private $constraintViolationList;

    /** @var ProductInterface */
    private $product;

    public function __construct(ConstraintViolationListInterface $constraintViolationList, ProductInterface $product)
    {
        $this->constraintViolationList = $constraintViolationList;
        $this->product = $product;
    }

    public function getConstraintViolationList(): ConstraintViolationListInterface
    {
        return $this->constraintViolationList;
    }

    public function getProduct(): ProductInterface
    {
        return $this->product;
    }
}
