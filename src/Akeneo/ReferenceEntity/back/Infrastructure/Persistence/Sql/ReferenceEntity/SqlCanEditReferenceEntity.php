<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\SecurityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\CanEditReferenceEntityInterface;
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

    public function __invoke(SecurityIdentifier $securityIdentifier, ReferenceEntityIdentifier $referenceEntityIdentifier): bool
    {
        return true;
    }
}
