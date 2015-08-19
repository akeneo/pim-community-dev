<?php

#TODO: drop this file

require_once __DIR__ . '/../../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../../app/AppKernel.php';
require_once __DIR__ . '/Helper.php';

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
$kernel->boot();

$helper = new Helper($kernel->getContainer());

truncateTables();

$count = 0;
foreach (getTagsCode() as $code) {
    $tag = createNewTag($code);

    echo "Saving new tag $code ...\n";
    $helper->getEm()->persist($tag);
    $helper->getEm()->flush($tag);
    $count++;
}
echo "$count tags has been successfully created.\n";

function truncateTables()
{
    global $helper;

    echo "Truncating tables ...\n";

    $helper->truncateTable('pimee_product_asset_asset_tag');
    $helper->truncateTable('pimee_product_asset_tag');
}

function createNewTag($code)
{
    echo "Creating new tag $code ...\n";
    $tag = new \PimEnterprise\Component\ProductAsset\Model\Tag();

    return $tag->setCode($code);
}

function getTagsCode()
{
    return [
        'women',
        'men',
        'big_sizes',
        'lacework',
        'backless',
        'pea',
        'stripes',
        'pattern',
        'solid_color',
        'flower',
        'vintage',
        'neckline',
        'dress_suit',
        'chicago',
        'cities',
        'flowers',
        'colored'
    ];
}
