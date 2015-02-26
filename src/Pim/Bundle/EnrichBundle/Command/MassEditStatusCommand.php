<?php

namespace Pim\Bundle\EnrichBundle\Command;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Job\BatchStatus;
use Akeneo\Bundle\BatchBundle\Job\ExitStatus;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Command for mass status products
 * TODO: Rename this class / file to be more generic to product mass edit actions
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditStatusCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:mass-edit:run-job')
            ->addArgument('execution', InputArgument::REQUIRED, 'Job execution id')
            ->setDescription('Mass edit products');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $executionId = $input->getArgument('execution');

        if ($executionId) {
            /** @var JobExecution $jobExecution */
            $jobExecution = $this->getJobManager()->getRepository('AkeneoBatchBundle:JobExecution')->find($executionId);

            if (!$jobExecution) {
                throw new \InvalidArgumentException(sprintf('Could not find job execution "%s".', $executionId));
            }

            if (!$jobExecution->getStatus()->isStarting()) {
                throw new \RuntimeException(
                    sprintf('Job execution "%s" has invalid status: %s', $executionId, $jobExecution->getStatus())
                );
            }
        }

        $jobInstance = $jobExecution->getJobInstance();
        $jobRawConfiguration = $jobInstance->getRawConfiguration();

        /** @var ProductMassEditOperation $operation */
        $operation = $this->getOperation($jobRawConfiguration);

        $filters = json_decode($jobRawConfiguration['filters'], true);
        $operationConfig = json_decode($jobRawConfiguration['config'], true);

        $operation->setPqbFilters($filters);
        $jobExecution->setStartTime(new \DateTime());
        $this->updateStatus($jobExecution, BatchStatus::STARTED);
        // TODO: Retrieve operation specific configuration
        $operation->setConfiguration($operationConfig);
        $operation->perform();
        $jobExecution->setExitStatus(new ExitStatus(ExitStatus::COMPLETED));
    }

    /**
     * Default mapping from throwable to {@link ExitStatus}. Clients can modify the exit code using a
     * {@link StepExecutionListener}.
     *
     * @param JobExecution $jobExecution Execution of the job
     * @param string       $status       Status of the execution
     *
     * @return an {@link ExitStatus}
     */
    private function updateStatus(JobExecution $jobExecution, $status)
    {
        $jobExecution->setStatus(new BatchStatus($status));
    }

    /**
     * @param $rawConfiguration
     *
     * @return MassEditActionOperator
     */
    protected function getOperation($rawConfiguration)
    {
        // TODO: Find a way to handle security context
        $userManager = $this->getContainer()->get('oro_user.manager');
        $user = $userManager->findUserByUsername('admin');

        // create the authentication token
        $token = new UsernamePasswordToken(
            $user,
            null,
            'main',
            $user->getRoles()
        );

        // give it to the security context
        $this->getContainer()->get('security.context')->setToken($token);

        $operatorRegistry = $this->getContainer()->get('pim_enrich.mass_edit_action.operator.registry');

        $operator = $operatorRegistry->getOperator($rawConfiguration['gridName']);
        $operator->setOperationAlias($rawConfiguration['operationAlias']);

        return $operator->getOperation();
    }

    /**
     * @return EntityManager
     */
    protected function getJobManager()
    {
        return $this->getContainer()->get('akeneo_batch.job_repository')->getJobManager();
    }
}
