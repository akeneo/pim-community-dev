<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Catalog;

use Akeneo\Pim\Structure\Component\Model\GroupType;
use Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;
use Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryGroupTypeRepository implements SaverInterface, GroupTypeRepositoryInterface
{
    /** @var GroupType[] */
    private $groupTypes;

    public function __construct()
    {
        $this->groupTypes = new ArrayCollection();
    }

    public function save($group, array $options = [])
    {
        if (!$group instanceof GroupTypeInterface) {
            throw new \InvalidArgumentException('Only group type objects are supported.');
        }
        $this->groupTypes->set($group->getCode(), $group);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->groupTypes->get($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function findTypeIds()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        throw new NotImplementedException(__METHOD__);
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
        $groupTypes = [];
        foreach ($this->groupTypes as $groupType) {
            $keepThisGroupType = true;
            foreach ($criteria as $key => $value) {
                $getter = sprintf('get%s', ucfirst($key));
                if ($groupType->$getter() !== $value) {
                    $keepThisGroupType = false;
                }
            }

            if ($keepThisGroupType) {
                $groupTypes[] = $groupType;
            }
        }

        return $groupTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        throw new NotImplementedException(__METHOD__);
    }
}
