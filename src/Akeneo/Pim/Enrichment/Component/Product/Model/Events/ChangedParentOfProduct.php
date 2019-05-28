<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\Events;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangedParentOfProduct
{
    /** @var string */
    private $productIdentifier;

    /** @var string */
    private $formerParentModelCode;

    /** @var string */
    private $newParentModelCode;

    public function __construct(string $productIdentifier, string $formerParentModelCode, string $newParentModelCode)
    {
        $this->productIdentifier = $productIdentifier;
        $this->formerParentModelCode = $formerParentModelCode;
        $this->newParentModelCode = $newParentModelCode;
    }

    public function productIdentifier(): string
    {
        return $this->productIdentifier;
    }

    public function formerParentModelCode(): string
    {
        return $this->formerParentModelCode;
    }

    public function newParentModelCode(): string
    {
        return $this->newParentModelCode;
    }
}
