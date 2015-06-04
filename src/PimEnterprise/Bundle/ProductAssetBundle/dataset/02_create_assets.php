<?php

require_once __DIR__ . '/../../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../../app/AppKernel.php';
require_once __DIR__ . '/Helper.php';

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
$kernel->boot();

$helper = new Helper($kernel->getContainer());
truncateTables();

foreach (getReferenceFilesConf() as $assetCode => $referenceFiles) {
    echo "Creating asset $assetCode...\n";
    $asset = createNewAsset($assetCode);
    foreach ($referenceFiles as $localeCode => $filename) {
        $file       = new \SplFileInfo(__DIR__ . '/' . $filename);
        $localeCode = is_int($localeCode) ? null : $localeCode;
        echo "Adding reference (locale=$localeCode)...\n";
        addReferenceToAsset($asset, $file, $localeCode);
    }
    $helper->getEm()->persist($asset);
    $helper->getEm()->flush($asset);
}


function getReferenceFilesConf()
{
    return [
        'paint'          => [
            'paint.jpg',
        ],
        'chicagoskyline' => [
            'en_US' => 'chicagoskyline-en.jpg',
            'fr_FR' => 'chicagoskyline-fr.jpg',
            'de_DE' => 'chicagoskyline-de.jpg',
        ],
        'akene'          => [
            'akene.jpg',
        ],
    ];
}

function truncateTables()
{
    global $helper;

    $helper->truncateTable('pimee_product_asset_file');
    $helper->truncateTable('pimee_product_asset_variation');
    $helper->truncateTable('pimee_product_asset_asset');
    $helper->truncateTable('pimee_product_asset_reference');
    $helper->truncateTable('pimee_product_asset_file_metadata');
}

function generateEmptyVariationsForReference(\PimEnterprise\Component\ProductAsset\Model\Reference $reference)
{
    global $helper;

    $channels = $helper->getChannelRepository()->findAll();

    /** @var \Pim\Bundle\CatalogBundle\Model\ChannelInterface $channel */
    foreach ($channels as $channel) {
        $localeReference   = $reference->getLocale();
        $generateVariation = false;

        if (null === $localeReference) {
            $generateVariation = true;
        } elseif (in_array($localeReference->getCode(), $channel->getLocaleCodes())) {
            $generateVariation = true;
        }

        if ($generateVariation) {
            $var = new \PimEnterprise\Component\ProductAsset\Model\Variation();
            $var->setReference($reference);
            $var->setChannel($channel);
        }
    }
}

function addReferenceToAsset(
    \PimEnterprise\Component\ProductAsset\Model\Asset $asset,
    \SplFileInfo $file,
    $localeCode = null
) {
    global $helper;

    $ref = new \PimEnterprise\Component\ProductAsset\Model\Reference();

    if (null !== $localeCode) {
        $locale = $helper->getLocaleRepository()->findOneByIdentifier($localeCode);
        $ref->setLocale($locale);
    }

    $file = $helper->getRawFileStorer()->store($file, 'storage');

    $ref->setFile($file);
    $ref->setAsset($asset);

    generateEmptyVariationsForReference($ref);
}

function createNewAsset($code)
{
    $asset = new \PimEnterprise\Component\ProductAsset\Model\Asset();

    if (1 === rand(0, 1)) {
        $asset->setEndOfUseAt(new \DateTime('2050-05-25 12:12:12'));
    }

    return $asset
        ->setCode($code)
        ->setDescription(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam non quam ex. Duis semper' .
            ' convallis risus at lobortis. Phasellus hendrerit auctor lectus in ullamcorper. Mauris non magna et' .
            ' tellus tempor hendrerit. Donec at lacus fringilla, rutrum enim porttitor, consectetur erat. Donec' .
            ' volutpat, nibh in hendrerit aliquet, sem massa vehicula eros, sed dictum augue tellus id nisi. Vivamus' .
            ' tempus scelerisque enim, sit amet vehicula enim scelerisque eu. Integer nec ultrices magna, sit amet' .
            ' volutpat.'
        )
        ->setCreatedAt(new \DateTime())
        ->setUpdatedAt(new \DateTime())
        ->setEnabled(true);
}
