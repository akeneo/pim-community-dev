<?php

namespace Akeneo\Component\Batch\Event;

use Akeneo\Component\Batch\Model\JobInstance;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author    Benoit Wannepain <benoit.wannepain@kaliop.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BatchCommandEvent extends ConsoleCommandEvent
{
    /** @var JobInstance  */
    private $jobInstance;

    /**
     * BatchCommandEvent constructor.
     * @param Command $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param JobInstance $jobInstance
     */
    public function __construct(
        Command $command,
        InputInterface $input,
        OutputInterface $output,
        JobInstance $jobInstance
    ) {
        parent::__construct($command, $input, $output);

        $this->jobInstance = $jobInstance;
    }

    /**
     * @return JobInstance
     */
    public function getJobInstance()
    {
        return $this->jobInstance;
    }
}
