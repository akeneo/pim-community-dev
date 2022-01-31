<?php
/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\ComputeCaseWords;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

interface ComputeCaseWordsRate
{
    public function __invoke(?string $productValue): ?Rate;
}
