<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\AttributeOption;
use PhpSpec\ObjectBehavior;

final class AttributeOptionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'an_attribute_option_code',
            [
                'en_US' => 'An attribute option',
                'fr_FR' => 'Une option',
            ]
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(AttributeOption::class);
    }

    public function it_returns_the_code(): void
    {
        $this->getCode()->shouldReturn('an_attribute_option_code');
    }

    public function it_returns_the_labels(): void
    {
        $this->getLabels()->shouldReturn([
            'en_US' => 'An attribute option',
            'fr_FR' => 'Une option',
        ]);
    }

    public function it_normalizes_itself(): void
    {
        $this->normalize()->shouldReturn([
            'code' => 'an_attribute_option_code',
            'labels' => [
                'en_US' => 'An attribute option',
                'fr_FR' => 'Une option',
            ]
        ]);
    }
}
