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
            'family'   => 'tshirt',
            'parent'   => $rootParentLabel,
            'label'    => $label,
            'color'    => $color,
            'material' => $materials[$color],
            'level'    => 0,
        ];

        foreach ($sizes as $size) {
            $toIndex[] = [
                'family' => 'tshirt',
                'parent' => $label,
                'label'  => 'Cotton t-shirt with a round neck Divided ' . $color . ' ' . $size . ' #' . $folie,
                'size'   => $size,
            ];
        }
    }

    return array_merge(
        [
            [
                'family' => 'tshirt',
                'label'  => $rootParentLabel,
                'level'  => 1,
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
            'family'   => 'tshirt',
            'label'    => $label,
            'color'    => $color,
            'material' => $materials[$color],
            'level'    => 0,
        ];

        foreach ($sizes as $size) {
            $toIndex[] = [
                'family' => 'tshirt',
                'parent' => $label,
                'label'  => 'T-shirt with a Kurt Cobain print motif ' . $size . ' #' . $folie,
                'size'   => $size,
            ];
        }
    }

    return $toIndex;
}

function generateMetalWatch($folie)
{
    return [
        [
            'family'   => 'watch',
            'label'    => 'Metal watch blue/white striped #' . $folie,
            'color'    => 'blue',
            'material' => 'metal',
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
            'family'   => 'watch',
            'label'    => $label,
            'color'    => $color,
            'material' => $materials[$color],
            'level'    => 0,
        ];

        foreach ($sizes as $size) {
            $toIndex[] = [
                'family' => 'watch',
                'parent' => $label,
                'label'  => 'Braided hat ' . $size . ' #' . $folie,
                'size'   => $size,
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
            'family'   => 'tshirt',
            'label'    => $label,
            'material' => 'cotton',
            'size'     => $size,
            'level'    => 0,
        ];

        foreach ($colors as $color) {
            $toIndex[] = [
                'family' => 'tshirt',
                'parent' => $label,
                'label'  => 'T-shirt unique size #' . $color . ' #' . $folie,
                'color'  => $color,
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
            'family'   => 'shoes',
            'parent'   => $rootParentLabel,
            'label'    => $label,
            'material' => 'leather',
            'size'     => $size,
            'level'    => 0,
        ];

        foreach ($colors as $color) {
            $toIndex[] = [
                'family' => 'shoes',
                'parent' => $label,
                'label'  => 'Running shoes ' . $size . ' ' . $color . ' #' . $folie,
                'color'  => $color,
            ];
        }
    }

    return array_merge(
        [
            [
                'family' => 'shoes',
                'label'  => $rootParentLabel,
                'level'  => 1,
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
            'family'   => 'jacket',
            'parent'   => $rootParentLabel,
            'label'    => $label,
            'material' => $material,
            'level'    => 0,
        ];

        foreach ($sizes as $size) {
            $toIndex[] = [
                'family' => 'jacket',
                'parent' => $label,
                'label'  => 'Biker jacket ' . $material . ' ' . $size . ' #' . $folie,
                'size'   => $size,
            ];
        }
    }

    return array_merge(
        [
            [
                'family' => 'jacket',
                'label'  => $rootParentLabel,
                'level'  => 1,
                'color'  => 'white',
            ],
        ],
        $toIndex
    );
}

$esClient = $kernel->getContainer()->get('akeneo_elasticsearch.client');

//for ($i = 0; $i < 1; $i++) {
//    $toIndex = array_merge(
//        generateCotonTshirtRounddNeckDivided($i)
//        generateCotonTshirtRounddNeckDivided($i),
//        generateTshirtKurtCobainPrint($i),
//        generateMetalWatch($i)
//        generateBraidedHat($i),
//        generateTshirtUniqueSize($i),
//        generateRunningShoes($i),
//        generateBikerJacket($i)
//    );
//
//    if ($i % 100 === 0) {
//        echo "Indexing batch $i";
//    }
//
//    var_dump($toIndex);
//
//    $esClient->bulkIndexes('foobar', $toIndex, 'label');
//}

$indexeMoi = [
    [
        'label'    => 'ffffffffffffo',
        'yoloo' => 'gzhgoiaheoiahr'
    ],
];

$esClient->bulkIndexes('foobar', $indexeMoi, 'label');
