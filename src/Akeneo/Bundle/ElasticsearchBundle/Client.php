<?php

namespace Akeneo\Bundle\ElasticsearchBundle;

use Akeneo\Bundle\ElasticsearchBundle\Exception\IndexationException;
use Akeneo\Bundle\ElasticsearchBundle\Exception\MissingIdentifierException;
use Akeneo\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Elasticsearch\Client as NativeClient;
use Elasticsearch\ClientBuilder;

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

    /**
     * Configure the PHP Elasticsearch client.
     * To learn more, please see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html}
     *
     * @param ClientBuilder $builder
     * @param Loader        $configurationLoader
     * @param array         $hosts
     * @param string        $indexName
     */
    public function __construct(ClientBuilder $builder, Loader $configurationLoader, array $hosts, $indexName)
    {
        $this->builder = $builder;
        $this->configurationLoader = $configurationLoader;
        $this->hosts = $hosts;
        $this->indexName = $indexName;

        $builder->setHosts($hosts);
        $this->client = $builder->build();
    }

    /**
     * @param string       $indexType
     * @param string       $id
     * @param array        $body
     * @param Refresh|null $refresh
     *
     * @throws IndexationException
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_index_a_document}
     */
    public function index($indexType, $id, array $body, Refresh $refresh = null)
    {
        $params = [
            'index' => $this->indexName,
            'type' => $indexType,
            'id' => $id,
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
     * @param string       $indexType
     * @param array        $documents
     * @param string       $keyAsId
     * @param Refresh|null $refresh
     *
     * @throws MissingIdentifierException
     * @throws IndexationException
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_indexing_documents.html#_bulk_indexing}
     */
    public function bulkIndexes($indexType, $documents, $keyAsId, Refresh $refresh = null)
    {
        $params = [];

        foreach ($documents as $document) {
            if (!isset($document[$keyAsId])) {
                throw new MissingIdentifierException(sprintf('Missing "%s" key in document', $keyAsId));
            }

            $params['body'][] = [
                'index' => [
                    '_index' => $this->indexName,
                    '_type' => $indexType,
                    '_id' => $document[$keyAsId],
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
     * @param string $indexType
     * @param string $id
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_get_a_document}
     */
    public function get($indexType, $id)
    {
        $params = [
            'index' => $this->indexName,
            'type' => $indexType,
            'id' => $id,
        ];

        return $this->client->get($params);
    }

    /**
     * @param string $indexType
     * @param array  $body
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_search_for_a_document}
     */
    public function search($indexType, array $body)
    {
        $params = [
            'index' => $this->indexName,
            'type' => $indexType,
            'body' => $body,
        ];

        return $this->client->search($params);
    }

    /**
     * @param string $indexType
     * @param string $id
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_delete_a_document}
     */
    public function delete($indexType, $id)
    {
        $params = [
            'index' => $this->indexName,
            'type' => $indexType,
            'id' => $id,
        ];

        return $this->client->delete($params);
    }

    /**
     * @param $indexType
     * @param $documentIds
     *
     * @return array
     */
    public function bulkDelete($indexType, $documentIds)
    {
        $params = [];

        foreach ($documentIds as $identifier) {
            $params['body'][] = [
                'delete' => [
                    '_index' => $this->indexName,
                    '_type' => $indexType,
                    '_id' => $identifier,
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
        return $this->client->indices()->delete(['index' => $this->indexName]);
    }

    /**
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_create_an_index}
     */
    public function createIndex()
    {
        $configuration = $this->configurationLoader->load();
        $body = $configuration->buildAggregated();

        $params = [
            'index' => $this->indexName,
            'body' => $body,
        ];

        return $this->client->indices()->create($params);
    }

    /**
     * See {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/ElasticsearchPHP_Endpoints.html#Elasticsearch_Namespaces_IndicesNamespaceexists_exists}
     *
     * @return bool
     */
    public function hasIndex()
    {
        return $this->client->indices()->exists(['index' => $this->indexName]);
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
    public function resetIndex()
    {
        if ($this->hasIndex()) {
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
