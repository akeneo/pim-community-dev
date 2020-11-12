<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRemovedEventDataBuilder implements EventDataBuilderInterface
{
    public function supports(object $businessEvent): bool
    {
        return $businessEvent instanceof ProductRemoved;
    }

    /**
     * @param ProductRemoved $businessEvent
     */
    public function build(object $businessEvent, int $userId): array
    {
        if (false === $this->supports($businessEvent)) {
            throw new \InvalidArgumentException();
        }

        $data = $businessEvent->getData();

        return [
            'resource' => ['identifier' => $data['identifier']]
        ];
    }
}
