<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Webhook;

use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EventDataBuilderInterface
{
    public function supports(BulkEventInterface $event): bool;

    /**
     * @return array<mixed> Normalized data.
     */
    public function build(BulkEventInterface $event, UserInterface $user): EventDataCollection;
}
