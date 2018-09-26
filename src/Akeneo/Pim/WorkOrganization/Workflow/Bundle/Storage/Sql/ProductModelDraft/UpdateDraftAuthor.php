<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql\ProductModelDraft;

use Doctrine\DBAL\Connection;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class UpdateDraftAuthor
{
    /** @var Connection */
    public $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(string $previousUsername, string $currentUsername): void
    {
        $sql = 'UPDATE pimee_workflow_product_model_draft SET author = :current_username WHERE author = :previous_username';

        $this->connection->executeUpdate(
            $sql,
            [
                'previous_username' => $previousUsername,
                'current_username' => $currentUsername,
            ]
        );
    }
}
