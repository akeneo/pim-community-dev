<?php

namespace spec\Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Bundle\Command\BackoffElasticSearchStateHandler;
use Akeneo\Pim\Enrichment\Bundle\Command\BulkEsHandlerInterface;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use PhpSpec\ObjectBehavior;
use PHPStan\Type\Php\ArgumentBasedFunctionReturnTypeExtension;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Response;

class BackoffElasticSearchStateHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BackoffElasticSearchStateHandler::class);
    }

    public function it_will_stop_after_a_403_response(BulkEsHandlerInterface $bulkEsHandler)
    {
        $codes = range(1, 17);

        $notFoundException = new BadRequest400Exception("",Response::HTTP_NOT_FOUND);
        $bulkEsHandler->bulkExecute(Argument::any())->willThrow($notFoundException);

        $this->bulkExecute($codes, $bulkEsHandler)($notFoundException);
    }

}
