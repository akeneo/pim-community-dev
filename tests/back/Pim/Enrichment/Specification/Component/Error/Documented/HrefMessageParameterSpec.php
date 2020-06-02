<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\Documented;

use Akeneo\Pim\Enrichment\Component\Error\Documented\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\Documented\MessageParameterInterface;
use Akeneo\Pim\Enrichment\Component\Error\Documented\MessageParameterTypes;
use PhpSpec\ObjectBehavior;

class HrefMessageParameterSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'What is an attribute?',
            'https://help.akeneo.com/what-is-an-attribute.html',
            '{what_is_attribute}'
        );
    }

    public function it_is_a_href_message_parameter(): void
    {
        $this->shouldHaveType(HrefMessageParameter::class);
        $this->shouldImplement(MessageParameterInterface::class);
    }

    public function it_provides_a_needle(): void
    {
        $this->needle()->shouldReturn('{what_is_attribute}');
    }

    public function it_normalizes_information(): void
    {
        $this->normalize()->shouldReturn([
            'type' => MessageParameterTypes::HREF,
            'href' => 'https://help.akeneo.com/what-is-an-attribute.html',
            'title' => 'What is an attribute?',
            'needle' => '{what_is_attribute}',
        ]);
    }

    public function it_validates_that_the_needle_has_the_good_format(): void
    {
        $wrongMatches = [
            '{}',
            'what_is_attribute',
            '{what_is_attribute',
            'what_is_attribute}',
            '{what_is{_attribute}',
            '{what_is}_attribute}',
        ];
        foreach ($wrongMatches as $wrongMatch) {
            $this->beConstructedWith(
                'What is an attribute?',
                'https://help.akeneo.com/what-is-an-attribute.html',
                $wrongMatch
            );
            $this
                ->shouldThrow(
                    new \InvalidArgumentException(sprintf(
                        '$needle must be a string surrounded by "{needle}", "%s" given.',
                        $wrongMatch
                    ))
                )
                ->duringInstantiation();
        }
    }

    public function it_validates_the_href(): void
    {
        $this->beConstructedWith(
            'What is an attribute?',
            'help.akeneo.com/what-is-an-attribute.html',
            '{what_is_attribute}'
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
