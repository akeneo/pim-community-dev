<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Command;

use Akeneo\Platform\Job\ServiceApi\JobInstance\CreateJobInstance\CreateJobInstanceCommand as CreateJobInstanceCqrsCommand;
use Akeneo\Platform\Job\ServiceApi\JobInstance\CreateJobInstance\CreateJobInstanceHandlerInterface;
use Akeneo\Tool\Component\Batch\Exception\InvalidJobException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Create a JobInstance
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateJobInstanceCommand extends Command
{
    protected static $defaultName = 'akeneo:batch:create-job';

    const EXIT_SUCCESS_CODE = 0;
    const EXIT_ERROR_CODE = 1;

    public function __construct(
        private CreateJobInstanceHandlerInterface $createJobInstanceHandler
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Create a job instance')
            ->addArgument('connector', InputArgument::REQUIRED, 'Connector code')
            ->addArgument('job', InputArgument::REQUIRED, 'Job name')
            ->addArgument('type', InputArgument::REQUIRED, 'Job type')
            ->addArgument('code', InputArgument::REQUIRED, 'Job instance code')
            ->addArgument('config', InputArgument::OPTIONAL, 'Job default parameters')
            ->addArgument('label', InputArgument::OPTIONAL, 'Job instance label');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connector = $input->getArgument('connector');
        $jobName = $input->getArgument('job');
        $type = $input->getArgument('type');
        $code = $input->getArgument('code');
        $label = $input->getArgument('label');
        $label = $label ? $label : $code;
        $jsonConfig = $input->getArgument('config');
        $rawConfig = null === $jsonConfig ? [] : json_decode($jsonConfig, true);

        $command = new CreateJobInstanceCqrsCommand(
            $type,
            $code,
            $label,
            $connector,
            $jobName,
            $rawConfig,
        );

        try {
            $this->createJobInstanceHandler->handle($command);
        } catch (\RuntimeException $e) {
            $output->writeln(
                sprintf(
                    '<error>Job "%s" does not exists.</error>',
                    $jobName
                )
            );

            return self::EXIT_ERROR_CODE;
        } catch (InvalidJobException $e) {
            $output->writeln(
                sprintf(
                    '<error>A validation error occurred while creating the job instance "%s".</error>',
                    $this->getErrorMessages($e->getViolations())
                )
            );

            return self::EXIT_ERROR_CODE;
        } finally {
            return self::EXIT_SUCCESS_CODE;
        }
    }

    /**
     * @param ConstraintViolationListInterface $errors
     *
     * @return string
     */
    protected function getErrorMessages(ConstraintViolationListInterface $errors)
    {
        $errorsStr = '';

        foreach ($errors as $error) {
            $errorsStr .= sprintf("\n  - %s", $error);
        }

        return $errorsStr;
    }
}
