<?php

declare(strict_types=1);

namespace Pim\Bundle\ImportExportBundle\Manager;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\JobExecutionRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Job execution manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionManager
{
    /** @var JobExecutionRepository */
    protected $repository;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * Constructor
     *
     * @param JobExecutionRepository     $repository
     * @param SecurityFacade             $securityFacade
     * @param TokenStorageInterface|null $tokenStorage
     */
    public function __construct(
        JobExecutionRepository $repository,
        SecurityFacade $securityFacade,
        TokenStorageInterface $tokenStorage = null
    ) {
        $this->repository = $repository;
        $this->securityFacade = $securityFacade;
        $this->tokenStorage = $tokenStorage;
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

        $token = $this->tokenStorage->getToken();
        $user = null !==  $token ? $token->getUsername() : null;

        return $this->repository->getLastOperationsData($types, $user);
    }
}
