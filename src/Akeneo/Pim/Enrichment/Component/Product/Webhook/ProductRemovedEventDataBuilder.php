<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRemovedEventDataBuilder implements EventDataBuilderInterface
{
    public function supports(BusinessEventInterface $businessEvent): bool
    {
        return $businessEvent instanceof ProductRemoved;
    }

    /**
     * @param ProductRemoved $businessEvent
     */
    public function build(BusinessEventInterface $businessEvent): array
    {
        if (!$this->supports($businessEvent)) {
            throw new \InvalidArgumentException();
        }

        $data = $businessEvent->data();

        return [
            'resource' => ['identifier' => $data['identifier']]
        ];
    }
}
