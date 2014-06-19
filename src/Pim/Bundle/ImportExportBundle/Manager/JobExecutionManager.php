<?php

namespace Pim\Bundle\ImportExportBundle\Manager;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\ImportExportBundle\Entity\Repository\JobExecutionRepository;

/**
 * Job execution manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionManager
{
    /**
     * Constructor
     *
     * @param JobExecutionRepository $repository
     * @param SecurityFacade         $securityFacade
     */
    public function __construct(
        JobExecutionRepository $repository,
        SecurityFacade $securityFacade
    ) {
        $this->repository     = $repository;
        $this->securityFacade = $securityFacade;
    }

    /**
     * Get last operations data
     *
     * @param array $types
     *
     * @return array
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

        return $this->repository->getLastOperationsData($types);
    }
}
