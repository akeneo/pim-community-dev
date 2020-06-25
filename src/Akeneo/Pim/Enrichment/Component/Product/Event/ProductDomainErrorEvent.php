<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Event;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductDomainErrorEvent
{
    /** @var DomainErrorInterface */
    private $error;

    /** @var ?ProductInterface */
    private $product;

    public function __construct(DomainErrorInterface $error, ?ProductInterface $product)
    {
        $this->error = $error;
        $this->product = $product;
    }

    public function getError(): DomainErrorInterface
    {
        return $this->error;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }
}
