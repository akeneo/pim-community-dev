<?php

namespace Context;

use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Behat\Behat\Context\Context;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectivityContext implements Context, KernelAwareContext
{
    private KernelInterface $kernel;
    private static string $kernelRootDir;
    private TransportInterface $transport;
    private ?array $envelopes = null;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @inheritDoc
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        self::$kernelRootDir = $kernel->getRootDir();
    }

    /**
     * @Given /^(\d+) events of type "([^"]*)" have been raised$/
     */
    public function eventsOfTypeHaveBeenRaised(int $expectedCount, string $type): void
    {
        if (null === $this->envelopes) {
            /** @var Envelope[] envelopes */
            $this->envelopes = $this->transport->get();
        }

        $count = 0;
        foreach ($this->envelopes as $envelope) {
            $payload = $envelope->getMessage();
            if (!$payload instanceof BulkEventInterface) {
                continue;
            }
            foreach ($payload->getEvents() as $event) {
                if ($type === $event->getName()) {
                    $count++;
                }
            }
        }

        Assert::assertEquals($expectedCount, $count);
    }
}

