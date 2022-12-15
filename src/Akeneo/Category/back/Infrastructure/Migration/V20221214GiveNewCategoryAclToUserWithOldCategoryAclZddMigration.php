<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Migration;

use Akeneo\Platform\Bundle\InstallerBundle\Command\ZddMigration;
use Doctrine\DBAL\Connection;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class V20221214GiveNewCategoryAclToUserWithOldCategoryAclZddMigration implements ZddMigration
{
    public function __construct(
        private AclManager $aclManager,
        private Connection $connection,
    )
    {

    }

    public function migrate(): void
    {
        // TODO: Implement migrate() method.
    }

    public function getName(): string
    {
        return 'GiveToUsersNewEnrichedCategoriesAclsBasedOnLegacyCategoriesAcls';
    }

    /**
     * @return array<string>
     */
    private function getRoles(): array
    {
        $data = $this->connection->fetchAllAssociative(<<<SQL
                SELECT identifier
                FROM acl_security_identities
        SQL);

        return $data;
    }
}
