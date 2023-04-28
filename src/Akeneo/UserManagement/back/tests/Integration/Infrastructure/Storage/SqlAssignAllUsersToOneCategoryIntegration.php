<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Integration\Infrastructure\Storage;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Domain\Storage\AssignAllUsersToOneCategory;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlAssignAllUsersToOneCategoryIntegration extends TestCase
{
    private readonly Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get(Connection::class);
    }

    public function testExecuteShouldUpdateAllUsersToTheSpecifiedCategory(): void
    {
        $category = $this->createCategory(['code' => 'socks']);

        /** @var AssignAllUsersToOneCategory $sut */
        $sut = $this->get(AssignAllUsersToOneCategory::class);
        $sut->execute($category->getId());

        $sql = <<<SQL
            SELECT defaultTree_id FROM oro_user;
        SQL;
        $result = $this->connection->executeQuery($sql)->fetchFirstColumn();

        $expected = $category->getId();
        foreach ($result as $actual)
        {
            $this->assertEquals($expected, $actual);
        }
    }



    /**
     * @inheritDoc
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
