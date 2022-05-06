<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Persistence;

use Akeneo\Connectivity\Connection\Domain\ClockInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Encrypter;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventsApiDebug\Persistence\SearchEventSubscriptionDebugLogsQuery;
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
        ClockInterface $clock,
        Encrypter $encrypter
    ): void {
        $this->beConstructedWith($elasticsearchClient, $clock, $encrypter);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(SearchEventSubscriptionDebugLogsQuery::class);
    }

    public function it_throws_an_exception_when_given_level_filter_is_invalid(): void
    {
        $this
            ->shouldThrow(InvalidOptionsException::class)
            ->during('execute', [
                'erp',
                null,
                ['levels' => 'red']
            ]);
    }

    public function it_throws_an_exception_when_given_timestamp_from_filter_is_invalid(): void
    {
        $this
            ->shouldThrow(InvalidOptionsException::class)
            ->during('execute', [
                'erp',
                null,
                ['timestamp_from' => 'not_a_correct_timestamp_from']
            ]);
    }

    public function it_throws_an_exception_when_given_timestamp_to_filter_is_invalid(): void
    {
        $this
            ->shouldThrow(InvalidOptionsException::class)
            ->during('execute', [
                'erp',
                null,
                ['timestamp_to' => 'not_a_correct_timestamp_to']
            ]);
    }

    public function it_throws_an_exception_when_given_text_filter_is_invalid(): void
    {
        $this
            ->shouldThrow(InvalidOptionsException::class)
            ->during('execute', [
                'erp',
                null,
                ['text' => []]
            ]);
    }
}
