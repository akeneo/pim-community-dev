<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Persistence\PurgeConnectionErrorsQuery;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PurgeConnectionErrorsQuerySpec extends ObjectBehavior
{
    public function let(Client $elastisearch): void
    {
        $this->beConstructedWith($elastisearch);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(PurgeConnectionErrorsQuery::class);
    }

    public function it_does_nothing_if_there_is_no_connections_to_purge($elastisearch): void
    {
        $elastisearch->msearch(Argument::any())->shouldNotBeCalled();

        $this->execute([]);
    }
}
