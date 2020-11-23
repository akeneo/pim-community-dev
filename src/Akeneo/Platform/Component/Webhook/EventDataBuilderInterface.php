<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Webhook;

use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\EventQueue\EventInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EventDataBuilderInterface
{
    /**
     * @param EventInterface|BulkEventInterface $event
     */
    public function supports(object $event): bool;

    /**
     * @param EventInterface|BulkEventInterface $event
     *
     * @return array<mixed> Normalized data.
     */
    public function build(object $event, UserInterface $user): EventDataCollection;
}
