<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\Events;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductIdentifierUpdated
{
    /** @var string */
    private $productIdentifier;

    /** @var string */
    private $previousProductIdentifier;

    public function __construct(string $productIdentifier, string $previousProductIdentifier)
    {
        $this->productIdentifier = $productIdentifier;
        $this->previousProductIdentifier = $previousProductIdentifier;
    }

    public function productIdentifier(): string
    {
        return $this->productIdentifier;
    }

    public function previousProductIdentifier(): string
    {
        return $this->previousProductIdentifier;
    }
}
