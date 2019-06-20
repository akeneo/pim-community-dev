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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\RecordValueValidatorRegistry;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\Value\OptionTypeValidator;
use Akeneo\ReferenceEntity\Infrastructure\Connector\Api\Record\JsonSchema\Value\TextTypeValidator;
use PhpSpec\ObjectBehavior;

class RecordValueValidatorRegistrySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            new TextTypeValidator(),
            new OptionTypeValidator()
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordValueValidatorRegistry::class);
    }

    function it_returns_the_record_value_validator_for_a_given_attribute_type()
    {
        $this->getValidator(TextAttribute::class)->shouldReturnAnInstanceOf(TextTypeValidator::class);
    }

    function it_throws_an_exception_if_no_validator_was_found()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('getValidator', ['Foo']);
    }
}
