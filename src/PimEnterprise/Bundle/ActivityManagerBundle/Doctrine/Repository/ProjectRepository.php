<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\Doctrine\Repository;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Repository\ProjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectRepository extends EntityRepository implements
    ProjectRepositoryInterface,
    IdentifiableObjectRepositoryInterface
{
    public function __construct(EntityManager $em, $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['id'];
    }

    /**
     * {@inheritdoc}
     *
     * @return ProjectInterface
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->find($identifier);
    }
}
