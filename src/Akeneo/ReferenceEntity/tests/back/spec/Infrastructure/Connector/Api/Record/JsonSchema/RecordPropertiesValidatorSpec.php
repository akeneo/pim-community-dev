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

use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\RecordPropertiesValidator;
use PhpSpec\ObjectBehavior;

class RecordPropertiesValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RecordPropertiesValidator::class);
    }

    function it_returns_all_the_errors_of_invalid_record_properties()
    {
        $record = [
            'labels' => [
                'en_US' => null,
                'fr_FR' => 42
            ],
            'main_image' => ['image1', 'image2'],
            'values' => null,
            'foo' => 'bar',
        ];

        $errors = $this->validate($record);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(6);
    }

    function it_returns_an_empty_array_if_all_the_record_properties_are_valid()
    {
        $record = [
            'code' => 'starck',
            'labels' => [
                'en_US' => 'Philippe Starck',
            ],
            'main_image' => null,
            'values' => [
                'favorite_color' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => 'blue'
                    ],
                ],
            ],
        ];

        $this->validate($record)->shouldReturn([]);
    }
}
