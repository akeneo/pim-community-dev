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

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\Create;

use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\Create\AttributeCreationValidator;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\Create\ImageAttributeValidator;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Attribute\JsonSchema\Create\OptionAttributeValidator;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AttributeCreationValidatorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([new ImageAttributeValidator(), new OptionAttributeValidator()]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeCreationValidator::class);
    }

    function it_validates_an_image_attribute()
    {
        $this->validate(['type' => 'image'])->shouldBeArray();
    }

    function it_validates_an_option_attribute()
    {
        $this->validate(['type' => 'single_option'])->shouldBeArray();
    }

    function it_validates_an_option_collection_attribute()
    {
        $this->validate(['type' => 'multiple_options'])->shouldBeArray();
    }

    function it_triggers_an_exception_when_attribute_type_is_unknown()
    {
        $this->shouldThrow(UnprocessableEntityHttpException::class)
            ->during('validate', [['type' => 'unknown']]);
    }

    function it_triggers_an_exception_when_attribute_type_is_not_a_string()
    {
        $this->shouldThrow(UnprocessableEntityHttpException::class)
            ->during('validate', [['type' => 1]]);
    }

    function it_triggers_an_exception_when_attribute_type_is_not_provided()
    {
        $this->shouldThrow(UnprocessableEntityHttpException::class)
            ->during('validate', [[]]);
    }
}
