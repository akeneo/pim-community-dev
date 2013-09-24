<?php

namespace Oro\Bundle\CronBundle\Command\Logger;

use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class OutputLogger extends AbstractLogger
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

    public function log($level, $message, array $context = array())
    {
        switch ($level) {
            case LogLevel::WARNING:
            case LogLevel::NOTICE:
                if ($this->output->getVerbosity() < OutputInterface::VERBOSITY_NORMAL) {
                    return;
                }
                break;
            case LogLevel::INFO:
                if ($this->output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
                    return;
                }
                break;
            case LogLevel::DEBUG:
                if ($this->output->getVerbosity() < OutputInterface::VERBOSITY_DEBUG) {
                    return;
                }
                break;
        }

        $this->output->writeln(sprintf('[%s] %s', $level, $message));
        if (!empty($context)) {
            foreach ($context as $item) {
                if (is_object($item)) {
                    if ($item instanceof \Exception) {
                        $this->output->writeln($item);
                    }
                } else {
                    $this->output->writeln($item);
                }
            }
        }
    }
}
