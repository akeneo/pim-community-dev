<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ImportExportBundle\Manager;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\ImportExportBundle\Manager\JobExecutionManager as BaseJobExecutionManager;
use PimEnterprise\Bundle\EnrichBundle\Doctrine\ORM\Repository\JobExecutionRepository;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\JobProfileAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Override job execution manager to introduce permissions
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class JobExecutionManager extends BaseJobExecutionManager
{
    /** @var JobProfileAccessRepository */
    protected $accessRepository;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var JobExecutionRepository */
    protected $repository;

    /**
     * Constructor
     *
     * @param JobExecutionRepository     $repository
     * @param SecurityFacade             $securityFacade
     * @param JobProfileAccessRepository $accessRepository
     * @param TokenStorageInterface      $tokenStorage
     */
    public function __construct(
        JobExecutionRepository $repository,
        SecurityFacade $securityFacade,
        JobProfileAccessRepository $accessRepository,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($repository, $securityFacade);

        $this->accessRepository = $accessRepository;
        $this->tokenStorage = $tokenStorage;
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

        $token = $this->tokenStorage->getToken();
        $subQB = $this->accessRepository->getGrantedJobsQB(
            $token->getUser(),
            Attributes::EXECUTE
        );

        return $this->repository->getLastOperations($types, $subQB, $token->getUsername());
    }
}
