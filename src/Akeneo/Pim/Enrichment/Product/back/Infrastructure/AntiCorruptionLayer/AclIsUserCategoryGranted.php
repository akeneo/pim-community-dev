<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\IsUserCategoryGranted;
use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AclIsUserCategoryGranted implements IsUserCategoryGranted
{
    public function __construct(private ?ProductCategoryAccessQueryInterface $productCategoryAccessQuery)
    {
    }

    public function forProductAndAccessLevel(
        int $userId,
        ProductIdentifier $productIdentifier,
        string $accessLevel
    ): bool {
        Assert::notNull($this->productCategoryAccessQuery);

        $grantedIdentifiers = $this->productCategoryAccessQuery->getGrantedProductIdentifiers(
            [$productIdentifier->asString()],
            $userId,
            $accessLevel
        );

        return [] !== $grantedIdentifiers;
    }
}
