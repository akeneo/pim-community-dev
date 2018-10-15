<?php

namespace AkeneoTestEnterprise\Asset\Integration\Normalizer\Standard;

use AkeneoTestEnterprise\Asset\Integration\Normalizer\Standard\AbstractStandardNormalizerTestCase;

class VariationIntegration extends AbstractStandardNormalizerTestCase
{
    public function testAsset()
    {
        $asset = $this->get('pimee_product_asset.repository.asset')->findOneByIdentifier('dog');
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');

        $file = new \SplFileInfo($this->getParameter('kernel.root_dir') . '/../tests/legacy/features/Context/fixtures/dog.jpg');
        $fileInfo = $this->get('akeneo_file_storage.file_storage.file_info_factory')
            ->createFromRawFile($file, 'assetStorage');

        $reference = $asset->getReferences()->first();
        $reference->setFileInfo($fileInfo);
        $reference->setLocale($this->get('pim_catalog.repository.locale')->findOneByIdentifier('en_US'));
        $variation = $this->get('pimee_product_asset.builder.variation')->buildOne($reference, $channel);
        $variation->setFileInfo($fileInfo);

        $expected = [
            'asset'          => 'dog',
            'code'           => $fileInfo->getKey(),
            'locale'         => 'en_US',
            'channel'        => 'ecommerce',
            'reference_file' => $fileInfo->getKey()
        ];

        $serializer = $this->get('pim_standard_format_serializer');
        $result = $serializer->normalize($variation, 'standard');

        $this->assertSame($expected, $result);
    }
}
