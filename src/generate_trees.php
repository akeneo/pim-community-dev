<?php

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
            'family'     => 'tshirt',
            'parent'     => $rootParentLabel,
            'identifier' => $label,
            'level'      => 0,
            'values'     => [
                [
                    'color' => [
                        '<all_channels>' => [
                            '<all_locales>' => $color,
                        ],
                    ],
                ],
                [
                    'material' => [
                        '<all_channels>' => [
                            '<all_locales>' => $materials[$color],
                        ],

                    ],
                ],
            ],
        ];

        foreach ($sizes as $size) {
            $toIndex[] = [
                'identifier' => 'Cotton t-shirt with a round neck Divided ' . $color . ' ' . $size . ' #' . $folie,
                'family'     => 'tshirt',
                'parent'     => $label,
                'values'     => [
                    [
                        'size' => [
                            '<all_channels>' => [
                                '<all_locales>' => $size,
                            ],
                        ],
                    ],
                ],
            ];
        }
    }

    return array_merge(
        [
            [
                'family'     => 'tshirt',
                'identifier' => $rootParentLabel,
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
            'family'     => 'tshirt',
            'identifier' => $label,
            'values'     => [
                [
                    'color' => [
                        '<all_channels>' => [
                            '<all_locales>' => $color,
                        ],
                    ],
                ],
                [
                    'material' => [
                        '<all_channels>' => [
                            '<all_locales>' => $materials[$color],
                        ],

                    ],
                ],
            ],
            'level'      => 0,
        ];

        foreach ($sizes as $size) {
            $toIndex[] = [
                'family'     => 'tshirt',
                'parent'     => $label,
                'identifier' => 'T-shirt with a Kurt Cobain print motif ' . $size . ' #' . $folie,
                'values'     => [
                    [
                        'size' => [
                            '<all_channels>' => [
                                '<all_locales>' => $size,
                            ],
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
    return [
        [
            'family'     => 'watch',
            'identifier' => 'Metal watch blue/white striped #' . $folie,
            'values'     => [
                [
                    'color' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                ],
                [
                    'material' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'metal',
                        ],
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
            'family'     => 'watch',
            'identifier' => $label,
            'values'     => [
                [
                    'color' => [
                        '<all_channels>' => [
                            '<all_locales>' => $color,
                        ],
                    ],
                ],
                [
                    'material' => [
                        '<all_channels>' => [
                            '<all_locales>' => $materials[$color],
                        ],

                    ],
                ],
            ],
            'level'      => 0,
        ];

        foreach ($sizes as $size) {
            $toIndex[] = [
                'family'     => 'watch',
                'parent'     => $label,
                'identifier' => 'Braided hat ' . $size . ' #' . $folie,
                'values'     => [
                    [
                        'size' => [
                            '<all_channels>' => [
                                '<all_locales>' => $size,
                            ],
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
            'family'     => 'tshirt',
            'identifier' => $label,
            'values'     => [
                [
                    'material' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'cotton',
                        ],
                    ],
                ],
                [
                    'size' => [
                        '<all_channels>' => [
                            '<all_locales>' => $size,
                        ],
                    ],

                ],
            ],
            'level'      => 0,
        ];

        foreach ($colors as $color) {
            $toIndex[] = [
                'family'     => 'tshirt',
                'parent'     => $label,
                'identifier' => 'T-shirt unique size #' . $color . ' #' . $folie,
                'values'     => [
                    [
                        'color' => [
                            '<all_channels>' => [
                                '<all_locales>' => $color,
                            ],
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
            'family'     => 'shoes',
            'parent'     => $rootParentLabel,
            'identifier' => $label,
            'values'     => [
                [
                    'material' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
                        ],
                    ],
                ],
                [
                    'size' => [
                        '<all_channels>' => [
                            '<all_locales>' => $size,
                        ],
                    ],
                ],
            ],
            'level'      => 0,
        ];

        foreach ($colors as $color) {
            $toIndex[] = [
                'family'     => 'shoes',
                'parent'     => $label,
                'identifier' => 'Running shoes ' . $size . ' ' . $color . ' #' . $folie,
                'values'     => [
                    [
                        'color' => [
                            '<all_channels>' => [
                                '<all_locales>' => $color,
                            ],
                        ],
                    ],
                ],
            ];
        }
    }

    return array_merge(
        [
            [
                'family'     => 'shoes',
                'identifier' => $rootParentLabel,
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
            'family'     => 'jacket',
            'parent'     => $rootParentLabel,
            'identifier' => $label,
            'values'     => [
                [
                    'material' => [
                        '<all_channels>' => [
                            '<all_locales>' => $material,
                        ],
                    ],
                ],

            ],
            'level'      => 0,
        ];

        foreach ($sizes as $size) {
            $toIndex[] = [
                'family'     => 'jacket',
                'parent'     => $label,
                'identifier' => 'Biker jacket ' . $material . ' ' . $size . ' #' . $folie,
                'values'     => [
                    [
                        'size' => [
                            '<all_channels>' => [
                                '<all_locales>' => $size,
                            ],
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
                'family'     => 'jacket',
                'level'      => 1,
                'values'     => [
                    [
                        'color' => [
                            '<all_channels>' => [
                                '<all_locales>' => 'white',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        $toIndex
    );
}

$esClient = $kernel->getContainer()->get('akeneo_elasticsearch.client');

for ($i = 0; $i < 1; $i++) {
    $toIndex = array_merge(
//        generateCotonTshirtRounddNeckDivided($i),
//        generateTshirtKurtCobainPrint($i),
        generateMetalWatch($i)
//        generateBraidedHat($i),
//        generateTshirtUniqueSize($i),
//        generateRunningShoes($i),
//        generateBikerJacket($i)
    );

    if ($i % 100 === 0) {
        echo "Indexing batch $i";
    }

    var_dump($toIndex);

    $esClient->bulkIndexes('foobar', $toIndex, 'identifier');
}

//$indexeMoi = [
//    [
//        'identifier' => 'ffffffffffffo',
//        'yoloo'      => 'gzhgoiaheoiahr',
//    ],
//];
//
//$esClient->bulkIndexes('foobar', $indexeMoi, 'identifier');
