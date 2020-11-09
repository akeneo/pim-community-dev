<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelRemovedEventDataBuilder implements EventDataBuilderInterface
{
    public function supports(BusinessEventInterface $businessEvent): bool
    {
        return $businessEvent instanceof ProductModelRemoved;
    }

    /**
     * @param ProductModelRemoved $businessEvent
     */
    public function build(BusinessEventInterface $businessEvent, int $userId): array
    {
        if (false === $this->supports($businessEvent)) {
            throw new \InvalidArgumentException();
        }

        $data = $businessEvent->data();

        return [
            'resource' => ['code' => $data['code']]
        ];
    }
}
