<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\Command;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductProjection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CleanRemovedProductsCommandEndToEnd extends TestCase
{
    protected Command $command;
    protected Client $esProductClient;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->esProductClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    public function test_it_removes_product_in_elasticesearch_index_when_product_not_existing_in_mysql(): void
    {
        $this->givenProductExistingInMySQLAndElasticsearch('product_A');
        $this->givenProductOnlyExistingInElasticsearch('product_B');
        $this->whenIExecuteTheCommandToCleanTheProducts();
        $this->thenTheIndexedProductsInElasticsearchAre(['product_A']);

    }

    private function givenProductExistingInMySQLAndElasticsearch(string $identifier): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: []
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
    }

    private function givenProductOnlyExistingInElasticsearch(string $identifier): void
    {
        $uuid = ElasticsearchProductProjection::INDEX_PREFIX_ID . Uuid::uuid4()->toString();
        $product = [
            'identifier' => $identifier,
            'document_type' => ProductInterface::class,
            'id' => $uuid,
            'values'     => [
                'name-text' => [
                    '<all_channels>' => [
                        '<all_locales>' => '2015/01/01',
                    ],
                ],
            ]
        ];
        $this->esProductClient->index($uuid, $product);
        $this->esProductClient->refreshIndex();
    }

    private function whenIExecuteTheCommandToCleanTheProducts(): void
    {
        $this->resetShellVerbosity();
        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => 'pim:product:clean-removed-product']);
        $output = new BufferedOutput();
        $application->run($input, $output);
    }

    private function thenTheIndexedProductsInElasticsearchAre(array $identifiers): void
    {
        $this->esProductClient->refreshIndex();
        $params = [
            '_source' => ['identifier'],
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                'term' => [
                                    'document_type' => ProductInterface::class
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $results = $this->esProductClient->search($params);

        $esIdentifiers = array_map(function ($doc) {
            return $doc['_source']['identifier'];
        }, $results['hits']['hits']);
        $diff = array_diff($esIdentifiers, $identifiers);

        Assert::assertEmpty($diff);
    }

    private function resetShellVerbosity()
    {
        putenv('SHELL_VERBOSITY=0');
        $_ENV['SHELL_VERBOSITY'] = 0;
        $_SERVER['SHELL_VERBOSITY'] = 0;
    }
}
