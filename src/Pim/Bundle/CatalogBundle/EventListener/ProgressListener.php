<?php

namespace Pim\Bundle\CatalogBundle\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pim\Bundle\CatalogBundle\Event\ProgressEvent;
use Symfony\Component\Console\Helper\ProgressHelper;

/**
 * Progress listener for commands
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProgressListener
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Creates progress bars for a specified event
     *
     * @param OutputInterface $output
     * @param string          $eventName
     * @param string          $message
     */
    public function bind(OutputInterface $output, $eventName, $message = '')
    {
        $progress = null;

        $this->eventDispatcher->addListener(
            $eventName,
            function (ProgressEvent $event) use ($output, &$progress, $message) {
                if (0 == $event->getTreatedItems()) {
                    if (null !== $progress) {
                        $progress->finish();
                    }
                    if ($message) {
                        $output->writeln(sprintf($message, $event->getSection()));
                    }
                    $progress = new ProgressHelper;
                    $progress->start($output, $event->getTotalItems());
                } else {
                    $progress->advance();
                    if ($event->getTotalItems() == $event->getTreatedItems()) {
                        $progress->finish();
                        $progress = null;
                    }
                }
            }
        );
    }
}
