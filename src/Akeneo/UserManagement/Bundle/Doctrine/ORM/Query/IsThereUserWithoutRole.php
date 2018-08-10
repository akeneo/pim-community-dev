<?php

namespace Akeneo\UserManagement\Bundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Check if there is user who will be without role if we remove a specific role
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsThereUserWithoutRole
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param int $roleIdToExclude
     *
     * @return bool
     */
    public function __invoke($roleIdToExclude)
    {
        $stmt = $this->em->getConnection()->prepare(
        'SELECT COUNT(username) AS count FROM oro_user u LEFT JOIN oro_user_access_role r ON u.id = r.user_id AND role_id != :roleId WHERE role_id IS NULL'
        );
        $stmt->bindValue('roleId', $roleIdToExclude, \PDO::PARAM_INT);

        $stmt->execute();
        $count = $stmt->fetch();

        return $count['count'] > 0;
    }
}
