<?php

namespace tests\integration\PimEnterprise\Component\ProductAsset\Normalizer\Standard;

use TestEnterprise\Integration\TestCase;

class VariationIntegration extends TestCase
{
    public function testAsset()
    {
        $asset = $this->get('pimee_product_asset.repository.asset')->findOneByIdentifier('dog');
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');

        $file = new \SplFileInfo($this->getParameter('kernel.root_dir') . '/../features/Context/fixtures/dog.jpg');
        $fileInfo = $this->get('akeneo_file_storage.file_storage.file_info_factory')
            ->createFromRawFile($file, 'assetStorage');

        $reference = $asset->getReferences()->first();
        $reference->setFileInfo($fileInfo);
        $reference->setLocale($this->get('pim_catalog.repository.locale')->findOneByIdentifier('en_US'));
        $variation = $this->get('pimee_product_asset.builder.variation')->buildOne($reference, $channel);
        $variation->setFileInfo($fileInfo);

        $expected = [
            'code'           => $fileInfo->getKey(),
            'asset'          => 'dog',
            'locale'         => 'en_US',
            'channel'        => 'ecommerce',
            'reference_file' => $fileInfo->getKey()
        ];

        $serializer = $this->get('pim_serializer');
        $result = $serializer->normalize($variation, 'standard');

        $this->assertSame($result, $expected);
    }
}
