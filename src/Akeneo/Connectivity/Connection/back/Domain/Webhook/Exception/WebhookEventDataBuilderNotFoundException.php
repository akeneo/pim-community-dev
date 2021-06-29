<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Exception;

use Akeneo\Platform\Component\Webhook\EventBuildingExceptionInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebhookEventDataBuilderNotFoundException extends \RuntimeException implements EventBuildingExceptionInterface
{
    public function __construct(object $event)
    {
        parent::__construct(
            sprintf('Webhook event data builder was not found for class %s', get_class($event))
        );
    }
}
