<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Category\Domain\UserIntent;

use Akeneo\Category\Domain\UserIntent\Factory\UserIntentFactory;
use PhpSpec\ObjectBehavior;

class UserIntentFactoryRegistrySpec extends ObjectBehavior
{
    function let(
        UserIntentFactory $userIntentFactory
    )
    {
        $this->beConstructedWith(
            ['field' => $userIntentFactory],
            ['ignored_field']
        );
    }

    function it_creates_a_user_intent_from_standard_format(
        UserIntentFactory $userIntentFactory
    )
    {
        $userIntentFactory->getSupportedFieldNames()->willReturn(['field']);
        $userIntentFactory->create('field', 1, ['key' => 'value', 'ignored_field' => 'another_value'])->shouldBeCalled();

        $this->fromStandardFormatField('field', 1, ['key' => 'value', 'ignored_field' => 'another_value']);
    }

    function it_throws_an_exception_when_no_factory_is_found(
        UserIntentFactory $userIntentFactory
    )
    {
        $userIntentFactory->getSupportedFieldNames()->willReturn(['field']);
        $userIntentFactory->create('unknown_field', 1, ['key' => 'value'])->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('fromStandardFormatField', ['unknown_field', 1, ['key' => 'value']]);
    }
}
