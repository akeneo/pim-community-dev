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

/**
 * Holds the data necessary to create a proposal on a product:
 *  - product id
 *  - standard format values.
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class ProposalSuggestedData
{
    /** @var int */
    private $productId;

    /** @var array */
    private $suggestedValues;

    /**
     * @param int $productId
     * @param array $suggestedValues
     */
    public function __construct(int $productId, array $suggestedValues)
    {
        $this->productId = $productId;
        $this->suggestedValues = $suggestedValues;
    }

    /**
     * @return int
     */
    public function getProductId(): int
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
