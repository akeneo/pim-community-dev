<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\ServiceApi;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Unit
{
    public string $code;
    public array $labels;
    public string $symbol;
    public array $convertFromStandard;
}
