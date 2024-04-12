<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Domain\Model\ElasticsearchProjection;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Exception\IndexationException;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Exception\MissingIdentifierException;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Elasticsearch\Client as NativeClient;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Elasticsearch\Common\Exceptions\Conflict409Exception;
use Ramsey\Uuid\Uuid;

/**
 * Wrapper for the PHP Elasticsearch client.
 * To learn more, please see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/index.html}.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Client
{
    /** Number of split requests when retrying bulk index */
    private const NUMBER_OF_BATCHES_ON_RETRY = 2;

    private ClientBuilder $builder;
    private Loader $configurationLoader;
    private array $hosts;
    private string $indexName;
    private NativeClient $client;
    private string $idPrefix;
    private int $maxChunkSize;
    private int $maxExpectedIndexationLatencyInMicroseconds;
    private int $maxNumberOfRetries;


    /**
     * Configure the PHP Elasticsearch client.
     * To learn more, please see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html}
     */
    public function __construct(
        ClientBuilder $builder,
        Loader $configurationLoader,
        array $hosts,
        string $indexName,
        string $idPrefix = '',
        int $maxChunkSize = 100000000,
        int $maxExpectedIndexationLatencyInMilliseconds=0,
        int $maxNumberOfRetries=3
    ) {
        $this->builder = $builder;
        $this->configurationLoader = $configurationLoader;
        $this->hosts = $hosts;
        $this->indexName = $indexName;
        $this->idPrefix = $idPrefix;
        $this->maxChunkSize = $maxChunkSize;
        $this->maxExpectedIndexationLatencyInMicroseconds = $maxExpectedIndexationLatencyInMilliseconds*1000;
        $this->maxNumberOfRetries = $maxNumberOfRetries;

        $builder->setHosts($hosts);
        $sslCa = getenv('APP_INDEX_SSL_CA');
        if (isset($sslCa))
        {
            $builder->setSSLVerification($sslCa);
        }

        $this->client = $builder->build();
    }

    /**
     * @param string       $id
     * @param array        $body
     * @param Refresh|null $refresh
     *
     * @throws IndexationException
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_index_a_document}
     */
    public function index($id, array $body, Refresh $refresh = null)
    {
        $params = [
            'index' => $this->indexName,
            'id' => $this->idPrefix.$id,
            'body' => $body,
        ];

        if (null !== $refresh) {
            $params['refresh'] = $refresh->getType();
        }

        try {
            $response = $this->client->index($params);
        } catch (\Exception $e) {
            throw new IndexationException($e->getMessage(), $e->getCode(), $e);
        }

        if (isset($response['errors']) && true === $response['errors']) {
            $this->throwIndexationExceptionFromResponse($response);
        }

        return $response;
    }

    /**
     * @param iterable $documents
     * @param ?string $keyAsId
     * @param Refresh|null $refresh
     *
     * @throws MissingIdentifierException
     * @throws IndexationException
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_indexing_documents.html#_bulk_indexing}
     */
    public function bulkIndexes($documents, $keyAsId = null, Refresh $refresh = null)
    {
        $params = [];
        $paramsComputedSize = 0;
        $mergedResponse = [
            'took' => 0,
            'errors' => false,
            'items' => [],
        ];
        foreach ($documents as $document) {
            $action = ['index' => ['_index' => $this->indexName]];
            if ($document instanceof ElasticsearchProjection) {
                $document = $document->toArray();
            }

            if (null !== $keyAsId) {
                if (!isset($document[$keyAsId])) {
                    throw new MissingIdentifierException(sprintf('Missing "%s" key in document', $keyAsId));
                }

                $action['index']['_id'] = $this->idPrefix . $document[$keyAsId];
            }

            $estimatedAddedSize = strlen(json_encode([$action, $document]));
            if ($paramsComputedSize + $estimatedAddedSize >= $this->maxChunkSize) {
                $mergedResponse = $this->doBulkIndex($params, $mergedResponse);
                $paramsComputedSize = 0;
                $params = [];
            }

            $params['body'][] = $action;
            $params['body'][] = $document;

            $paramsComputedSize += $estimatedAddedSize;

            if (null !== $refresh) {
                $params['refresh'] = $refresh->getType();
            }
        }
        $mergedResponse = $this->doBulkIndex($params, $mergedResponse);

        if (isset($mergedResponse['errors']) && true === $mergedResponse['errors']) {
            $this->throwIndexationExceptionFromResponse($mergedResponse);
        }

        return $mergedResponse;
    }

    private function doBulkIndex(array $params, array $mergedResponse): array
    {
        $length = count($params['body']);
        try {
            $mergedResponse = $this->doChunkedBulkIndex($params, $mergedResponse, $length);
        } catch (BadRequest400Exception) {
            $chunkLength = intdiv($length, self::NUMBER_OF_BATCHES_ON_RETRY);
            $chunkLength = $chunkLength % 2 == 0 ? $chunkLength : $chunkLength + 1;

            $mergedResponse = $this->doChunkedBulkIndex($params, $mergedResponse, $chunkLength);
        } catch (\Exception $e) {
            throw new IndexationException($e->getMessage(), $e->getCode(), $e);
        }

        return $mergedResponse;
    }

    private function doChunkedBulkIndex(array $params, array $mergedResponse, int $chunkLength): array
    {
        $bulkRequest = [];
        if (isset($params['refresh'])) {
            $bulkRequest['refresh'] = $params['refresh'];
        }

        $chunkedBody = array_chunk($params['body'], $chunkLength);
        foreach ($chunkedBody as $chunk) {
            $bulkRequest['body'] = $chunk;
            $response = $this->client->bulk($bulkRequest);

            if (isset($response['errors']) && true === $response['errors']) {
                $mergedResponse['errors'] = true;
            }

            $mergedResponse['items'] = array_merge($mergedResponse['items'], $response['items']);

            if (isset($response['took'])) {
                $mergedResponse['took'] += $response['took'];
            }
        }

        return $mergedResponse;
    }

    /**
     * @param string $id
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_get_a_document}
     */
    public function get($id)
    {
        $params = [
            'index' => $this->indexName,
            'id' => $this->idPrefix.$id,
        ];

        return $this->client->get($params);
    }

    /**
     * @param array  $body
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_search_for_a_document}
     */
    public function search(array $body)
    {
        $params = [
            'index' => $this->indexName,
            'body' => $body,
        ];

        return $this->client->search($params);
    }

    /**
     * @param array  $body
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-multi-search.html}
     */
    public function msearch(array $body): array
    {
        $params = [
            'index' => $this->indexName,
            'body' => $body,
        ];

        return $this->client->msearch($params);
    }

    /**
     * @param array $body
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/ElasticsearchPHP_Endpoints.html#Elasticsearch_Clientcount_count}
     */
    public function count(array $body): array
    {
        $params = [
            'index' => $this->indexName,
            'body' => $body,
        ];

        return $this->client->count($params);
    }

    /**
     * @param string $id
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_delete_a_document}
     */
    public function delete($id)
    {
        $params = [
            'index' => $this->indexName,
            'id' => $this->idPrefix.$id,
        ];

        return $this->client->delete($params);
    }

    /**
     * @param $documentIds
     *
     * @return array
     */
    public function bulkDelete($documentIds)
    {
        $params = [];

        foreach ($documentIds as $identifier) {
            $params['body'][] = [
                'delete' => [
                    '_index' => $this->indexName,
                    '_id' => $this->idPrefix.$identifier,
                ],
            ];
        }

        return $this->client->bulk($params);
    }

    public function bulkUpdate($documentIds, $params)
    {
        $queries = [];

        foreach ($documentIds as $identifier) {
            $queries['body'][] = [
                'update' => [
                    '_index' => $this->indexName,
                    '_id' => $this->idPrefix.$identifier,
                ],
            ];

            $queries['body'][] = $params[$identifier];
        }

        return $this->client->bulk($queries);
    }

    /**
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_delete_an_index}
     */
    public function deleteIndex()
    {
        $indices = $this->client->indices();
        $indexName = $this->indexName;
        if ($indices->existsAlias(['name' => $indexName])) {
            $aliases = $indices->getAlias(['name' => $indexName]);
            $indexName = array_keys($aliases)[0];
        }

        return $indices->delete(['index' => $indexName]);
    }

    /**
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_create_an_index}
     */
    public function createIndex()
    {
        if ($this->hasIndexForAlias()) {
            throw new \LogicException(sprintf('Index %s already exists', $this->indexName));
        }

        $configuration = $this->configurationLoader->load();
        $body = $configuration->buildAggregated();
        $body['aliases'] = [$this->indexName => (object) []];

        $params = [
            'index' => strtolower($this->indexName . '_' . Uuid::uuid4()->toString()),
            'body' => $body,
        ];

        return $this->client->indices()->create($params);
    }

    /**
     * See {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/ElasticsearchPHP_Endpoints.html#Elasticsearch_Namespaces_IndicesNamespaceexists_exists}
     *
     * @return bool
     */
    public function hasIndex(): bool
    {
        return $this->client->indices()->exists(['index' => $this->indexName]);
    }

    public function hasIndexForAlias(): bool
    {
        return $this->client->indices()->existsAlias(['name' => $this->indexName]);
    }

    /**
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-refresh.html}
     */
    public function refreshIndex()
    {
        return $this->client->indices()->refresh(['index' => $this->indexName]);
    }

    /**
     * Deletes an index if it exists and recreates it with its associated configuration.
     */
    public function resetIndex(): void
    {
        if ($this->hasIndexForAlias() || $this->hasIndex()) {
            $this->deleteIndex();
        }

        $this->createIndex();
    }

    /**
     * @param array $response
     *
     * @throws IndexationException
     */
    private function throwIndexationExceptionFromResponse(array $response)
    {
        foreach ($response['items'] as $item) {
            if (isset($item['index']['error'])) {
                throw new IndexationException(json_encode($item['index']['error']));
            }
        }
    }

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return $this->indexName;
    }

    public function getConfigurationLoader(): Loader
    {
        return $this->configurationLoader;
    }

    /**
     * @param array $body an array containing a query compatible with https://www.elastic.co/guide/en/elasticsearch/reference/5.5/docs-delete-by-query.html
     * @throws Conflict409Exception
     */
    public function deleteByQuery(array $body): void
    {
        $attempts = 0;
        $exception = null;
        do {
            $attempts++;
            try {
                $this->client->deleteByQuery([
                    'index' => $this->indexName,
                    'body' => $body,
                ]);
                return;
            } catch (Conflict409Exception $e) {
                $exception = $e;
                usleep($this->maxExpectedIndexationLatencyInMicroseconds);
                continue;
            }
        } while ($attempts < $this->maxNumberOfRetries);
        throw $exception;
    }

    /**
     * @param array $body an array containing a query compatible with https://www.elastic.co/guide/en/elasticsearch/reference/5.5/docs-update-by-query.html
     */
    public function updateByQuery(array $body): void
    {
        $this->client->updateByQuery([
            'index'     => $this->indexName,
            'conflicts' => 'proceed',
            'body'      => $body,
        ]);
    }
}
