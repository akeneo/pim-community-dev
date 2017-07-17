<?php


namespace Akeneo\Bundle\BatchBundle\EventListener;

use Akeneo\Component\Batch\Event\BatchCommandEvent;
use Akeneo\Component\Batch\Event\EventInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\LockHandler;

/**
 * Subscriber to lock and unlock commands
 *
 * @author    Benoit Wannepain <benoit.wannepain@kaliop.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class LockSubscriber implements EventSubscriberInterface
{
    const AKENEO_BATCH_JOB = 'akeneo:batch:job';

    /** @var array */
    protected $managedJobCodes;

    /** @var LockHandler */
    protected $lockHandler;

    /**
     * LockSubscriber constructor.
     */
    public function __construct()
    {
        $this->managedJobCodes = [];
    }

    /**
     * @param string $jobCode
     */
    public function registerJobCode($jobCode)
    {
        $this->managedJobCodes[] = $jobCode;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            EventInterface::BATCH_COMMAND_START => 'onCommandStart',
            EventInterface::BATCH_COMMAND_TERMINATE => 'onCommandTerminate',
        ];
    }

    /**
     * @param BatchCommandEvent $event
     */
    public function onCommandStart(BatchCommandEvent $event)
    {
        if (false === $this->supportsCommand($event)) {
            return;
        }

        $output = $event->getOutput();
        $this->lockHandler = new LockHandler(md5($this->getJobName($event)) . '.lock');
        if (!$this->lockHandler->lock()) {
            $output->writeln('<error>Command already locked</error>');
            $event->disableCommand();
        } else {
            $output->writeln('<info>Command locked</info>');
        }
    }

    /**
     * @param BatchCommandEvent $event
     */
    public function onCommandTerminate(BatchCommandEvent $event)
    {
        if (false === $this->supportsCommand($event) || null === $this->lockHandler) {
            return;
        }

        $this->lockHandler->release();
        $output = $event->getOutput();
        $output->writeln('<info>Command unlocked</info>');
    }

    /**
     * @param BatchCommandEvent $event
     * @return bool
     */
    protected function supportsCommand(BatchCommandEvent $event)
    {
        if ($event->getCommand()->getName() !== self::AKENEO_BATCH_JOB) {
            return false;
        }

        $input = $event->getInput();
        if ($input->hasOption('no-lock') && $input->getOption('no-lock')) {
            return false;
        }

        return in_array($this->getJobName($event), $this->managedJobCodes);
    }

    /**
     * @param BatchCommandEvent $event
     * @return string
     */
    protected function getJobName(BatchCommandEvent $event)
    {
        return $event->getJobInstance()->getJobName();
    }
}
