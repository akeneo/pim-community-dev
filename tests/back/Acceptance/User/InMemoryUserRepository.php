<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\User;

use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\Uuid;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryUserRepository implements IdentifiableObjectRepositoryInterface, SaverInterface, UserRepositoryInterface
{
    /** @var UserInterface[] */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function save($user, array $options = [])
    {
        if (!$user instanceof UserInterface) {
            throw new \InvalidArgumentException('Only user objects are supported.');
        }
        if (null === $user->getId()) {
            $user->setId(Uuid::uuid4()->toString());
        }

        $this->users->set($user->getUsername(), $user);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['username'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->users->get($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function countAll(): int
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        foreach ($this->users as $user) {
            if ($user->getId() === $id) {
                return $user;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $users = [];
        foreach ($this->users as $user) {
            $keepThisUser = true;
            foreach ($criteria as $key => $value) {
                $getter = sprintf('get%s', ucfirst($key));
                if ($user->$getter() !== $value) {
                    $keepThisUser = false;
                }
            }

            if ($keepThisUser) {
                $users[] = $user;
            }
        }

        return $users;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        $username = $criteria['username'] ?? null;
        if (null === $username || count($criteria) > 1) {
            throw new \InvalidArgumentException('This method only supports finding by "username"');
        }

        foreach ($this->users as $user) {
            if (null !== $username && $username === $user->getUsername()) {
                return $user;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findByGroupIds(array $groupIds)
    {
        throw new NotImplementedException(__METHOD__);
    }
}
