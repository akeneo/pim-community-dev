<?php
declare(strict_types=1);

namespace AkeneoTest\Acceptance\Messenger;

use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class InMemorySpyTransportFactory implements TransportFactoryInterface, ResetInterface
{
    /** @var InMemorySpyTransport[] */
    private array $createdTransports = [];

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        return $this->createdTransports[] = new InMemorySpyTransport();
    }

    public function supports(string $dsn, array $options): bool
    {
        return 0 === strpos($dsn, 'in-memory-spy://');
    }

    public function reset()
    {
        foreach ($this->createdTransports as $transport) {
            $transport->reset();
        }
    }

    /**
     * @return InMemorySpyTransport[]
     */
    public function getCreatedTransports(): array
    {
        return $this->createdTransports;
    }
}
