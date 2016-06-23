<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction;

use Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ConfigurableOperationInterface;
use Pim\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * Background process launcher for mass edit Operations.
 * It internally uses the Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OperationJobLauncher
{
    /** @var SimpleJobLauncher */
    protected $jobLauncher;

    /** @var JobInstanceRepository */
    protected $jobInstanceRepo;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param SimpleJobLauncher     $jobLauncher
     * @param JobInstanceRepository $jobInstanceRepo
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        SimpleJobLauncher $jobLauncher,
        JobInstanceRepository $jobInstanceRepo,
        TokenStorageInterface $tokenStorage
    ) {
        $this->jobLauncher     = $jobLauncher;
        $this->jobInstanceRepo = $jobInstanceRepo;
        $this->tokenStorage    = $tokenStorage;
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
        $jobCode = $operation->getBatchJobCode();
        $jobInstance = $this->jobInstanceRepo->findOneBy(['code' => $jobCode]);

        if (null === $jobInstance) {
            throw new NotFoundResourceException(sprintf('No job found with job code "%s"', $jobCode));
        }

        if ($operation instanceof ConfigurableOperationInterface) {
            $operation->finalize();
        }

        $configuration = $operation->getBatchConfig();
        $this->jobLauncher->launch(
            $jobInstance,
            $this->tokenStorage->getToken()->getUser(),
            $configuration
        );
    }
}
