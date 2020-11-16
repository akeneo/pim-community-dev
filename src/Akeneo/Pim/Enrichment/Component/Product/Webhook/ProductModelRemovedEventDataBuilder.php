<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelRemovedEventDataBuilder implements EventDataBuilderInterface
{
    public function supports(object $event): bool
    {
        return $event instanceof ProductModelRemoved;
    }

    /**
     * @param ProductModelRemoved $event
     */
    public function build(object $event, int $userId): array
    {
        if (false === $this->supports($event)) {
            throw new \InvalidArgumentException();
        }

        $data = $event->getData();

        return [
            'resource' => ['code' => $data['code']]
        ];
    }
}
