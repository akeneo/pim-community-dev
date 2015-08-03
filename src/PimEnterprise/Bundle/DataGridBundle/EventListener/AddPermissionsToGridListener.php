<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AccessRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Add permissions to datagrid listener
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class AddPermissionsToGridListener
{
    /** @var AccessRepositoryInterface */
    protected $accessRepository;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var string */
    protected $accessLevel;

    /**
     * @param AccessRepositoryInterface $accessRepository
     * @param TokenStorageInterface     $tokenStorage
     * @param string                    $accessLevel
     */
    public function __construct(
        AccessRepositoryInterface $accessRepository,
        TokenStorageInterface $tokenStorage,
        $accessLevel
    ) {
        $this->accessRepository = $accessRepository;
        $this->tokenStorage     = $tokenStorage;
        $this->accessLevel      = $accessLevel;
    }

    /**
     * Update query build adding permissions
     *
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();

        // Prepare subquery
        $user  = $this->tokenStorage->getToken()->getUser();
        $subQB = $this->accessRepository->getGrantedEntitiesQB($user, $this->accessLevel);

        $datasource->getRepository()->addGridAccessQB(
            $datasource->getQueryBuilder(),
            $subQB
        );

        $queryParameters = [
            'groups' => $user->getGroups()->toArray()
        ];
        $datasource->setParameters($queryParameters);
    }
}
