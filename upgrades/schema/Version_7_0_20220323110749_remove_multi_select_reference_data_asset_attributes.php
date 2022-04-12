<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Elasticsearch\Client as NativeClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/*
 * It was possible to create reference data attribute on our Serenity instances. As reference data is for customized code, it didn't make sense. Some customers played with it and created  reference attribute, even if it cannot be used.
 * The UI prevents it now: it's not available anymore in Serenity. Purpose of this migration is to clean the attributes in the database for our Serenity customers if reference data exists.
 * To not play in Flexibility for 7.0 ! As reference data for these customers can exist.
 */
final class Version_7_0_20220323110749_remove_multi_select_reference_data_asset_attributes extends AbstractMigration implements ContainerAwareInterface
{
    private ?ContainerInterface $container;
    private ?NativeClient $nativeClient = null;
    private LoggerInterface $logger;

    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        parent::__construct($connection, $logger);

        $this->logger = $logger;
    }

    public function up(Schema $schema): void
    {
        $attributeCodeToDelete = [];
        $assetReferenceDataAttributes = $this->getAssetReferenceDataAttributes();

        foreach ($assetReferenceDataAttributes as $assetReferenceDataAttribute) {
            $count = $this->countProductWithValueSetForAttribute($assetReferenceDataAttribute['code']);

            if ($count > 0) {
                $this->logger->warning(
                    sprintf(
                        'Can\'t remove asset reference data multi select attribute "%s". There is at least one product with a value set for it.',
                        $assetReferenceDataAttribute['code']
                    )
                );

                continue;
            }

            $attributeCodeToDelete[] = $assetReferenceDataAttribute['code'];
        }

        $this->removeAttributes($attributeCodeToDelete);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    private function getAssetReferenceDataAttributes(): array
    {
        $sql = <<<SQL
            SELECT code, properties FROM pim_catalog_attribute WHERE attribute_type = 'pim_reference_data_multiselect';
        SQL;

        $attributes = $this->connection->fetchAllAssociative($sql);

        return array_filter($attributes, function (array $attribute) {
            $properties = unserialize($attribute['properties']);

            return 'assets' === $properties['reference_data_name'];
        });
    }

    private function countProductWithValueSetForAttribute(string $code): int
    {
        $indexName = $this->container->getParameter('product_and_product_model_index_name');

        $request = [
            'index' => $indexName,
            'body' => [
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'exists' => [
                                            'field' => sprintf('values.%s-reference_data_options.*.*', $code),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->getClient()->count($request);

        return (int) $result['count'];
    }

    private function removeAttributes(array $attributeCodeToDelete): void
    {
        if (empty($attributeCodeToDelete)) {
            return;
        }

        $removeAttributeSql = <<<SQL
            DELETE FROM pim_catalog_attribute WHERE code IN (:attribute_codes)
        SQL;

        $this->connection
            ->executeQuery(
                $removeAttributeSql,
                ['attribute_codes' => $attributeCodeToDelete],
                ['attribute_codes' => Connection::PARAM_STR_ARRAY],
            );
    }

    private function getClient(): NativeClient
    {
        if (!$this->nativeClient) {
            $indexHosts = $this->container->getParameter('index_hosts');
            $clientBuilder = $this->container->get('akeneo_elasticsearch.client_builder')->setHosts([$indexHosts]);
            $this->nativeClient = $clientBuilder->build();
        }

        return $this->nativeClient;
    }
}
