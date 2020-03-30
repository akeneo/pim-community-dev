<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\HourlyEventCount;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class HourlyEventCountSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(DateTime::createFromFormat('', '2019-12-03 15:00:00'), 5);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(HourlyEventCount::class);
    }
}
