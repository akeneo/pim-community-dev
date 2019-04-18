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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Acceptance\Persistence\InMemory\Query;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductInfosForSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductInfosForSubscriptionQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Persistence\InMemory\Query\InMemorySelectProductIdentifierValuesQuery;
use Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Persistence\InMemory\Repository\InMemoryFamilyRepository;
use Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Persistence\InMemory\Repository\InMemoryProductSubscriptionRepository;

class InMemorySelectProductInfosForSubscriptionQuery implements SelectProductInfosForSubscriptionQueryInterface
{
    /** @var InMemoryProductRepository */
    private $productRepository;

    /** @var InMemoryProductSubscriptionRepository */
    private $productSubscriptionRepository;

    /** @var InMemoryFamilyRepository */
    private $familyRepository;

    /** @var InMemorySelectProductIdentifierValuesQuery */
    private $selectProductIdentifierValuesQuery;

    public function __construct(
        InMemoryProductRepository $productRepository,
        InMemoryProductSubscriptionRepository $productSubscriptionRepository,
        InMemoryFamilyRepository $familyRepository,
        InMemorySelectProductIdentifierValuesQuery $selectProductIdentifierValuesQuery
    ) {
        $this->productRepository = $productRepository;
        $this->productSubscriptionRepository = $productSubscriptionRepository;
        $this->familyRepository = $familyRepository;
        $this->selectProductIdentifierValuesQuery = $selectProductIdentifierValuesQuery;
    }

    public function execute(ProductId $productId): ?ProductInfosForSubscription
    {
        $product = $this->productRepository->find($productId->toInt());
        if (!$product instanceof ProductInterface) {
            return null;
        }

        $subscription = $this->productSubscriptionRepository->findOneByProductId($productId);

        $family = $product->getFamily() instanceof Family
            ? $this->familyRepository->findOneByIdentifier(new FamilyCode($product->getFamily()->getCode()))
            : null;

        $productIdentifierValuesCollection = $this->selectProductIdentifierValuesQuery->execute([$productId]);
        $productIdentifierValues = $productIdentifierValuesCollection->get($productId);

        return new ProductInfosForSubscription(
            $productId,
            $productIdentifierValues ?? new ProductIdentifierValues($productId, []),
            $family,
            $product->getIdentifier(),
            $product->isVariant(),
            $subscription instanceof ProductSubscription
        );
    }
}
