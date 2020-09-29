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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\RequiredAttributesMask;

interface GetProductModelAttributesMaskQueryInterface
{
    public function execute(ProductId $productModelId): ?RequiredAttributesMask;
}
