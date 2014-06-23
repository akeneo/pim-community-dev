<?php

namespace PimEnterprise\Bundle\DataGridBundle\EventListener;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use PimEnterprise\Bundle\SecurityBundle\Voter\JobProfileVoter;

/**
 * Add permissions to datagrid listener
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AddPermissionsToGridListener
{
    /** @var EntityRepository */
    protected $accessRepository;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     *
     * @param EntityRepository $accessRepository
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        $accessRepository,
        SecurityContextInterface $securityContext
    ) {
        $this->accessRepository = $accessRepository;
        $this->securityContext  = $securityContext;
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
            $subQB = $this->accessRepository->getGrantedJobsQB($user, JobProfileVoter::EXECUTE_JOB_PROFILE);

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

    /**
     * @see Pim\Bundle\DataGridBundle\EventListener\AddParametersToGridListener
     *
     * @return array
     */
    protected function prepareParameters()
    {
        $queryParameters = array();
        $queryParameters['roles'] = $user->getRoles();

        return $queryParameters;
    }
}
