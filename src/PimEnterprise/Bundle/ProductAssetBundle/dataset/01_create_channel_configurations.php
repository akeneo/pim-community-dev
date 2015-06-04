<?php

require_once __DIR__ . '/../../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../../app/AppKernel.php';
require_once __DIR__ . '/Helper.php';

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
$kernel->boot();

$helper = new Helper($kernel->getContainer());
$helper->truncateTable('pimee_product_asset_channel_variation_configuration');
createConfs(getRawConfs());

function getRawConfs()
{
    return [
        'ecommerce' => ['scale' => ['ratio' => 0.5],],
        'mobile'    => [
            'scale'      => ['width' => 200],
            'colorspace' => ['colorspace' => 'gray'],
        ],
        'print'     => ['resize' => ['width' => 400, 'height' => 200]],
    ];
}

function createConfs(array $rawConfs)
{
    global $helper;

    foreach ($rawConfs as $channelCode => $rawConf) {
        $channel = $helper->getChannelRepository()->findOneByIdentifier($channelCode);
        $conf    = new \PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfiguration();
        $conf->setChannel($channel);
        $conf->setConfiguration($rawConf);
        $helper->getEm()->persist($rawConf);
        $helper->getEm()->flush($rawConf);
    }
}



