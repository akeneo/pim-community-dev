<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductEntityIdFactoryInterface
{
    /**
     * @param string $id
     * @return ProductEntityIdInterface
     */
    public function create(string $id): ProductEntityIdInterface;

    /**
     * @param string[] $ids
     * @return ProductEntityIdCollection
     */
    public function createCollection(array $ids): ProductEntityIdCollection;
}
