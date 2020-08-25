<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\WebhookEvent;

use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface WebhookEventDataBuilder
{
    /**
     * @param array<mixed> $context
     *
     * @return array<mixed> Normalized data.
     */
    public function build(BusinessEventInterface $businessEvent, array $context): array;

    public function supports(BusinessEventInterface $businessEvent): bool;
}
