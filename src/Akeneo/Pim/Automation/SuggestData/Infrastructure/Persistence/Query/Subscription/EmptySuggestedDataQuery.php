<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\Subscription;

use Akeneo\Pim\Automation\SuggestData\Domain\Query\Subscription\EmptySuggestedDataQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class EmptySuggestedDataQuery implements EmptySuggestedDataQueryInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string[] $subscriptionIds
     */
    public function execute(array $subscriptionIds): void
    {
        $sql = <<<SQL
UPDATE pim_suggest_data_product_subscription
SET raw_suggested_data = NULL
WHERE subscription_id IN (:subscriptionIds);
SQL;

        $this->entityManager->getConnection()->executeQuery(
            $sql,
            [
                'subscriptionIds' => $subscriptionIds,
            ],
            [
                'subscriptionIds' => Connection::PARAM_STR_ARRAY,
            ]
        );
    }
}
