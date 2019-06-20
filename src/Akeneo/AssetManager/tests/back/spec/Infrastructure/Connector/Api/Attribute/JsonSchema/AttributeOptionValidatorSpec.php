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

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema;

use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\AttributeOptionValidator;
use PhpSpec\ObjectBehavior;

class AttributeOptionValidatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeOptionValidator::class);
    }

    function it_returns_an_empty_array_if_the_option_is_valid()
    {
        $validOptions = [
            'code' => 'Dog',
            'labels' => [
                'en_US' => 'dog'
            ]
        ];

        $this->validate($validOptions)->shouldReturn([]);
    }

    function it_returns_an_empty_array_if_an_option_without_labels_is_valid()
    {
        $validOptions = [
            'code' => 'Dog'
        ];

        $this->validate($validOptions)->shouldReturn([]);
    }

    function it_returns_an_error_when_the_code_is_not_provided()
    {
        $invalidCode = [
            'labels' => [
                'en_US' => 'test'
            ]
        ];

        $errors = $this->validate($invalidCode);
        $errors->shouldBeArray();
        $errors->shouldHaveCount(1);
    }
}
