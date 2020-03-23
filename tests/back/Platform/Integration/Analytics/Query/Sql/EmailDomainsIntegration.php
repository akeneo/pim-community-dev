<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\Analytics\Query;

use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\Assert;

class EmailDomainsIntegration extends TestCase
{
    public function testEmailDomains()
    {
        $this->createUser('user1', 'user1@example.com');
        $this->createUser('user2', 'user2@example.com');
        $this->createUser('user3', 'user3@example2.com');
        $this->createUser('user4', 'user4@example2.com');
        $this->createUser('user5', 'user5@example3.com');

        $query = $this->get('pim_analytics.query.email_domains');

        $emailDomains = $query->fetch();

        Assert::assertEquals(
            'example.com,example2.com,example3.com',
            $emailDomains
        );
    }

    /**
     * Create test users based on username, email
     * Other properties are automaticlaly created.
     */
    protected function createUser(string $username, string $email)
    {
        $conn = $this->get('database_connection');

        $localeId = $conn->fetchColumn('SELECT id FROM pim_catalog_locale LIMIT 1');

        $sqlInsert = <<<SQL
            INSERT INTO oro_user
            (username, email, ui_locale_id, salt, password, createdAt, updatedAt, timezone, properties) VALUES
            (:username, :email, :localeId, 'my_salt', 'my_password', '2019-09-09', '2019-09-09', 'UTC', '{}')
SQL;

        $conn->executeQuery(
            $sqlInsert,
            [
                "username" => $username,
                "email" => $email,
                "localeId" => $localeId,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
