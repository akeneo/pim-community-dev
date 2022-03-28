<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment;

use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRoleLoader
{
    public function __construct(
        private SimpleFactoryInterface $builder,
        private ObjectUpdaterInterface $updater,
        private ValidatorInterface $validator,
        private SaverInterface $saver,
        private Connection $connection,
    ) {
    }

    public function create(array $data = []): RoleInterface
    {
        /** @var RoleInterface $role */
        $role = $this->builder->create();
        $this->updater->update($role, $data);

        $violations = $this->validator->validate($role);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }

        $this->saver->save($role);

        return $role;
    }

    public function createOnlyNecessary(array $roles = []): void
    {
        $existingRoles = $this->connection->fetchFirstColumn('SELECT role FROM oro_access_role');
        $rolesToCreate = \array_diff($roles, $existingRoles);

        foreach ($rolesToCreate as $role) {
            $this->create([
                'role' => $role,
                'label' => $role,
            ]);
        }
    }
}
