<?php

require_once __DIR__ . '/../../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../../app/AppKernel.php';
require_once __DIR__ . '/Helper.php';

$environment = 'dev';

if (isset($argv[1]) && 'behat' === $argv[1]) {
    $environment = 'behat';
}

$kernel = new AppKernel($environment, true);
$kernel->loadClassCache();
$kernel->boot();

$helper = new Helper($kernel->getContainer());
truncateTables();
$helper->cleanFilesystem();

foreach (getReferenceFilesConf() as $assetCode => $referenceFiles) {
    echo "Creating asset $assetCode...\n";
    $asset = createNewAsset($assetCode, $environment);
    foreach ($referenceFiles as $localeCode => $filename) {
        copy(__DIR__ . '/' . $filename, '/tmp/' . $filename);
        $file       = new \SplFileInfo('/tmp/' . $filename);
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
        'autumn'         => [
            'autumn.jpg',
        ],
        'bridge'         => [
            'bridge.jpg',
        ],
        'dog'            => [
            'dog.jpg',
        ],
        'eagle'          => [
            'eagle.jpg',
        ],
        'machine'        => [
            'machine.jpg',
        ],
        'man-wall'       => [
            'man-wall.jpg',
        ],
        'minivan'        => [
            'minivan.jpg',
        ],
        'mouette'        => [
            'mouette.jpg',
        ],
        'mountain'       => [
            'mountain.jpg',
        ],
        'mugs'           => [
            'mugs.jpg',
        ],
        'photo'          => [
            'photo.jpg',
        ],
        'tiger'          => [
            'tiger.jpg',
        ],
    ];
}

function truncateTables()
{
    global $helper;

    $helper->truncateTable('akeneo_file_storage_file');
    $helper->truncateTable('pimee_product_asset_variation');
    $helper->truncateTable('pimee_product_asset_asset');
    $helper->truncateTable('pimee_product_asset_reference');
    $helper->truncateTable('pimee_product_asset_file_metadata');
}

function generateEmptyVariationsForReference(
    \PimEnterprise\Component\ProductAsset\Model\Reference $reference,
    \Akeneo\Component\FileStorage\Model\FileInterface $referenceFile
) {
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
            $var->setSourceFile($referenceFile);
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

    generateEmptyVariationsForReference($ref, $file);
}

function createNewAsset($code, $environment = 'dev')
{
    $asset = new \PimEnterprise\Component\ProductAsset\Model\Asset();

    if (1 === rand(0, 1)) {
        $asset->setEndOfUseAt(new \DateTime('2050-05-25 12:12:12'));
    }

    $descriptions = [
        'Aut porro magnam numquam sapiente quidem ipsam, ea est quos maiores asperiores! Quos atque aliquid dignissimos suscipit sed neque vitae illo alias.',
        'Et aliquid sed laborum fugiat inventore eveniet fugit error, veritatis repellendus eaque, autem molestiae soluta! Placeat natus corporis nostrum iure sapiente ipsam?',
        'Rem ipsum impedit libero recusandae beatae. Voluptate fugit laboriosam, non laborum libero cum, quasi reprehenderit ratione assumenda in, minus perferendis eaque voluptates!',
        'Sit voluptate reiciendis quaerat quam laudantium, maxime nulla molestias asperiores nesciunt repellat ut deserunt, explicabo eligendi est dolore iure quisquam. Sunt, nobis.',
        'Quo eos dolores odit dolorum velit autem, inventore placeat voluptates culpa ex illo accusamus quidem earum, tenetur nam incidunt provident sit fugiat.',
        'Minus sit dolorem, voluptatibus alias, distinctio quasi magnam ea molestiae consequatur magni voluptas ut at, cupiditate fugiat quia. Numquam animi, dolorum fuga.',
        'Deserunt nihil odit hic, maxime vero consectetur ipsum officia inventore magni possimus nostrum totam iste at suscipit. Officia quam magnam reiciendis qui.',
    ];

    shuffle($descriptions);

    $asset
        ->setCode($code)
        ->setDescription($descriptions[0])
        ->setCreatedAt(new \DateTime())
        ->setUpdatedAt(new \DateTime())
        ->setEnabled(true);

    if ('behat' === $environment) {
        $values = getBehatAssetValues()[$code];
        $asset->setDescription($values['description']);
        $asset->setEndOfUseAt($values['endOfUseAt']);
    }

    return $asset;
}

function getBehatAssetValues()
{
    return [
        'paint'          => [
            'description' => 'Photo of a paint.',
            'endOfUseAt'  => new \DateTime('2006-05-12 00:00:01')
        ],
        'chicagoskyline' => [
            'description' => 'This is chicago!',
            'endOfUseAt'  => null
        ],
        'akene'          => [
            'description' => 'Because Akeneo',
            'endOfUseAt'  => new \DateTime('2015-08-01 00:00:01')
        ],
        'autumn'         => [
            'description' => 'Leaves and water',
            'endOfUseAt'  => new \DateTime('2015-12-01 00:00:01')
        ],
        'bridge'         => [
            'description' => 'Architectural bridge of a city, above water',
            'endOfUseAt'  => null
        ],
        'dog'            => [
            'description' => 'Obviously not a cat, but still an animal',
            'endOfUseAt'  => new \DateTime('2006-05-12 00:00:01')
        ],
        'eagle'          => [
            'description' => '',
            'endOfUseAt'  => null
        ],
        'machine'        => [
            'description' => 'A big machine',
            'endOfUseAt'  => null
        ],
        'man-wall'       => [
            'description' => '',
            'endOfUseAt'  => null
        ],
        'minivan'        => [
            'description' => 'My car',
            'endOfUseAt'  => null
        ],
        'mouette'        => [
            'description' => 'Majestic animal',
            'endOfUseAt'  => null
        ],
        'mountain'       => [
            'description' => '',
            'endOfUseAt'  => null
        ],
        'mugs'           => [
            'description' => '',
            'endOfUseAt'  => null
        ],
        'photo'          => [
            'description' => '',
            'endOfUseAt'  => null
        ],
        'tiger'          => [
            'description' => 'Tiger of bengal, taken by J. Josh',
            'endOfUseAt'  => new \DateTime('2050-01-25 00:00:01')
        ],
    ];
}
