<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle;

use Akeneo\Platform\CommunityVersion;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Exception\IndexationException;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Exception\MissingIdentifierException;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Elasticsearch\Client as NativeClient;
use Elasticsearch\ClientBuilder;
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
    /** @var ClientBuilder */
    private $builder;

    /** @var Loader */
    private $configurationLoader;

    /** @var array */
    private $hosts;

    /** @var string */
    private $indexName;

    /** @var NativeClient */
    private $client;

    private $idPrefix;

    /**
     * Configure the PHP Elasticsearch client.
     * To learn more, please see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html}
     *
     * @param ClientBuilder $builder
     * @param Loader        $configurationLoader
     * @param array         $hosts
     * @param string        $indexName
     */
    public function __construct(ClientBuilder $builder, Loader $configurationLoader, array $hosts, $indexName, string $idPrefix = '')
    {
        $this->builder = $builder;
        $this->configurationLoader = $configurationLoader;
        $this->hosts = $hosts;
        $this->indexName = $indexName;
        $this->idPrefix = $idPrefix;

        $builder->setHosts($hosts);
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
            $this->throwIndexationExceptionFromReponse($response);
        }

        return $response;
    }

    /**
     * @param array        $documents
     * @param string       $keyAsId
     * @param Refresh|null $refresh
     *
     * @throws MissingIdentifierException
     * @throws IndexationException
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_indexing_documents.html#_bulk_indexing}
     */
    public function bulkIndexes($documents, $keyAsId, Refresh $refresh = null)
    {
        $params = [];

        foreach ($documents as $document) {
            if (!isset($document[$keyAsId])) {
                throw new MissingIdentifierException(sprintf('Missing "%s" key in document', $keyAsId));
            }

            $params['body'][] = [
                'index' => [
                    '_index' => $this->indexName,
                    '_id' => $this->idPrefix.$document[$keyAsId],
                ],
            ];

            $params['body'][] = $document;

            if (null !== $refresh) {
                $params['refresh'] = $refresh->getType();
            }
        }

        try {
            $response = $this->client->bulk($params);
        } catch (\Exception $e) {
            throw new IndexationException($e->getMessage(), $e->getCode(), $e);
        }

        if (isset($response['errors']) && true === $response['errors']) {
            $this->throwIndexationExceptionFromReponse($response);
        }

        return $response;
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
        $configuration = $this->configurationLoader->load();
        $body = $configuration->buildAggregated();
        $body['aliases'] = [$this->indexName => (object) []];

        $params = [
            'index' => strtolower($this->indexName . '_' . str_replace('.', '_', CommunityVersion::VERSION) . '_' . Uuid::uuid4()),
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
    private function throwIndexationExceptionFromReponse(array $response)
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
     */
    public function deleteByQuery(array $body): void
    {
        $this->client->deleteByQuery([
            'index' => $this->indexName,
            'body' => $body,
        ]);
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
