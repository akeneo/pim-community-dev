<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

final class InstallerEvent extends GenericEvent
{
    /**
     * @param string[] $arguments
     */
    public function __construct(
        ?string $subject = null,
        array $arguments = [],
    ) {
        parent::__construct($subject, $arguments);
    }
}
