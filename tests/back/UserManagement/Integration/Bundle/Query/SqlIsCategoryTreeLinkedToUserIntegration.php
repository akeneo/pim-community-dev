<?php

declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle\Query;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Bundle\Storage\Query\SqlIsCategoryTreeLinkedToUser;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SqlIsCategoryTreeLinkedToUserIntegration extends TestCase
{
    private SqlIsCategoryTreeLinkedToUser $isCategoryTreeLinkedToUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->isCategoryTreeLinkedToUser = $this->get('pim_user.query.is_category_tree_linked_to_user');
    }

    public function test_it_counts_the_number_users_linked_to_a_category(): void
    {
        $category = $this->createCategory(['code' => 'clothes']);
        $this->createUser([
            'username' => 'julia',
            'email' => 'julia@akeneo.com',
            'password' => 'a_password',
            'default_category_tree' => 'clothes'
        ]);

        $isLinked = $this->isCategoryTreeLinkedToUser->byCategoryTreeId($category->getId());

        $this->assertTrue($isLinked);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createUser(array $data): UserInterface
    {
        $user = $this->get('pim_user.factory.user')->create();
        $this->get('pim_user.updater.user')->update($user, $data);
        $this->get('validator')->validate($user);
        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }
}
