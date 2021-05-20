<?php

//namespace spec\Akeneo\Pim\Enrichment\Bundle\Command;
namespace Specification\Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Bundle\Command\BackoffElasticSearchStateHandler;
use Akeneo\Pim\Enrichment\Bundle\Command\BulkEsHandlerInterface;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
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
        $bulkEsHandler->bulkExecute(Argument::any())->willThrow(new BadRequest400Exception("", Response::HTTP_FORBIDDEN));
        $this->shouldThrow(BadRequest400Exception::class)->during('bulkExecute', [$codes,$bulkEsHandler]);
        $bulkEsHandler->bulkExecute(Argument::any())->shouldHaveBeenCalledOnce();
    }

    public function it_will_make_several_attempts_reducing_batch_size(BulkEsHandlerInterface $bulkEsHandler) {
        $codes = range(1, 17);
        $badRequest400Exception = new BadRequest400Exception("", Response::HTTP_TOO_MANY_REQUESTS);
        $bulkEsHandler->bulkExecute(Argument::any())->willThrow($badRequest400Exception);
        $this->shouldThrow($badRequest400Exception)->during('bulkExecute', [$codes,$bulkEsHandler]);
        $bulkEsHandler->bulkExecute(Argument::any())->shouldHaveBeenCalledTimes(2);
        $bulkEsHandler->bulkExecute($codes)->shouldHaveBeenCalled();
        $bulkEsHandler->bulkExecute(range(1,8))->shouldHaveBeenCalledOnce();
    }

    public function it_will_reset_decrease_batch_size_after_error_and_reset_after_success(BulkEsHandlerInterface $bulkEsHandler) {
        $codes = range(1, 17);
        $badRequest400Exception = new BadRequest400Exception("", Response::HTTP_TOO_MANY_REQUESTS);
        $bulkEsHandler->bulkExecute(Argument::is($codes))->willThrow($badRequest400Exception);
        $this->bulkExecute($codes,$bulkEsHandler)->shouldReturn(17);
        $bulkEsHandler->bulkExecute(Argument::any())->shouldHaveBeenCalledTimes(3);
        $bulkEsHandler->bulkExecute($codes)->shouldHaveBeenCalledOnce();
        $bulkEsHandler->bulkExecute(range(1,8))->shouldHaveBeenCalledOnce();
        $bulkEsHandler->bulkExecute(range(9,17))->shouldHaveBeenCalledOnce();
    }


}
