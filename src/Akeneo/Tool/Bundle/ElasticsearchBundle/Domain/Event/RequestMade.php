<?php

declare(strict_types=1);

/*
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Domain\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class RequestMade extends Event
{
    public const NAME = 'es.request_made';

    public function __construct(
        public string $type,
        public array $request,
        public array $response
    ) {
    }
}
