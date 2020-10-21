<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCountPerConnection;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ErrorCountPerConnectionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $errorCount1 = new ErrorCount('erp', 5);
        $errorCount2 = new ErrorCount('ecommerce', 8);

        $this->beConstructedWith(
            [$errorCount1, $errorCount2]
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ErrorCountPerConnection::class);
    }

    public function it_normalizes_the_error_count_per_connection(): void
    {
        $this->normalize()->shouldReturn([
            'erp' => 5,
            'ecommerce' => 8,
        ]);
    }

    public function it_normalizes_when_zero_error_count_per_connection(): void
    {
        $this->beConstructedWith([]);
        $this->normalize()->shouldReturn([]);
    }
}
