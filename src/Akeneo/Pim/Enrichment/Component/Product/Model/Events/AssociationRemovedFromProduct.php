<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model\Events;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationRemovedFromProduct
{
    /** @var string */
    private $productIdentifier;

    /** @var string */
    private $associationId;

    public function __construct(string $productIdentifier, string $associationId)
    {
        $this->productIdentifier = $productIdentifier;
        $this->associationId = $associationId;
    }

    public function productIdentifier(): string
    {
        return $this->productIdentifier;
    }

    public function associationId(): string
    {
        return $this->associationId;
    }
}