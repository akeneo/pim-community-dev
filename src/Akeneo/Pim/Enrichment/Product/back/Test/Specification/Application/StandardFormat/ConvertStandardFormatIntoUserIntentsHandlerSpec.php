<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\StandardFormat;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\Query\GetUserIntentsFromStandardFormat;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\UserIntentFactoryRegistry;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConvertStandardFormatIntoUserIntentsHandlerSpec extends ObjectBehavior
{
    function let(UserIntentFactoryRegistry $userIntentFactoryRegistry)
    {
        $this->beConstructedWith($userIntentFactoryRegistry);
    }

    function it_returns_user_intents(
        UserIntentFactoryRegistry $userIntentFactoryRegistry,
        UserIntent $userIntent1,
        UserIntent $userIntent2,
        UserIntent $userIntent3,
    ) {
        $userIntentFactoryRegistry->fromStandardFormatField('family', 'accessories')
            ->willReturn([$userIntent1]);
        $userIntentFactoryRegistry->fromStandardFormatField('categories', ['print'])
            ->willReturn([$userIntent2]);
        $userIntentFactoryRegistry->fromStandardFormatField('enabled', true)
            ->willReturn([$userIntent3]);
        $userIntentFactoryRegistry->fromStandardFormatField('identifier', 'my-identifier')
            ->willReturn([]);

        $this->__invoke(new GetUserIntentsFromStandardFormat([
            'family' => 'accessories',
            'categories' => ['print'],
            'enabled' => true,
            'identifier' => 'my-identifier',
        ]))->shouldReturn([$userIntent1, $userIntent2, $userIntent3]);
    }
}
