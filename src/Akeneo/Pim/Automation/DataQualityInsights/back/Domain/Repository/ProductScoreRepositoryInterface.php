<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductScores;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductScoreRepositoryInterface
{
    /**
     * @param ProductScores[] $productsScores
     */
    public function saveAll(array $productsScores): void;

    public function purgeUntil(\DateTimeImmutable $date): void;
}
