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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Query\EmptySuggestedDataQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class EmptySuggestedDataQuery implements EmptySuggestedDataQueryInterface
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        $query = <<<SQL
UPDATE pim_suggest_data_product_subscription
SET raw_suggested_data = NULL
WHERE raw_suggested_data IS NOT NULL;
SQL;
        $this->connection->executeQuery($query);
    }
}
