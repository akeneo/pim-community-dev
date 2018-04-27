<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\JobInstance;

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\DataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryJobInstanceRepository implements ObjectRepository, Selectable, DatagridRepositoryInterface, SaverInterface
{
    /** @var Collection */
    private $jobInstances;

    /** @var string */
    private $className;

    /**
     * @param array  $jobInstances
     * @param string $className
     */
    public function __construct(array $jobInstances, string $className)
    {
        $this->jobInstances = new ArrayCollection($jobInstances);
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function save($jobInstance, array $options = [])
    {
        if (!$jobInstance instanceof JobInstance) {
            throw new \InvalidArgumentException('The object argument should be a job instance');
        }

        $this->jobInstances->set($jobInstance->getCode(), $jobInstance);
    }

    /**
     * {@inheritdoc}
     */
    public function createDatagridQueryBuilder()
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
        throw new NotImplementedException(__METHOD__);
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
        return $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function matching(Criteria $criteria)
    {
        throw new NotImplementedException(__METHOD__);
    }
}
