<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCount;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ErrorCountSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'erp',
            5
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ErrorCount::class);
    }

    public function it_normalizes_the_error_count(): void
    {
        $this->normalize()->shouldReturn([
            'connection_code' => 'erp',
            'count' => 5,
        ]);
    }
}
