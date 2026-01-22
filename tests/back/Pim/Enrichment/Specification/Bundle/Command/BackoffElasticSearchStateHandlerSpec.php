<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Bundle\Command\BackoffElasticSearchStateHandler;
use Akeneo\Pim\Enrichment\Bundle\Command\BulkEsHandlerInterface;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use PhpSpec\ObjectBehavior;
use PHPStan\Type\Php\ArgumentBasedFunctionReturnTypeExtension;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Response;

class BackoffElasticSearchStateHandlerSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(2, 2);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(BackoffElasticSearchStateHandler::class);
    }

    public function it_will_stop_after_a_403_response(BulkEsHandlerInterface $bulkEsHandler)
    {
        $codes = range(1, 17);
        $bulkEsHandler->bulkExecute(Argument::any())->willThrow(new ClientResponseException("", Response::HTTP_FORBIDDEN));
        $this->shouldThrow(ClientResponseException::class)->during('bulkExecute', [$codes,$bulkEsHandler]);
        $bulkEsHandler->bulkExecute(Argument::any())->shouldHaveBeenCalledOnce();
    }

    public function it_will_make_several_attempts_reducing_batch_size(BulkEsHandlerInterface $bulkEsHandler) {
        $codes = range(1, 17);
        $badRequest400Exception = new ClientResponseException("", Response::HTTP_TOO_MANY_REQUESTS);
        $bulkEsHandler->bulkExecute(Argument::any())->willThrow($badRequest400Exception);
        $this->shouldThrow($badRequest400Exception)->during('bulkExecute', [$codes,$bulkEsHandler]);
        $bulkEsHandler->bulkExecute(Argument::any())->shouldHaveBeenCalledTimes(3);
        $bulkEsHandler->bulkExecute($codes)->shouldHaveBeenCalled();
        $bulkEsHandler->bulkExecute(range(1,8))->shouldHaveBeenCalledOnce();
        $bulkEsHandler->bulkExecute(range(1,4))->shouldHaveBeenCalledOnce();
    }

    public function it_will_reset_decrease_batch_size_after_error_and_reset_after_success(BulkEsHandlerInterface $bulkEsHandler) {
        $codes = range(1, 17);
        $badRequest400Exception = new ClientResponseException("", Response::HTTP_TOO_MANY_REQUESTS);
        $bulkEsHandler->bulkExecute($codes)->willThrow($badRequest400Exception);
        $bulkEsHandler->bulkExecute(range(1,8))->willReturn(8);
        $bulkEsHandler->bulkExecute(range(9,16))->willReturn(8);
        $bulkEsHandler->bulkExecute([17])->willReturn(1);

        $this->bulkExecute($codes,$bulkEsHandler)->shouldReturn(17);

        $bulkEsHandler->bulkExecute(Argument::any())->shouldHaveBeenCalledTimes(4);
        $bulkEsHandler->bulkExecute($codes)->shouldHaveBeenCalledOnce();
        $bulkEsHandler->bulkExecute(range(1,8))->shouldHaveBeenCalledOnce();
        $bulkEsHandler->bulkExecute(range(9,16))->shouldHaveBeenCalledOnce();
        $bulkEsHandler->bulkExecute([17])->shouldHaveBeenCalledOnce();
    }


}
