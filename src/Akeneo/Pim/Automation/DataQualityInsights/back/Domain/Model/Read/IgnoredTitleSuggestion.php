<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class IgnoredTitleSuggestion
{
    /**
     * @var ProductId
     */
    private $productId;
    /**
     * @var array
     */
    private $ignoredSuggestion;

    public function __construct(ProductId $productId, array $ignoredSuggestion)
    {
        $this->productId = $productId;
        $this->ignoredSuggestion = $ignoredSuggestion;
    }

    /**
     * @return ProductId
     */
    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    /**
     * @return array
     */
    public function getIgnoredSuggestion(): array
    {
        return $this->ignoredSuggestion;
    }
}
