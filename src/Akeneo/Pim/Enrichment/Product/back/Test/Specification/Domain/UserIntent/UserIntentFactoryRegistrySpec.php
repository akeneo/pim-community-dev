<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\UserIntentFactory;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserIntentFactoryRegistrySpec extends ObjectBehavior
{
    function let(UserIntentFactory $userIntentFactory1, UserIntentFactory $userIntentFactory2)
    {
        $userIntentFactory1->getSupportedFieldNames()->willReturn(['family']);
        $userIntentFactory2->getSupportedFieldNames()->willReturn(['categories']);
        $this->beConstructedWith([$userIntentFactory1, $userIntentFactory2], ['identifier']);
    }

    function it_returns_a_user_intent(
        UserIntentFactory $userIntentFactory1,
        UserIntentFactory $userIntentFactory2,
        UserIntent $userIntent,
        UserIntent $userIntent2,
    ) {
        $userIntentFactory1->create('family', 'data')->willReturn([$userIntent]);
        $this->fromStandardFormatField('family', 'data')->shouldReturn([$userIntent]);
        $userIntentFactory2->create('categories', 'data')->willReturn([$userIntent2]);
        $this->fromStandardFormatField('categories', 'data')->shouldReturn([$userIntent2]);
    }

    function it_throws_an_exception_if_fieldname_is_not_supported()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('fromStandardFormatField', ['unknown', 'data']);
    }

    function it_returns_null_if_fieldname_is_ignored()
    {
        $this->fromStandardFormatField('identifier', 'data')->shouldReturn([]);
    }
}
