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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;

final class DashboardRatesProjection
{
    /** @var DashboardProjectionType */
    private $type;

    /** @var DashboardProjectionCode */
    private $code;

    /** @var array */
    private $rates;

    public function __construct(DashboardProjectionType $type, DashboardProjectionCode $code, array $rates)
    {
        $this->type = $type;
        $this->code = $code;
        $this->rates = $rates;
    }

    public function getType(): DashboardProjectionType
    {
        return $this->type;
    }

    public function getCode(): DashboardProjectionCode
    {
        return $this->code;
    }

    public function getRates(): array
    {
        return $this->rates;
    }
}
