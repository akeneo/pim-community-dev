<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer\ACLReferenceEntityExists;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\Test\Pim\TableAttribute\Helper\FeatureHelper;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ACLReferenceEntityExistsSpec extends ObjectBehavior
{
    function let($referenceEntityExists)
    {
        FeatureHelper::skipSpecTestWhenReferenceEntityIsNotActivated();

        $referenceEntityExists->beADoubleOf(ReferenceEntityExistsInterface::class);
        $this->beConstructedWith($referenceEntityExists);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ACLReferenceEntityExists::class);
    }

    function it_returns_false_when_reference_entity_does_not_exist($referenceEntityExists)
    {
        $referenceEntityExists->withIdentifier('unknown')->willReturn(false);

        $this->forIdentifier('unknown')->shouldReturn(false);
    }

    function it_returns_false_when_reference_entity_identifier_cannot_be_instantiated($referenceEntityExists)
    {
        $referenceEntityExists->withIdentifier(Argument::any())->shouldNotBeCalled();

        $this->forIdentifier('+=&*)')->shouldReturn(false);
    }

    function it_returns_true_when_reference_entity_exists($referenceEntityExists)
    {
        $referenceEntityExists->withIdentifier('brand')->willReturn(true);

        $this->forIdentifier('brand')->shouldReturn(true);
    }
}
