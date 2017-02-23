<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectStatusRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectStatusRepository extends EntityRepository implements ProjectStatusRepositoryInterface
{
    /**
     * @param EntityManager $em
     * @param string        $class
     */
    public function __construct(EntityManager $em, $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));
    }

    /**
     * {@inheritdoc}
     */
    public function findProjectStatus(ProjectInterface $project, UserInterface $user)
    {
        return $this->findOneBy(['project' => $project, 'user' => $user]);
    }
}
