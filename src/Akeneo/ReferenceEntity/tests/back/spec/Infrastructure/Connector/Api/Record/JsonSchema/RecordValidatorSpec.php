<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\RecordPropertiesValidator;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\RecordValidator;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\RecordValuesValidator;
use PhpSpec\ObjectBehavior;

class RecordValidatorSpec extends ObjectBehavior
{
    function let(RecordPropertiesValidator $recordPropertiesValidator, RecordValuesValidator $recordValuesValidator)
    {
        $this->beConstructedWith($recordPropertiesValidator, $recordValuesValidator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordValidator::class);
    }

    function it_validates_both_properties_and_values_of_a_valid_record($recordPropertiesValidator, $recordValuesValidator)
    {
        $record = [
            'code' => 'starck',
            'labels' => [
                'en_US' => 'Philippe Starck'
            ],
            'image' => null,
            'values' => [
                'nationality' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'data'    => 'French'
                    ],
                ]
            ]
        ];

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $recordPropertiesValidator->validate($record)->shouldBeCalled()->willReturn([]);
        $recordValuesValidator->validate($referenceEntityIdentifier, $record)->shouldBeCalled()->willReturn([]);

        $this->validate($referenceEntityIdentifier, $record)->shouldReturn([]);
    }

    function it_returns_errors_of_invalid_values_if_properties_are_valid($recordPropertiesValidator, $recordValuesValidator)
    {
        $record = [
            'code' => 'starck',
            'labels' => [
                'en_US' => 'Philippe Starck'
            ],
            'image' => null,
            'values' => [
                'nationality' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'data'    => 42
                    ],
                ]
            ]
        ];

        $errors = [[
            'property' => 'values.nationality[0].data',
            'message'  => 'Integer value found, but a string or a null is required',
        ]];

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $recordPropertiesValidator->validate($record)->willReturn([]);
        $recordValuesValidator->validate($referenceEntityIdentifier, $record)->willReturn($errors);

        $this->validate($referenceEntityIdentifier, $record)->shouldReturn($errors);
    }

    function it_does_not_validate_values_if_the_are_invalid_properties(
        $recordPropertiesValidator,
        $recordValuesValidator
    ) {
        $record = [
            'code' => 'starck',
            'labels' => [
                'en_US' => 'Philippe Starck'
            ],
            'image' => null,
            'values' => [
                'foo' => 'bar',
                'nationality' => [
                    [
                        'channel' => null,
                        'locale'  => 'en_US',
                        'data'    => 'French'
                    ],
                ]
            ]
        ];

        $errors = [[
            'property' => 'values.foo',
            'message'  => 'String value found, but an array is required',
        ]];

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $recordPropertiesValidator->validate($record)->shouldBeCalled()->willReturn($errors);
        $recordValuesValidator->validate($referenceEntityIdentifier, $record)->shouldNotBeCalled();

        $this->validate($referenceEntityIdentifier, $record)->shouldReturn($errors);
    }
}
