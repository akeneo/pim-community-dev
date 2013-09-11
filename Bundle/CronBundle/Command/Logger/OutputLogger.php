<?php

namespace Oro\Bundle\CronBundle\Command\Logger;

use Symfony\Component\Console\Output\OutputInterface;

class OutputLogger implements LoggerInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Constructor
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function error($message)
    {
        $this->output->writeln($message);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message)
    {
        if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_QUIET) {
            $this->output->writeln($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL) {
            $this->output->writeln($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function info($message)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message)
    {
        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
            $this->output->writeln($message);
        }
    }
}
