<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Event;

use Akeneo\Pim\Enrichment\Component\Error\Event\DomainErrorEvent;
use Akeneo\Pim\Enrichment\Component\Error\IdentifiableDomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductDomainErrorEvent extends DomainErrorEvent
{
    /** @var ProductInterface */
    private $product;

    public function __construct(IdentifiableDomainErrorInterface $error, ProductInterface $product)
    {
        parent::__construct($error);

        $this->product = $product;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }
}
