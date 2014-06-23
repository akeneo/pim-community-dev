<?php

namespace PimEnterprise\Bundle\DataGridBundle\EventListener;

use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;


use PimEnterprise\Bundle\SecurityBundle\Voter\JobProfileVoter;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;

use Symfony\Component\Security\Core\SecurityContextInterface;

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
     *
     * @TODO: Create AccessRepositoryInterface
     */
    public function __construct(
        $accessRepository,
        SecurityContextInterface $securityContext,
        RequestParameters $requestParams
    ) {
        $this->accessRepository = $accessRepository;
        $this->securityContext  = $securityContext;
        $this->requestParams    = $requestParams;
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
