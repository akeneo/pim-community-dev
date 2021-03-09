<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query;

use Akeneo\Connectivity\Connection\Domain\Clock;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Elasticsearch\Query\SearchEventSubscriptionDebugLogsQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Encrypter;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PhpSpec\ObjectBehavior;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchEventSubscriptionDebugLogsQuerySpec extends ObjectBehavior
{
    public function let(
        Client $elasticsearchClient,
        Clock $clock,
        Encrypter $encrypter
    ): void {
        $this->beConstructedWith($elasticsearchClient, $clock, $encrypter);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(SearchEventSubscriptionDebugLogsQuery::class);
    }

    public function it_throws_an_exception_when_given_filter_level_are_invalid(): void
    {
        $this
            ->shouldThrow(InvalidOptionsException::class)
            ->during('execute', [
                'erp',
                null,
                ['levels' => 'red']
            ]);
    }
}
