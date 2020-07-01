<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\HrefMessageParameter;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\MessageParameterInterface;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\MessageParameterTypes;
use PhpSpec\ObjectBehavior;

class HrefMessageParameterSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'What is an attribute?',
            'https://help.akeneo.com/what-is-an-attribute.html'
        );
    }

    public function it_is_a_href_message_parameter(): void
    {
        $this->shouldHaveType(HrefMessageParameter::class);
        $this->shouldImplement(MessageParameterInterface::class);
    }

    public function it_normalizes_information(): void
    {
        $this->normalize()->shouldReturn([
            'type' => MessageParameterTypes::HREF,
            'href' => 'https://help.akeneo.com/what-is-an-attribute.html',
            'title' => 'What is an attribute?',
        ]);
    }

    public function it_validates_the_href(): void
    {
        $this->beConstructedWith(
            'What is an attribute?',
            'help.akeneo.com/what-is-an-attribute.html'
        );
        $this
            ->shouldThrow(
                new \InvalidArgumentException(sprintf(
                    'Class "%s" need an URL as href argument, "%s" given.',
                    HrefMessageParameter::class,
                    'help.akeneo.com/what-is-an-attribute.html'
                ))
            )
            ->duringInstantiation();
    }
}
