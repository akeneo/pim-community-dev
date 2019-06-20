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

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Api\ReferenceEntity\JsonSchema;

use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\ReferenceEntity\JsonSchema\ReferenceEntityValidator;
use PhpSpec\ObjectBehavior;

class ReferenceEntityValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceEntityValidator::class);
    }

    function it_does_not_return_any_error_when_the_reference_entity_is_valid()
    {
        $referenceEntity = [
            'code' => 'starck',
            'labels' => [
                'en_US' => 'Philippe Starck'
            ],
            'image' => 'images/starck.png',
            '_links'  => [
                'image_download' => [
                    'href' => 'http://localhost/api/rest/v1/reference-entities-media-files/images/starck.png'
                ]
            ]
        ];

        $this->validate($referenceEntity)->shouldReturn([]);
    }

    function it_is_only_mandatory_to_provide_the_code_of_the_reference_entity()
    {
        $referenceEntity = [
            'code' => 'starck'
        ];

        $this->validate($referenceEntity)->shouldReturn([]);
    }

    function it_returns_an_error_when_code_is_not_a_string()
    {
        $referenceEntity = [
            'code' => []
        ];

        $errors = $this->validate($referenceEntity);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_labels_has_a_wrong_format()
    {
        $referenceEntity = [
            'code' => 'starck',
            'labels' => [
                'en_US' => []
            ]
        ];

        $errors = $this->validate($referenceEntity);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_code_is_not_provided()
    {
        $errors = $this->validate([]);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_an_additional_property_is_filled()
    {
        $referenceEntity = [
            'code' => 'starck',
            'unknown_property' => 'michel'
        ];

        $errors = $this->validate($referenceEntity);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }

    function it_returns_an_error_when_image_is_not_a_string_or_null()
    {
        $referenceEntity = [
            'code' => 'starck',
            'image' => 42
        ];

        $errors = $this->validate($referenceEntity);

        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }
}
