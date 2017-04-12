<?php

namespace spec\Pim\Bundle\DataGridBundle\Normalizer\Product;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Pim\Component\Catalog\ProductValue\MediaProductValueInterface;
use PhpSpec\ObjectBehavior;

class FileNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGridBundle\Normalizer\Product\FileNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_datagrid_format_and_product_value(MediaProductValueInterface $productValue)
    {
        $this->supportsNormalization($productValue, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($productValue, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_a_media_product_value(
        MediaProductValueInterface $productValue,
        FileInfoInterface $fileInfo
    ) {
        $productValue->getData()->willReturn($fileInfo);
        $fileInfo->getOriginalFilename()->willReturn('cat.jpg');
        $fileInfo->getKey()->willReturn('1/2/3/4/zertyj_cat.jpg');
        $productValue->getLocale()->willReturn(null);
        $productValue->getScope()->willReturn(null);

        $data =  [
            'locale' => null,
            'scope'  => null,
            'data'   => [
                'originalFilename' => 'cat.jpg',
                'filePath'         => '1/2/3/4/zertyj_cat.jpg'
            ]
        ];

        $this->normalize($productValue, 'datagrid')->shouldReturn($data);
    }
}
