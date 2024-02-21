<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessengerBundle\Stamp;

use Akeneo\Tool\Bundle\MessengerBundle\Stamp\NativeMessageStamp;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NativeMessageStampSpec extends ObjectBehavior
{
    public function let(): void
    {
        $nativeMessage = new \stdClass();
        $this->beConstructedWith($nativeMessage);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(NativeMessageStamp::class);
    }

    public function it_is_a_stamp(): void
    {
        $this->shouldImplement(StampInterface::class);
    }

    public function it_returns_the_native_message(): void
    {
        $nativeMessage = new \stdClass();
        $this->beConstructedWith($nativeMessage);

        $this->getNativeMessage()->shouldReturn($nativeMessage);
    }
}
