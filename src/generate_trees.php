<?php

use Akeneo\Bundle\ElasticsearchBundle\Refresh;

$loader = require_once __DIR__ . '/../app/bootstrap.php.cache';

require_once __DIR__ . '/../app/AppKernel.php';

$kernel = new AppKernel('prod', true);
$kernel->loadClassCache();
$kernel->boot();

function generateCotonTshirtRounddNeckDivided($folie)
{

    $colors = ['grey', 'blue', 'red'];
    $materials = ['grey' => 'cotton', 'blue' => 'polyester', 'red' => 'cotton'];
    $sizes = ['s', 'm', 'l', 'xl'];
    $toIndex = [];
    $rootParentLabel = 'Cotton t-shirt with a round neck Divided #' . $folie;

    foreach ($colors as $color) {
        $label = 'Cotton t-shirt with a round neck Divided ' . $color . ' #' . $folie;
        $toIndex[] = [
            'family'        => [
                'code'   => 'tshirt',
                'labels' => [
                    'fr_FR' => 'La famille des tshirts',
                ],
            ],
            'root_ancestor' => $rootParentLabel,
            'parent'        => $rootParentLabel,
            'identifier'    => $label,
            'level'         => 0,
            'values'        => [
                'name-text'       => [
                    '<all_channels>' => [
                        '<all_locales>' => $label,
                    ],

                ],
                'color-option'    => [
                    '<all_channels>' => [
                        '<all_locales>' => $color,
                    ],
                ],
                'material-option' => [
                    '<all_channels>' => [
                        '<all_locales>' => $materials[$color],
                    ],

                ],
            ],
        ];

        foreach ($sizes as $size) {
            $toIndex[] = [
                'identifier'    => 'Cotton t-shirt with a round neck Divided ' . $color . ' ' . $size . ' #' . $folie,
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'root_ancestor' => $rootParentLabel,
                'parent'        => $label,
                'values'        => [
                    'name-text'   => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided ' . $color . ' ' . $size . ' #' . $folie,
                        ],

                    ],
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => $size,
                        ],
                    ],
                ],
            ];
        }
    }

    return array_merge(
        [
            [
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'identifier' => $rootParentLabel,
                'values'     => [
                    'name-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => $rootParentLabel,
                        ],

                    ],
                ],
                'level'      => 1,
            ],
        ],
        $toIndex
    );
}

function generateTshirtKurtCobainPrint($folie)
{
    $colors = ['red'];
    $materials = ['red' => 'cotton'];
    $sizes = ['s', 'm', 'l', 'xl'];
    $toIndex = [];

    foreach ($colors as $color) {
        $label = 'T-shirt with a Kurt Cobain print motif #' . $folie;
        $toIndex[] = [
            'family'        => [
                'code'   => 'tshirt',
                'labels' => [
                    'fr_FR' => 'La famille des tshirts',
                ],
            ],
            'identifier'    => $label,
            'parent'        => '_no_parent_',
            'root_ancestor' => '_no_parent_',
            'values'        => [
                'name-text'       => [
                    '<all_channels>' => [
                        '<all_locales>' => $label,
                    ],

                ],
                'color-option'    => [
                    '<all_channels>' => [
                        '<all_locales>' => $color,
                    ],
                ],
                'material-option' => [
                    '<all_channels>' => [
                        '<all_locales>' => $materials[$color],
                    ],

                ],
            ],
            'level'         => 0,
        ];

        foreach ($sizes as $size) {
            $toIndex[] = [
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'parent'        => $label,
                'root_ancestor' => '_no_parent_',
                'identifier'    => 'T-shirt with a Kurt Cobain print motif ' . $size . ' #' . $folie,
                'values'        => [
                    'name-text'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt with a Kurt Cobain print motif ' . $size . ' #' . $folie,
                        ],
                    ],
                    'size-otion' => [
                        '<all_channels>' => [
                            '<all_locales>' => $size,
                        ],
                    ],
                ],
            ];
        }
    }

    return $toIndex;
}

function generateMetalWatch($folie)
{
    $label = 'Metal watch blue/white striped #' . $folie;

    return [
        [
            'family'        => [
                'code'   => 'watch',
                'labels' => [
                    'fr_FR' => 'La famille des watch',
                ],
            ],
            'identifier'    => $label,
            'parent'        => '_no_parent_',
            'root_ancestor' => '_no_parent_',
            'values'        => [
                'name-text'       => [
                    '<all_channels>' => [
                        '<all_locales>' => $label,
                    ],
                ],
                'color-option'    => [
                    '<all_channels>' => [
                        '<all_locales>' => 'blue',
                    ],
                ],
                'material-option' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'metal',
                    ],
                ],
            ],
        ],
    ];
}

function generateBraidedHat($folie)
{
    $colors = ['grey'];
    $materials = ['grey' => 'wool'];
    $sizes = ['m', 'l'];
    $toIndex = [];

    foreach ($colors as $color) {
        $label = 'Braided hat #' . $folie;
        $toIndex[] = [
            'family'        => [
                'code'   => 'hat',
                'labels' => [
                    'fr_FR' => 'La famille des hats',
                ],
            ],
            'identifier'    => $label,
            'parent'        => '_no_parent_',
            'root_ancestor' => '_no_parent_',
            'values'        => [
                'name-text'       => [
                    '<all_channels>' => [
                        '<all_locales>' => $label,
                    ],
                ],
                'color-option'    => [
                    '<all_channels>' => [
                        '<all_locales>' => $color,
                    ],
                ],
                'material-option' => [
                    '<all_channels>' => [
                        '<all_locales>' => $materials[$color],
                    ],

                ],
            ],
            'level'         => 0,
        ];

        foreach ($sizes as $size) {
            $toIndex[] = [
                'family'        => [
                    'code'   => 'hat',
                    'labels' => [
                        'fr_FR' => 'La famille des hats',
                    ],
                ],
                'parent'        => $label,
                'root_ancestor' => '_no_parent_',
                'identifier'    => 'Braided hat ' . $size . ' #' . $folie,
                'values'        => [
                    'name-text'   => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Braided hat ' . $size . ' #' . $folie,
                        ],
                    ],
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => $size,
                        ],
                    ],
                ],
            ];
        }
    }

    return $toIndex;
}

function generateTshirtUniqueSize($folie)
{
    $colors = ['blue', 'red', 'yellow'];
    $sizes = ['u'];
    $toIndex = [];

    foreach ($sizes as $size) {
        $label = 'T-shirt unique size #' . $folie;
        $toIndex[] = [
            'family'        => [
                'code'   => 'tshirt',
                'labels' => [
                    'fr_FR' => 'La famille des tshirts',
                ],
            ],
            'identifier'    => $label,
            'parent'        => '_no_parent_',
            'root_ancestor' => '_no_parent_',
            'values'        => [
                'name-text'       => [
                    '<all_channels>' => [
                        '<all_locales>' => $label,
                    ],
                ],
                'material-option' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'cotton',
                    ],
                ],
                'size-option'     => [
                    '<all_channels>' => [
                        '<all_locales>' => $size,
                    ],
                ],
            ],
            'level'         => 0,
        ];

        foreach ($colors as $color) {
            $toIndex[] = [
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'parent'        => $label,
                'root_ancestor' => '_no_parent_',
                'identifier'    => 'T-shirt unique size #' . $color . ' #' . $folie,
                'values'        => [
                    'name-text'    => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt unique size #' . $color . ' #' . $folie,
                        ],
                    ],
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => $color,
                        ],
                    ],
                ],
            ];
        }
    }

    return $toIndex;
}

function generateRunningShoes($folie)
{

    $colors = ['white', 'blue', 'red'];
    $sizes = ['s', 'm', 'l'];
    $toIndex = [];
    $rootParentLabel = 'Running shoes #' . $folie;

    foreach ($sizes as $size) {
        $label = 'Running shoes ' . $size . ' #' . $folie;
        $toIndex[] = [
            'family'        => [
                'code'   => 'shoe',
                'labels' => [
                    'fr_FR' => 'La famille des shoe',
                ],
            ],
            'parent'        => $rootParentLabel,
            'root_ancestor' => $rootParentLabel,
            'identifier'    => $label,
            'values'        => [
                'name-text'       => [
                    '<all_channels>' => [
                        '<all_locales>' => $label,
                    ],
                ],
                'material-option' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'leather',
                    ],
                ],
                'size-option'     => [
                    '<all_channels>' => [
                        '<all_locales>' => $size,
                    ],
                ],
            ],
            'level'         => 0,
        ];

        foreach ($colors as $color) {
            $toIndex[] = [
                'family'        => [
                    'code'   => 'shoe',
                    'labels' => [
                        'fr_FR' => 'La famille des shoe',
                    ],
                ],
                'parent'        => $label,
                'root_ancestor' => $rootParentLabel,
                'identifier'    => 'Running shoes ' . $size . ' ' . $color . ' #' . $folie,
                'values'        => [
                    'name-text'    => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Running shoes ' . $size . ' ' . $color . ' #' . $folie,
                        ],
                    ],
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => $color,
                        ],
                    ],
                ],
            ];
        }
    }

    return array_merge(
        [
            [
                'family'     => [
                    'code'   => 'shoe',
                    'labels' => [
                        'fr_FR' => 'La famille des shoe',
                    ],
                ],
                'identifier' => $rootParentLabel,
                'values'     => [
                    'name-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => $rootParentLabel,
                        ],
                    ],
                ],
                'level'      => 1,
            ],
        ],
        $toIndex
    );
}

function generateBikerJacket($folie)
{

    $materials = ['leather', 'polyester'];
    $sizes = ['s', 'm', 'l'];
    $toIndex = [];
    $rootParentLabel = 'Biker jacket #' . $folie;

    foreach ($materials as $material) {
        $label = 'Biker jacket ' . $material . ' #' . $folie;
        $toIndex[] = [
            'family'        => [
                'code'   => 'jacket',
                'labels' => [
                    'fr_FR' => 'La famille des jacket',
                ],
            ],
            'parent'        => $rootParentLabel,
            'root_ancestor' => $rootParentLabel,
            'identifier'    => $label,
            'values'        => [
                'name-text'       => [
                    '<all_channels>' => [
                        '<all_locales>' => $label,
                    ],
                ],
                'material-option' => [
                    '<all_channels>' => [
                        '<all_locales>' => $material,
                    ],
                ],

            ],
            'level'         => 0,
        ];

        foreach ($sizes as $size) {
            $toIndex[] = [
                'family'        => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des jacket',
                    ],
                ],
                'parent'        => $label,
                'root_ancestor' => $rootParentLabel,
                'identifier'    => 'Biker jacket ' . $material . ' ' . $size . ' #' . $folie,
                'values'        => [
                    'name-text'   => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Biker jacket ' . $material . ' ' . $size . ' #' . $folie,
                        ],
                    ],
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => $size,
                        ],
                    ],
                ],
            ];
        }
    }

    return array_merge(
        [
            [
                'identifier' => $rootParentLabel,
                'family'     => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des jacket',
                    ],
                ],
                'level'      => 1,
                'values'     => [
                    'name-text'    => [
                        '<all_channels>' => [
                            '<all_locales>' => $rootParentLabel,
                        ],
                    ],
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                ],
            ],
        ],
        $toIndex
    );
}

function resetElasticsearchIndex($kernel)
{
    $esConfigurationLoader = $kernel->getContainer()->get('akeneo_elasticsearch.index_configuration.loader');
    $esClient = $kernel->getContainer()->get('akeneo_elasticsearch.client');

    $conf = $esConfigurationLoader->load();

    if ($esClient->hasIndex()) {
        $esClient->deleteIndex();
    }

    $esClient->createIndex($conf->buildAggregated());
}

$esClient = $kernel->getContainer()->get('akeneo_elasticsearch.client');
resetElasticsearchIndex($kernel);

for ($i = 0; $i < 100000; $i++) {
    $productsAndModel = array_merge(
        generateCotonTshirtRounddNeckDivided($i),
        generateTshirtKurtCobainPrint($i),
        generateMetalWatch($i),
        generateBraidedHat($i),
        generateTshirtUniqueSize($i),
        generateRunningShoes($i),
        generateBikerJacket($i)
    );

//    if ($i % 100 === 0) {
//        echo "Indexing batch $i \n";
//    }

    echo "\n";
    echo "Indexing batch $i \n";

    $productsModel0 = [];
    $productsModel1 = [];
    $productsVariant = [];
    foreach ($productsAndModel as $doc) {
        if (isset($doc['level']) && $doc['level'] === 0) {
            $productsModel0[] = $doc;
        } elseif (isset($doc['level']) && $doc['level'] === 1) {
            $productsModel1[] = $doc;
        } else {
            $productsVariant[] = $doc;
        }
    }

    if (!empty($productsModel1)) {
//        echo "Indexing ". count($productsModel1) . " model 1\n";

        $response = $esClient->bulkIndexes('pim_catalog_product_model_1', $productsModel1, 'identifier',
            Refresh::disabled());

        if ($response['errors'] === true) {
            var_dump($response);
        }
    }
    if (!empty($productsModel0)) {
//        echo "Indexing ". count($productsModel0) . " model 0\n";

        $response = $esClient->bulkIndexes('pim_catalog_product_model_0', $productsModel0, 'identifier',
            Refresh::disabled());

        if ($response['errors'] === true) {
            var_dump($response);
        }
    }

    if (!empty($productsVariant)) {
//        echo "Indexing ". count($productsVariant) . " products\n";

        $response = $esClient->bulkIndexes('pim_catalog_product', $productsVariant, 'identifier', Refresh::disabled());

        if ($response['errors'] === true) {
            var_dump($response);
        }
    }
}

