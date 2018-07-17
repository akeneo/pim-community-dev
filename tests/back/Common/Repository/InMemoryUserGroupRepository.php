<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\User;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryUserGroupRepository implements IdentifiableObjectRepositoryInterface, SaverInterface
{
    /** @var GroupInterface[] */
    private $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function save($group, array $options = [])
    {
        if (!$group instanceof GroupInterface) {
            throw new \InvalidArgumentException('Only user group objects are supported.');
        }
        $this->groups->set($group->getName(), $group);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['name'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->groups->get($identifier);
    }
}
