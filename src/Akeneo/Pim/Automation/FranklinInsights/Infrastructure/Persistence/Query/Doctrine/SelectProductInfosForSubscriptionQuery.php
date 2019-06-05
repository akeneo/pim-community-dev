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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductInfosForSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductIdentifierValuesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductInfosForSubscriptionQueryInterface;
use Doctrine\DBAL\Connection;

class SelectProductInfosForSubscriptionQuery implements SelectProductInfosForSubscriptionQueryInterface
{
    /** @var Connection */
    private $connection;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var SelectProductIdentifierValuesQuery */
    private $selectProductIdentifierValuesQuery;

    public function __construct(
        Connection $connection,
        FamilyRepositoryInterface $familyRepository,
        SelectProductIdentifierValuesQueryInterface $selectProductIdentifierValuesQuery
    ) {
        $this->connection = $connection;
        $this->familyRepository = $familyRepository;
        $this->selectProductIdentifierValuesQuery = $selectProductIdentifierValuesQuery;
    }

    public function execute(ProductId $productId): ?ProductInfosForSubscription
    {
        $query = <<<SQL
SELECT p.identifier, f.code as family_code,
    NOT ISNULL(p.product_model_id) as is_variant, 
    EXISTS(SELECT s.id FROM pimee_franklin_insights_subscription s WHERE s.product_id = p.id) as is_subscribed
FROM pim_catalog_product p
     LEFT JOIN pim_catalog_family f ON f.id = p.family_id
WHERE p.id = :product_id
SQL;
        $statement = $this->connection->executeQuery(
            $query,
            ['product_id' => (string) $productId->toInt()],
            ['product_id' => \PDO::PARAM_INT]
        );

        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        $familyCode = isset($result['family_code']) ? new FamilyCode($result['family_code']) : null;
        $family = null !== $familyCode ? $this->familyRepository->findOneByIdentifier($familyCode) : null;

        return empty($result) ? null : new ProductInfosForSubscription(
            $productId,
            $this->getProductIdentifierValues($productId),
            $family,
            $result['identifier'],
            (bool) $result['is_variant'],
            (bool) $result['is_subscribed']
        );
    }

    private function getProductIdentifierValues(ProductId $productId): ProductIdentifierValues
    {
        $productIdentifierValuesCollection = $this->selectProductIdentifierValuesQuery->execute([$productId]);
        $productIdentifierValues = $productIdentifierValuesCollection->get($productId);

        return $productIdentifierValues instanceof ProductIdentifierValues ? $productIdentifierValues
            : new ProductIdentifierValues($productId, []);
    }
}
