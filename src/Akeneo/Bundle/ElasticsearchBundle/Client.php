<?php

namespace Akeneo\Bundle\ElasticsearchBundle;

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

    /** @var string */
    private $indexName;

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
    public function __construct(ClientBuilder $builder, array $hosts, $indexName)
    {
        $this->builder = $builder;
        $this->hosts = $hosts;
        $this->indexName = $indexName;

        $builder->setHosts($hosts);
        $this->client = $builder->build();
    }

    /**
     * @param string $indexType
     * @param string $id
     * @param array  $body
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_index_a_document}
     */
    public function index($indexType, $id, array $body)
    {
        $params = [
            'index' => $this->indexName,
            'type'  => $indexType,
            'id'    => $id,
            'body'  => $body
        ];

        return $this->client->index($params);
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
            'type'  => $indexType,
            'id'    => $id,
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
            'type'  => $indexType,
            'body'  => $body
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
            'type'  => $indexType,
            'id'    => $id
        ];

        return $this->client->delete($params);
    }

    /**
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_delete_an_index}
     */
    public function deleteIndex()
    {
        return $this->client->indices()->delete(['index' => $this->indexName]);
    }

    /**
     * @param array $body
     *
     * @return array see {@link https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_quickstart.html#_create_an_index}
     */
    public function createIndex(array $body)
    {
        $params = [
            'index' => $this->indexName,
            'body'  => $body,
        ];

        return $this->client->indices()->create($params);
    }
}
