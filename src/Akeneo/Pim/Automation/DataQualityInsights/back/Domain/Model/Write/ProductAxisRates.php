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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

final class ProductAxisRates
{
    /** @var AxisCode */
    private $axisCode;

    /** @var ProductId */
    private $productId;

    /** @var \DateTimeImmutable */
    private $evaluatedAt;

    /** @var ChannelLocaleRateCollection */
    private $rates;

    public function __construct(AxisCode $axisCode, ProductId $productId, \DateTimeImmutable $evaluatedAt, ChannelLocaleRateCollection $rates)
    {
        $this->axisCode = $axisCode;
        $this->productId = $productId;
        $this->evaluatedAt = $evaluatedAt;
        $this->rates = $rates;
    }

    public function getAxisCode(): AxisCode
    {
        return $this->axisCode;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getEvaluatedAt(): \DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    public function getRates(): ChannelLocaleRateCollection
    {
        return $this->rates;
    }
}
