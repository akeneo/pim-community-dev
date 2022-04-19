<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetProductScoresByIdentifiersQueryInterface
{
    /**
     * Returns collections of product scores indexed by their product identifiers
     *
     * @param string[] $productIdentifiers
     *
     * @return Read\Scores[]
     */
    public function byProductIdentifiers(array $productIdentifiers): array;

    public function byProductIdentifier(string $identifier): Read\Scores;
}
