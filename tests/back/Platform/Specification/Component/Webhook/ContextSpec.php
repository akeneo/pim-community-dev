<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Component\Webhook;

use Akeneo\Platform\Component\Webhook\Context;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EventDataCollectionSpec extends ObjectBehavior
{
    public function it_is_a_context(): void
    {
        $this->shouldBeAnInstanceOf(Context::class);
    }
}
