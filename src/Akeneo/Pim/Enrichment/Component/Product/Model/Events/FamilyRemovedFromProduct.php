<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\Events;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyRemovedFromProduct
{
    /** @var string */
    private $productIdentifier;

    /** @var string */
    private $formerFamilyCode;

    public function __construct(string $productIdentifier, string $formerFamilyCode)
    {
        $this->productIdentifier = $productIdentifier;
        $this->formerFamilyCode = $formerFamilyCode;
    }

    public function productIdentifier(): string
    {
        return $this->productIdentifier;
    }

    public function formerFamilyCode(): string
    {
        return $this->formerFamilyCode;
    }
}
