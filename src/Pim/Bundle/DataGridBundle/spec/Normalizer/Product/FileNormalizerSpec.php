<?php

namespace spec\Pim\Bundle\DataGridBundle\Normalizer\Product;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Value\MediaValueInterface;

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

    function it_supports_datagrid_format_and_product_value(MediaValueInterface $value)
    {
        $this->supportsNormalization($value, 'datagrid')->shouldReturn(true);
        $this->supportsNormalization($value, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'datagrid')->shouldReturn(false);
    }

    function it_normalizes_a_media_product_value(
        MediaValueInterface $value,
        FileInfoInterface $fileInfo
    ) {
        $value->getData()->willReturn($fileInfo);
        $fileInfo->getOriginalFilename()->willReturn('cat.jpg');
        $fileInfo->getKey()->willReturn('1/2/3/4/zertyj_cat.jpg');
        $value->getLocale()->willReturn(null);
        $value->getScope()->willReturn(null);

        $data =  [
            'locale' => null,
            'scope'  => null,
            'data'   => [
                'originalFilename' => 'cat.jpg',
                'filePath'         => '1/2/3/4/zertyj_cat.jpg'
            ]
        ];

        $this->normalize($value, 'datagrid')->shouldReturn($data);
    }
}
