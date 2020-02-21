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

final class ProductEvaluation
{
    /** @var ProductId */
    private $productId;

    /** @var AxisEvaluationCollection */
    private $axesEvaluations;

    public function __construct(ProductId $productId, AxisEvaluationCollection $axesEvaluations)
    {
        $this->productId = $productId;
        $this->axesEvaluations = $axesEvaluations;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getAxesEvaluations(): AxisEvaluationCollection
    {
        return $this->axesEvaluations;
    }
}
