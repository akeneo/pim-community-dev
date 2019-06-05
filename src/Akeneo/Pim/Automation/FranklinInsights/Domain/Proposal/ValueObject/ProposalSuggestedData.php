<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;

/**
 * Holds the data necessary to create a proposal on a product:
 *  - product id
 *  - standard format values.
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class ProposalSuggestedData
{
    /** @var ProductId */
    private $productId;

    /** @var array */
    private $suggestedValues;

    public function __construct(ProductId $productId, array $suggestedValues)
    {
        $this->productId = $productId;
        $this->suggestedValues = $suggestedValues;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    /**
     * @return array
     */
    public function getSuggestedValues(): array
    {
        return $this->suggestedValues;
    }
}
