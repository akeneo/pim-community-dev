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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation;

/**
 * Retrieve the ids of the products that have been updated since a given date but that haven't any evaluation created since then.
 */
interface GetUpdatedProductsWithoutUpToDateEvaluationQueryInterface
{
    public function execute(\DateTimeImmutable $updatedSince, int $bulkSize): \Iterator;
}
