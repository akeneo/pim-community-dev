<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\EnabledUserIntentFactory;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class EnabledUserIntentFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(EnabledUserIntentFactory::class);
    }

    public function it_returns_a_set_enabled_user_intent()
    {
        $this->create('enabled', true)
            ->shouldBeLike([new SetEnabled(true)]);
    }

    public function it_throws_an_error_when_data_is_invalid()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['enabled', 10]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['enabled', null]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['enabled', 'toto']);
    }
}
