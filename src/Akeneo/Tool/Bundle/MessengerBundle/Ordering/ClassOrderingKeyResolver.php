<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Ordering;

use Symfony\Component\Messenger\Envelope;

/**
 * Simple resolver that supports given class and always returns the same ordering key.
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ClassOrderingKeyResolver implements OrderingKeyResolverInterface
{
    private string $class;
    private string $orderingKey;

    public function __construct(string $class, string $orderingKey)
    {
        $this->class = $class;
        $this->orderingKey = $orderingKey;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(Envelope $envelope): bool
    {
        return $envelope->getMessage() instanceof $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Envelope $envelope): ?string
    {
        return $this->orderingKey;
    }
}
