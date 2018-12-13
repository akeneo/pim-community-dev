<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Permission;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Permission\CanEditReferenceEntityInterface;
use Akeneo\ReferenceEntity\Domain\Model\UserIdentifier;
use Doctrine\DBAL\Driver\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlCanEditReferenceEntity implements CanEditReferenceEntityInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function __invoke(UserIdentifier $userIdentifier, ReferenceEntityIdentifier $referenceEntityIdentifier): bool
    {
        return false;
    }
}
