<?php

namespace Akeneo\Bundle\ElasticsearchBundle;

use Akeneo\Bundle\ElasticsearchBundle\Exception\MissingIdentifierException;
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

    /** @var array */
    private $hosts;

    /** @var NativeClient */
    private $client;

    /**
     * Configure the PHP Elasticsearch client.
     * To learn more, please see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html}
     *
     * @param ClientBuilder $builder
     * @param array         $hosts
     * @param string        $indexName
     */
    public function __construct(ClientBuilder $builder, array $hosts)
    {
        $this->builder = $builder;
        $this->hosts = $hosts;

        $builder->setHosts($hosts);
        $this->client = $builder->build();
    }

    /**
     * @param string       $indexName
     * @param string       $indexType
     * @param string       $id
     * @param array        $body
     * @param Refresh|null $refresh
     *
     * @return array see <a href='psi_element://https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_index_a_document}'>https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_index_a_document}</a>
     */
    public function index($indexName, $indexType, $id, array $body, Refresh $refresh = null)
    {
        $params = [
            'index' => $indexName,
            'type' => $indexType,
            'id' => $id,
            'body' => $body,
        ];

        if (null !== $refresh) {
            $params['refresh'] = $refresh->getType();
        }

        return $this->client->index($params);
    }

    /**
     * @param string       $indexType
     * @param array        $documents
     * @param string       $keyAsId
     * @param Refresh|null $refresh
     *
     * @throws MissingIdentifierException
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_indexing_documents.html#_bulk_indexing}
     */
    public function bulkIndexes($indexName, $indexType, $documents, $keyAsId, Refresh $refresh = null)
    {
        $params = [];

        foreach ($documents as $document) {
            if (!isset($document[$keyAsId])) {
                throw new MissingIdentifierException(sprintf('Missing "%s" key in document', $keyAsId));
            }

            $params['body'][] = [
                'index' => [
                    '_index' => $indexName,
                    '_type' => $indexType,
                    '_id' => $document[$keyAsId],
                ],
            ];

            $params['body'][] = $document;

            if (null !== $refresh) {
                $params['refresh'] = $refresh->getType();
            }
        }

        return $this->client->bulk($params);
    }

    /**
     * @param string $indexType
     * @param string $id
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_get_a_document}
     */
    public function get($indexName, $indexType, $id)
    {
        $params = [
            'index' => $indexName,
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
    public function search($indexName, $indexType, array $body)
    {
        $params = [
            'index' => $indexName,
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
    public function delete($indexName, $indexType, $id)
    {
        $params = [
            'index' => $indexName,
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
    public function bulkDelete($indexName, $indexType, $documentIds)
    {
        $params = [];

        foreach ($documentIds as $identifier) {
            $params['body'][] = [
                'delete' => [
                    '_index' => $indexName,
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
    public function deleteIndex($indexName)
    {
        return $this->client->indices()->delete(['index' => $indexName]);
    }

    /**
     * @param array $body
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_create_an_index}
     */
    public function createIndex($indexName, array $body)
    {
        $params = [
            'index' => $indexName,
            'body' => $body,
        ];

        return $this->client->indices()->create($params);
    }

    /**
     * See {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/ElasticsearchPHP_Endpoints.html#Elasticsearch_Namespaces_IndicesNamespaceexists_exists}
     *
     * @return bool
     */
    public function hasIndex($indexName)
    {
        return $this->client->indices()->exists(['index' => $indexName]);
    }

    /**
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-refresh.html}
     */
    public function refreshIndex($indexName)
    {
        return $this->client->indices()->refresh(['index' => $indexName]);
    }
}
