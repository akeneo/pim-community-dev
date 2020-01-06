<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention as DomainNamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NullNamingConvention;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator\ConnectorNamingConventionHydrator;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\NamingConvention;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConnectorNamingConventionHydratorSpec extends ObjectBehavior
{
    function let(ValidatorInterface $validator)
    {
        $this->beConstructedWith($validator);
    }

    function it_is_a_naming_convention_hydrator()
    {
        $this->shouldHaveType(ConnectorNamingConventionHydrator::class);
    }

    function it_hydrates_a_valid_normalized_naming_convention(ValidatorInterface $validator)
    {
        $normalizedNamingConvention = [
            'source' => [
                'property' => 'code',
                'channel' => null,
                'locale' => null,
            ],
            'pattern' => '/^(?P<product>\w+)-(.*)_(?P<attr>\w+)\.png$/',
            'strict' => false,
        ];

        $validator->validate($normalizedNamingConvention, Argument::type(NamingConvention::class))->willReturn(
            new ConstraintViolationList()
        );

        $this->hydrate($normalizedNamingConvention, AssetFamilyidentifier::fromString('packshot'))
             ->shouldBeLike(DomainNamingConvention::createFromNormalized($normalizedNamingConvention));
    }

    function it_hydrates_a_non_valid_normalized_naming_convention(
        ValidatorInterface $validator,
        ConstraintViolationList $violations
    ) {
        $normalizedNamingConvention = [
            'source' => [
                'property' => 'removed_attribute',
                'channel' => null,
                'locale' => null,
            ],
            'pattern' => '/^(?P<product>\w+)-(.*)_(?P<attr>\w+)\.png$/',
            'strict' => false,
        ];

        $violations->count()->willReturn(1);
        $validator->validate($normalizedNamingConvention, Argument::type(NamingConvention::class))
                  ->willReturn($violations);

        $this->hydrate($normalizedNamingConvention, AssetFamilyidentifier::fromString('packshot'))
             ->shouldBeLike(new NullNamingConvention());
    }
}
