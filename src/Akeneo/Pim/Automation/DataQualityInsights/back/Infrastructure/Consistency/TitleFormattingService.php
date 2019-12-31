<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text\TitleFormattingServiceInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\UnableToProvideATitleSuggestion;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductTitle;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

final class TitleFormattingService implements TitleFormattingServiceInterface
{
    /** @var ClientInterface */
    private $client;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(ClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function format(ProductTitle $title): ProductTitle
    {
        try {
            //TODO Add JWT token to be authenticated/identified @DAPI-710
            $response = $this->client->request('GET', 'api/data-quality-insights/title', [
                    'query' => [
                        'title' => $title->__toString()
                    ]
                ]
            );

            $body = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() !== Response::HTTP_OK || empty($body['suggestion'])) {
                $this->logger->error('An error occurred while trying to provide a title suggestion.', [
                    'http_response_status_code' => $response->getStatusCode(),
                    'title' => $title->__toString()
                ]);

                throw new UnableToProvideATitleSuggestion();
            }

            return new ProductTitle($body['suggestion']);
        } catch (GuzzleException $e) {
            $this->logger->error('An error occurred while trying to provide a title suggestion.', [
                'exception' => $e->getMessage(),
                'title' => $title->__toString()
            ]);

            throw new UnableToProvideATitleSuggestion();
        }
    }
}
