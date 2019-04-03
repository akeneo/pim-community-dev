<?php

namespace Akeneo\Pim\Enrichment\Bundle\MassEditAction;

use Akeneo\Pim\Enrichment\Bundle\MassEditAction\Operation\BatchableOperationInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * Background process launcher for mass edit Operations.
 * It internally uses the Akeneo\Tool\Bundle\BatchQueueBundle\Launcher\QueueJobLauncher.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OperationJobLauncher
{
    /** @var JobLauncherInterface */
    protected $jobLauncher;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $jobInstanceRepo;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param JobLauncherInterface                  $jobLauncher
     * @param IdentifiableObjectRepositoryInterface $jobInstanceRepo
     * @param TokenStorageInterface                 $tokenStorage
     */
    public function __construct(
        JobLauncherInterface $jobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepo,
        TokenStorageInterface $tokenStorage
    ) {
        $this->jobLauncher = $jobLauncher;
        $this->jobInstanceRepo = $jobInstanceRepo;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Launch the background process for the given $operation
     *
     * @param BatchableOperationInterface $operation
     *
     * @throws NotFoundResourceException
     */
    public function launch(BatchableOperationInterface $operation)
    {
        $jobInstanceCode = $operation->getJobInstanceCode();
        $jobInstance = $this->jobInstanceRepo->findOneByIdentifier($jobInstanceCode);

        if (null === $jobInstance) {
            throw new NotFoundResourceException(sprintf('No JobInstance found with code "%s"', $jobInstanceCode));
        }

        $user = $this->retrieveUser();

        $configuration = $operation->getBatchConfig();
        $configuration['user_to_notify'] = $user->getUsername();

        $this->jobLauncher->launch($jobInstance, $user, $configuration);
    }

    private function retrieveUser(): UserInterface
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}
