<?php


namespace Akeneo\Pim\Enrichment\Bundle\Queries;


use Akeneo\Queries\GetProductQuery;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

class GetProductHandler implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        yield GetProductQuery::class => [
             'bus' => 'query.bus',
          ];
    }

    public function __invoke(GetProductQuery $getProductQuery)
    {
        // TODO: Implement __invoke() method.
        return "pouet";
    }
}