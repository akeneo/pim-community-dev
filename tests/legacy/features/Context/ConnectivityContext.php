<?php
declare(strict_types=1);

namespace Context;

use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Behat\Behat\Context\Context;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectivityContext implements Context, KernelAwareContext
{
    private KernelInterface $kernel;
    private TransportInterface $transport;
    private array $envelopes = [];

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
    }

    /**
     * @Given /^(\d+) event(?:s|) of type "([^"]*)" should have been raised$/
     */
    public function eventsOfTypeShouldHaveBeenRaised(int $expectedCount, string $type): void
    {
        while (true) {
            $envelopes = $this->transport->get();
            if (empty($envelopes)) {
                break;
            }
            foreach ($envelopes as $envelope) {
                $this->transport->ack($envelope);
                $this->envelopes[] = $envelope;
            }
        }

        $count = 0;
        foreach ($this->envelopes as $envelope) {
            $payload = $envelope->getMessage();
            if (!$payload instanceof BulkEventInterface) {
                continue;
            }
            foreach ($payload->getEvents() as $event) {
                if ($event->getName() === $type) {
                    $count++;
                }
            }
        }

        Assert::assertEquals($expectedCount, $count);
    }
}
