<?php

namespace PimEnterprise\Bundle\DataGridBundle\EventListener;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AccessRepositoryInterface;

/**
 * Add permissions to datagrid listener
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AddPermissionsToGridListener
{
    /** @var AccessRepositoryInterface */
    protected $accessRepository;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var string */
    protected $accessLevel;

    /**
     * @param AccessRepositoryInterface         $accessRepository
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        AccessRepositoryInterface $accessRepository,
        SecurityContextInterface $securityContext,
        $accessLevel
    ) {
        $this->accessRepository = $accessRepository;
        $this->securityContext  = $securityContext;
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
        if ($datasource instanceof OrmDatasource) {

            // Prepare subquery
            $user  = $this->securityContext->getUser();
            $subQB = $this->accessRepository->getGrantedEntitiesQB($user, $this->accessLevel);

            $datasource->getRepository()->addGridAccessQB(
                $datasource->getQueryBuilder(),
                $subQB
            );

            $queryParameters = [
                'roles' => $user->getRoles()
            ];
            $datasource->setParameters($queryParameters);
        }
    }
}
