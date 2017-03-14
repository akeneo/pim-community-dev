<?php

namespace spec\Akeneo\Bundle\ElasticsearchBundle;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Elasticsearch\Client as NativeClient;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Namespaces\IndicesNamespace;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ClientSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Client::class);
    }

    function let(NativeClient $client, ClientBuilder $clientBuilder)
    {
        $this->beConstructedWith($clientBuilder, ['localhost:9200'], 'an_index_name');
        $clientBuilder->setHosts(Argument::any())->willReturn($clientBuilder);
        $clientBuilder->build()->willReturn($client);
    }

    public function it_indexes_a_document($client)
    {
        $client->index(
            [
                'index' => 'an_index_name',
                'type'  => 'an_index_type',
                'id'    => 'identifier',
                'body'  => ['a key' => 'a value']
            ]
        )->shouldBeCalled();

        $this->index('an_index_type', 'identifier', ['a key' => 'a value']);
    }

    public function it_gets_a_document($client)
    {
        $client->get(
            [
                'index' => 'an_index_name',
                'type'  => 'an_index_type',
                'id'    => 'identifier',
            ]
        )->shouldBeCalled();

        $this->get('an_index_type', 'identifier');
    }

    public function it_indexes_documents($client)
    {
        $client->search(
            [
                'index' => 'an_index_name',
                'type'  => 'an_index_type',
                'body'  => ['a key' => 'a value']
            ]
        )->shouldBeCalled();

        $this->search('an_index_type', ['a key' => 'a value']);
    }

    public function it_deletes_a_document($client)
    {
        $client->delete(
            [
                'index' => 'an_index_name',
                'type'  => 'an_index_type',
                'id'    => 'identifier',
            ]
        )->shouldBeCalled();

        $this->delete('an_index_type', 'identifier');
    }

    public function it_deletes_an_index($client, IndicesNamespace $indices)
    {
        $client->indices()->willReturn($indices);
        $indices->delete(['index' => 'an_index_name'])->shouldBeCalled();

        $this->deleteIndex();
    }

    public function it_creates_an_index($client, IndicesNamespace $indices)
    {
        $client->indices()->willReturn($indices);
        $indices->create(
            [
                'index' => 'an_index_name',
                'body' => ['index configuration']
            ]
        )->shouldBeCalled();

        $this->createIndex(['index configuration']);
    }
}
