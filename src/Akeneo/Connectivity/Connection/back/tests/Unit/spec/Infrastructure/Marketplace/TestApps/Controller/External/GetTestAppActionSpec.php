<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Controller\External;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetTestAppActionSpec extends ObjectBehavior
{
    public function it_throws_a_not_found_exception_as_endpoint_is_not_implemented(): void
    {
        $this
            ->shouldThrow(new NotFoundHttpException())
            ->during('__invoke', []);
    }
}
