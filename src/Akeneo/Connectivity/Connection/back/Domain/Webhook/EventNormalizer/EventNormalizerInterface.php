<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\EventNormalizer;

use Akeneo\Platform\Component\EventQueue\EventInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EventNormalizerInterface
{
    public function supports(EventInterface $event): bool;

    /**
     * @return array<mixed>
     */
    public function normalize(EventInterface $event): array;
}
