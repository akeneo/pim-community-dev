<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

final class CriterionEvaluationResult
{
    /** @var CriterionRateCollection */
    private $rates;

    /** @var array */
    private $data;

    public function __construct(CriterionRateCollection $rates, array $data)
    {
        $this->rates = $rates;
        $this->data = $data;
    }

    public function getRates(): CriterionRateCollection
    {
        return $this->rates;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
