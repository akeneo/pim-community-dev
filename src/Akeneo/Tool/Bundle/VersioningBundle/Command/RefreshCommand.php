<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Refresh versioning data
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshCommand extends Command
{
    protected static $defaultName = 'pim:versioning:refresh';
    protected static $defaultDescription = 'Version any updated entities';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption(
                'batch-size',
                null,
                InputOption::VALUE_OPTIONAL,
                'flush new versions by using this batch size',
                100
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $batchConfig = [
            'batch_size' => (int)$input->getOption('batch-size'),
        ];

        $command = $this->getApplication()->find('akeneo:batch:job');

        $arguments = new ArrayInput([
            'code' => 'versioning_refresh',
            '--config' => \json_encode($batchConfig),
        ]);

        return $command->run($arguments, $output);
    }
}
