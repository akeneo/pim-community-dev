<?php

namespace Pim\Bundle\ConnectorBundle\Command;

use Akeneo\Bundle\BatchBundle\Command\BatchCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Batch command to launch an job with an authenticated user.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class AuthenticatedBatchCommand extends BatchCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:batch:job')
            ->setDescription('Launch a registered job instance with an authenticated user.')
            ->addArgument('code', InputArgument::REQUIRED, 'Job instance code')
            ->addArgument('username', InputArgument::REQUIRED, 'User to use when running the job')
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Override job configuration (formatted as json. ie: ' .
                'php bin/console akeneo:batch:job -c "{\"filePath\":\"/tmp/foo.csv\"}" acme_product_import)'
            )
            ->addOption(
                'email',
                null,
                InputOption::VALUE_REQUIRED,
                'The email to notify at the end of the job execution'
            )
            ->addOption(
                'no-log',
                null,
                InputOption::VALUE_NONE,
                'Don\'t display logs'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = null !== $input->getOption('config') ? json_decode($input->getOption('config'), true) : [];
        $configuration['is_user_authenticated'] = true;

        $encodedConfiguration = json_encode($configuration);

        $this->addOption('username', null, InputOption::VALUE_REQUIRED);

        $input->setOption('config', $encodedConfiguration);
        $input->setOption('username', $input->getArgument('username'));

        parent::execute($input, $output);
    }
}
