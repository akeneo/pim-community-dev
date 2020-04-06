<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Repository;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\BusinessErrorRepository;
use Doctrine\DBAL\Connection as DbalConnection;

class DbalApiBusinessErrorRepository implements BusinessErrorRepository
{
    /** @var DbalConnection */
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function bulkInsert(array $businessErrors): void
    {
        foreach ($businessErrors as $businessError) {
            $this->insert($businessError);
        }
    }

    private function insert(BusinessError $businessError): void
    {
        $insertQuery = <<<SQL
INSERT INTO akeneo_connectivity_connection_audit_business_error (connection_code, content, error_datetime)
VALUES (:connection_code, :content, UTC_TIMESTAMP())
SQL;

        $this->dbalConnection->executeQuery(
            $insertQuery,
            ['connection_code' => $businessError->connectionCode(), 'content' => $businessError->content()]
        );
    }
}
