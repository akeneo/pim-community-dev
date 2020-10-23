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

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Axis;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Consistency;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Axis\Enrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AxisCode;

final class PimEnterpriseAxisRegistry implements AxisRegistryInterface
{
    /** @var array */
    private $axes;

    public function __construct()
    {
        $this->axes = [
            Enrichment::AXIS_CODE => new Enrichment(),
            Consistency::AXIS_CODE => new Consistency(),
        ];
    }

    public function get(AxisCode $axisCode): ?Axis
    {
        return $this->axes[strval($axisCode)] ?? null;
    }

    public function all(): array
    {
        return $this->axes;
    }
}
