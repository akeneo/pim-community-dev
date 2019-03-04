<?php

namespace Pim\Upgrade\Schema;

use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client as IndexClient;

/**
 * In PIM 2.x (ES 5.6), each indices has multiple mapping types. This is not allowed anymore in ES 6.5 (PIM 3.x).
 * Our goal is that each indices has only one mapping type.
 * The problem is that it is not possible with the ES API to rename an index nor to remove a type from an existing
 * index.
 *
 * For instance : In PIM 2.x
 *   - the index akeneo_pim_product had 3 types (pim_catalog_product / pimee_workflow_product_proposal /
 *   pimee_workflow_published_product)
 *   - the index akeneo_pim_product_model had 3 types (pim_catalog_product / pimee_workflow_product_proposal /
 *   pimee_workflow_published_product)
 *   - the index akeneo_pim_product_and_product_model had 3 types (pim_catalog_product /
 *   pimee_workflow_product_proposal / pimee_workflow_published_product)
 *   ...
 *
 * In PIM 3.x, this is not the case anymore and each indices has only one type.
 *
 * To migrate we use the REINDEX API
 * {@link https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-reindex.html} and:
 *    - create a temporary index compliant with ES 6.5
 *    - migrate documents corresponding to a type
 *    - remove the old index
 *    - create a new index with the old name compliant with ES 6.5
 *    - move documents from the temporary index to the new one
 *    - remove the temporary index
 */
class CleanIndex
{
    /**
     * @param ClientBuilder $builder
     * @param array $hosts
     * @param array|IndexMovement[] $indexMovements
     */
    public function __invoke(
        ClientBuilder $builder,
        array $hosts,
        array $indexMovements
    ): void {
        $esNativeClient = $builder->setHosts($hosts)->build();

        $indexTmp = function (IndexMovement $indexMovement) {
            return $indexMovement->indexTo.'_v3';
        };

        foreach ($indexMovements as $indexMovement) {
            echo "Creating {$indexTmp($indexMovement)} index\n";
            $esClientTmp = new IndexClient($builder, $indexMovement->configurationLoader, $hosts, $indexTmp($indexMovement));
            $esClientTmp->createIndex();
            echo "Moving {$indexMovement->type} from {$indexMovement->indexFrom} to {$indexTmp($indexMovement)}\n";
            $this->reindex($esNativeClient, $indexMovement->indexFrom, $indexTmp($indexMovement), $indexMovement->type);
        }

        foreach ($indexMovements as $indexMovement) {
            if ($esNativeClient->indices()->exists(['index' => $indexMovement->indexFrom])) {
                echo "Deleting {$indexMovement->indexFrom}\n";
                $esNativeClient->indices()->delete(['index' => $indexMovement->indexFrom]);
            }
            if ($esNativeClient->indices()->exists(['index' => $indexMovement->indexTo])) {
                echo "Deleting {$indexMovement->indexTo}\n";
                $esNativeClient->indices()->delete(['index' => $indexMovement->indexTo]);
            }
        }

        foreach ($indexMovements as $indexMovement) {
            //Setup the new Index (which has the same name than old one but with the good configuration)
            echo "Creating {$indexMovement->indexTo}\n";
            $esClient = new IndexClient($builder, $indexMovement->configurationLoader, $hosts, $indexMovement->indexTo);
            $esClient->createIndex();
            echo "Moving {$indexMovement->type} from {$indexTmp($indexMovement)} to {$indexMovement->indexTo}\n";
            $this->reindex($esNativeClient, $indexTmp($indexMovement), $indexMovement->indexTo, $indexMovement->type);
            echo "Deleting {$indexTmp($indexMovement)}\n";
            $esNativeClient->indices()->delete(['index' => $indexTmp($indexMovement)]);
        }
    }

    /**
     * @param Client $client
     * @param string $indexDest
     * @param string $indexFrom
     * @param string $type
     */
    private function reindex(Client $client, string $indexFrom, string $indexDest, string $type): void
    {
        $client->reindex(
            [
                //TODO test it works on big catalog
                "wait_for_completion" => true,
                "body"                => [
                    "source" => [
                        "index" => $indexFrom,
                        "type"  => $type,
                    ],
                    "dest"   => [
                        "index" => $indexDest
                    ]
                ]
            ]
        );
    }
}

final class IndexMovement
{
    /** @var string */
    public $indexFrom;

    /** @var string */
    public $indexTo;

    /** @var string */
    public $type;

    /** @var Loader */
    public $configurationLoader;

    /**
     * @param Loader $configurationLoader
     * @param string $indexFrom
     * @param string $indexTo
     * @param string $type
     */
    public function __construct(Loader $configurationLoader, string $indexFrom, string $indexTo, string $type)
    {
        $this->indexFrom = $indexFrom;
        $this->indexTo = $indexTo;
        $this->type = $type;
        $this->configurationLoader = $configurationLoader;
    }
}
