<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiDebugController
{
    private Client $elasticsearchClient;

    public function __construct(Client $elasticsearchClient)
    {
        $this->elasticsearchClient = $elasticsearchClient;
    }

    public function downloadEventSubscriptionLogs(Request $request): Response
    {
        $connectionCode = $request->query->get('connection_code');

        $results = $this->elasticsearchClient->scroll([
            'query' => [
                'match_all' => new \stdClass()
            ]
        ], 1000);

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'logs.txt'
        );

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set('Content-Disposition', $disposition);

        $response->setCallback(function () use ($results) {
            foreach ($results as $result) {
                foreach ($result['hits']['hits'] as $hit) {
                    echo json_encode($hit['_source']);
                }
                flush();
            }
        });

        return $response;
    }
}
