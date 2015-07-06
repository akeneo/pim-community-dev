<?php

use Akeneo\Component\FileTransformer\Exception\NotApplicableTransformationException;
use Doctrine\ORM\EntityManager;
use PimEnterprise\Component\ProductAsset\Builder\VariationBuilder;
use PimEnterprise\Component\ProductAsset\Model\Asset;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

define('PIM_PATH', '/home/jjanvier/workspaces/phpstorm/akeneo/pim_master/ped/');
define('DATASET', realpath(__DIR__ . '/../../dataset/') . '/');
define('STORED', 'stored/');
define('THUMBNAIL', 'thumbnail/');

require_once PIM_PATH . 'vendor/autoload.php';
require_once PIM_PATH . 'app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
$kernel->boot();

$resizeResolver     = new \Akeneo\Component\FileTransformer\Options\Image\ResizeOptionsResolver();
$thumbnailResolver  = new \Akeneo\Component\FileTransformer\Options\Image\ThumbnailOptionsResolver();
$resolutionResolver = new \Akeneo\Component\FileTransformer\Options\Image\ResolutionOptionsResolver();
$colorSpaceResolver = new \Akeneo\Component\FileTransformer\Options\Image\ColorSpaceOptionsResolver();
$scaleResolver      = new \Akeneo\Component\FileTransformer\Options\Image\ScaleOptionsResolver();

$resizeTransformation     = new \Akeneo\Component\FileTransformer\Transformation\Image\Resize($resizeResolver);
$thumbnailTransformation  = new \Akeneo\Component\FileTransformer\Transformation\Image\Thumbnail($thumbnailResolver);
$resolutionTransformation = new \Akeneo\Component\FileTransformer\Transformation\Image\Resolution($resolutionResolver);
$colorSpaceTransformation = new \Akeneo\Component\FileTransformer\Transformation\Image\ColorSpace($colorSpaceResolver);
$scaleTransformation      = new \Akeneo\Component\FileTransformer\Transformation\Image\Scale($scaleResolver);

$registry = new \Akeneo\Component\FileTransformer\Transformation\TransformationRegistry();
$registry->add($resizeTransformation);
$registry->add($thumbnailTransformation);
$registry->add($resolutionTransformation);
$registry->add($colorSpaceTransformation);
$registry->add($scaleTransformation);

$transformer   = new \Akeneo\Component\FileTransformer\FileTransformer($registry);
$pathGenerator = new \Akeneo\Component\FileStorage\PathGenerator();
$filesystem    = new \League\Flysystem\Filesystem(new \League\Flysystem\Adapter\Local(DATASET));

$em = $kernel->getContainer()->get('doctrine.orm.default_entity_manager');

$metadataBuilder = $kernel->getContainer()->get('pimee_product_asset.builder.image_metadata');
$localeRepo  = $kernel->getContainer()->get('pim_catalog.repository.locale');
$channelRepo = $kernel->getContainer()->get('pim_catalog.repository.channel');
$productVarBuilder = new VariationBuilder($channelRepo);
$referenceBuilder  = new \PimEnterprise\Component\ProductAsset\Builder\ReferenceBuilder($localeRepo);


function createSplFile($imageName)
{
    $imagePath = realpath(DATASET . $imageName);
    $splFile   = new \SplFileInfo($imagePath);

    return $splFile;
}

function createPimFile(\SplFileInfo $splFile, $imageName)
{
    $pathGenerator = new \Akeneo\Component\FileStorage\PathGenerator();

    $imagePath = DATASET . $imageName;
    $mimeType  = MimeTypeGuesser::getInstance()->guess($imagePath);
    $storage   = $pathGenerator->generate($splFile);

    $pimFile = new \Akeneo\Component\FileStorage\Model\File();

    return $pimFile
        ->setFilename($storage['file_name'])
        ->setGuid($storage['guid'])
        ->setMimeType($mimeType)
        ->setOriginalFilename($imageName)
        ->setExtension(explode('.', $imageName)[1])
        ->setPath($storage['path'])
        ->setSize(filesize($imagePath))
        ->setStorage('storage');
}

function createNewAsset($key)
{
    $asset = new Asset();

    if (1 === rand(0, 1)) {
        $asset->setEndOfUseAt(new \DateTime('2050-05-25 12:12:12'));
    }

    return $asset
        ->setCode($key)
        ->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam non quam ex. Duis semper'.
            ' convallis risus at lobortis. Phasellus hendrerit auctor lectus in ullamcorper. Mauris non magna et'.
            ' tellus tempor hendrerit. Donec at lacus fringilla, rutrum enim porttitor, consectetur erat. Donec'.
            ' volutpat, nibh in hendrerit aliquet, sem massa vehicula eros, sed dictum augue tellus id nisi. Vivamus'.
            ' tempus scelerisque enim, sit amet vehicula enim scelerisque eu. Integer nec ultrices magna, sit amet'.
            ' volutpat.'
        )
        ->setCreatedAt(new \DateTime())
        ->setUpdatedAt(new \DateTime())
        ->setEnabled(true);
}

function getVariationPipeline($image)
{
    $exploded  = explode('.', $image);
    $imageName = $exploded[0];
    $ext       = '.' . $exploded[1];

    $output = $imageName . '-%s' . $ext;

    return [
        //sprintf($output, 'thumbnail') => getThumbnailVariation(),
        sprintf($output, 'ecommerce') => getEcommerceVariation(),
        sprintf($output, 'mobile')    => getMobileVariation(),
        sprintf($output, 'print')     => getPrintVariation()
    ];
}

function getThumbnailVariation()
{
    return [
        'thumbnail' => ['width' => 150, 'height' => 150],
        'colorspace' => ['colorspace' => 'gray']
    ];
}

function getEcommerceVariation()
{
    return [
        'scale' => ['ratio' => 0.5],
    ];
}

function getMobileVariation()
{
    return [
        'scale' => ['width' => 200],
        'colorspace' => ['colorspace' => 'gray'],
    ];
}

function getPrintVariation()
{
    return [
        'resize' => ['width' => 400, 'height' => 200],
    ];
}

function getChannelCode($outputName)
{
    $explodedName = explode('-', $outputName);
    $channelIndex = count($explodedName) - 1;

    return explode('.', $explodedName[$channelIndex])[0];
}

function cleanDB(EntityManager $em)
{
    $connection = $em->getConnection();
    $dbPlatform = $connection->getDatabasePlatform();

    $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');

    $truncateSql = $dbPlatform->getTruncateTableSQL('pimee_product_asset_file') . ';';
    $truncateSql .= $dbPlatform->getTruncateTableSQL('pimee_product_asset_variation') . ';';
    $truncateSql .= $dbPlatform->getTruncateTableSQL('pimee_product_asset_asset') . ';';
    $truncateSql .= $dbPlatform->getTruncateTableSQL('pimee_product_asset_reference') . ';';
    $truncateSql .= $dbPlatform->getTruncateTableSQL('pimee_product_asset_file_metadata') . ';';

    $connection->executeUpdate($truncateSql);

    $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');
}

function cleanImageDirectory()
{
    if (is_dir(DATASET . STORED)) {
        exec(sprintf('rm %s/%s -rf', DATASET, STORED));
    }
}

function cleanThumbnailDirectory()
{
    if (is_dir(DATASET . THUMBNAIL)) {
        exec(sprintf('rm %s/%s -rf', DATASET, THUMBNAIL));
    }
}

cleanDB($em);
cleanImageDirectory();
cleanThumbnailDirectory();

$images = [
//    'hammer' => [
//        'en_US' => 'hammer-en.jpg',
//        'fr_FR' => 'hammer-fr.jpg'
//    ],
//    'round-sofa' => [
//        'en_US' => 'round-sofa-en.jpg',
//        'fr_FR' => 'round-sofa-fr.jpg',
//    ],
//    'square-sofa' => [
//        'en_US' => 'square-sofa-en.jpg',
//        'fr_FR' => 'square-sofa-fr.jpg',
//    ],
//    'wrench' => [
//        'en_US' => 'wrench-en.jpg',
//        'fr_FR' => 'wrench-fr.jpg',
//    ],
    'paint' => [
        'paint.jpg',
    ],
    'chicagoskyline' => [
        'en_US' => 'chicagoskyline-en.jpg',
        'fr_FR' => 'chicagoskyline-fr.jpg',
        'de_DE' => 'chicagoskyline-de.jpg',
    ],
    'akene' => [
        'akene.jpg',
    ],
];

foreach ($images as $key => $references) {
    $asset = createNewAsset($key);

    foreach ($references as $localeCode => $imageName) {
        $locale    = $localeRepo->findOneByIdentifier($localeCode);
        $reference = $referenceBuilder->buildOne($asset, $locale);

        $splFileRef = createSplFile($imageName);
        $pimFile    = createPimFile($splFileRef, $imageName);

        $meta = $metadataBuilder->build($splFileRef);
        $meta->setFile($pimFile);

        $reference->setFile($pimFile);
        $asset->addReference($reference);

        $em->persist($meta);
        $em->persist($pimFile);
        $em->persist($reference);

        $filesystem->copy($imageName, STORED . $pimFile->getKey());

        try {
            $pipelines = getVariationPipeline($imageName);
            
            foreach ($pipelines as $outputName => $pipeline) {
                $transformer->transform($splFileRef, $pipeline, $outputName);
                /** @var \Pim\Bundle\CatalogBundle\Model\ChannelInterface $channel */
                $channel    = $channelRepo->findOneByIdentifier(getChannelCode($outputName));
                $varSplFile = createSplFile($outputName);
                $varPimFile = createPimFile($varSplFile, $outputName);

                $filesystem->copy($outputName, STORED . $varPimFile->getKey());

                $variationBuildable = ((null === $locale) || ($channel->hasLocale($reference->getLocale())));
                if (null !== $channel && $variationBuildable) {
                    $variation = $productVarBuilder->buildOne($reference, $channel);
                    $variation->setFile($varPimFile);

                    $varMeta = $metadataBuilder->build($varSplFile);
                    $varMeta->setFile($varPimFile);

                    $em->persist($varPimFile);
                    $em->persist($varMeta);
                } else {
                    //$filesystem->copy($outputName, THUMBNAIL . $pimFile->getPathname());
                }

                $filesystem->delete($outputName);

                echo "Image $outputName has been transformed and copied.\n";
            }
        } catch (NotApplicableTransformationException $e) {
            echo "Impossible to transform the image $outputName.\n";
            var_dump($e);
        }
    }

    $em->persist($asset);
}
$em->flush();
