<?php

require_once __DIR__ . '/../../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../../app/AppKernel.php';
require_once __DIR__ . '/Helper.php';

$environment = 'dev';

if (isset($argv[1])) {
    $environment = $argv[1];
}

$kernel = new AppKernel($environment, true);
$kernel->loadClassCache();
$kernel->boot();

$helper = new Helper($kernel->getContainer());
$helper->truncateTable('pimee_product_asset_channel_variation_configuration');
createConfs(getRawConfs());

function getRawConfs()
{
    return [
        'ecommerce' => ['scale' => ['ratio' => 0.5]],
        'tablet'    => ['scale' => ['ratio' => 0.25]],
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
        if (null !== $channel) {
            $conf    = new \PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfiguration();
            $conf->setChannel($channel);
            $conf->setConfiguration($rawConf);
            $helper->getEm()->persist($conf);
            $helper->getEm()->flush($conf);
        }
    }
}
