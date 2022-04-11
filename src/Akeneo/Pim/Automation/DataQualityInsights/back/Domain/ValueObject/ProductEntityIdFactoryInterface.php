<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

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
     * @return ProductIdCollection
     */
    public function createCollection(array $ids): ProductIdCollection;
}
