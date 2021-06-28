<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Ordering;

use Symfony\Component\Messenger\Envelope;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OrderingKeySolver
{
    /** @var OrderingKeyResolverInterface[] */
    private iterable $resolvers;

    public function __construct(iterable $resolvers)
    {
        Assert::allImplementsInterface(
            !is_array($resolvers) ? iterator_to_array($resolvers) : $resolvers,
            OrderingKeyResolverInterface::class
        );

        $this->resolvers = $resolvers;
    }

    public function solve(Envelope $envelope): ?string
    {
        foreach ($this->resolvers as $candidate) {
            if ($candidate->supports($envelope)) {
                return $candidate->resolve($envelope);
            }
        }

        return null;
    }
}
