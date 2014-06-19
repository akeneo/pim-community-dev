<?php

namespace PimEnterprise\Bundle\ImportExportBundle\Manager;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\ImportExportBundle\Entity\Repository\JobExecutionRepository;
use Pim\Bundle\ImportExportBundle\Manager\JobExecutionManager as PimJobExecutionManager;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\JobProfileAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Voter\JobProfileVoter;

/**
 * Override job execution manager to introduce permissions
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class JobExecutionManager extends PimJobExecutionManager
{
    /** @var JobProfileAccessRepository */
    protected $accessRepository;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * Constructor
     *
     * @param JobExecutionRepository     $repository
     * @param SecurityFacade             $securityFacade
     * @param JobProfileAccessRepository $accessRepository
     * @param SecurityContextInterface   $securityContext
     */
    public function __construct(
        JobExecutionRepository $repository,
        SecurityFacade $securityFacade,
        JobProfileAccessRepository $accessRepository,
        SecurityContextInterface $securityContext
    ) {
        parent::__construct($repository, $securityFacade);

        $this->accessRepository = $accessRepository;
        $this->securityContext  = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastOperationsData(array $types)
    {
        $types = array_filter(
            $types,
            function ($type) {
                return $this->securityFacade->isGranted(
                    sprintf('pim_importexport_%s_execution_show', $type)
                );
            }
        );

        $subQB = $this->accessRepository->getGrantedJobsQB(
            $this->securityContext->getUser(),
            JobProfileVoter::EXECUTE_JOB_PROFILE
        );

        return $this->repository->getLastOperations($types, $subQB);
    }
}
