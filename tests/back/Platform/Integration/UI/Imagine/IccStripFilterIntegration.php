<?php

namespace AkeneoTest\Platform\Integration\UI\Imagine;

use Akeneo\Platform\Bundle\UIBundle\Imagine\IccStripFilter;
use Imagine\Imagick\Image;
use Imagine\Imagick\Imagine;
use PHPUnit\Framework\TestCase;

class IccStripFilterIntegration extends TestCase
{
    /**
     * @dataProvider fileNameProvider
     */
    public function testResolutionMetadataIsKept(string $fileName): void
    {
        $sut = new IccStripFilter();

        $image = (new Imagine())->open(__DIR__ . '/fixtures/' .$fileName);

        $this->assertInstanceOf(Image::class, $image);

        $dpi = $image->getImagick()->getImageResolution();
        $unit = $image->getImagick()->getImageUnits();

        $result = $sut->load($image, ['keep_resolution_metadata' => true]);

        $this->assertInstanceOf(Image::class, $result);

        // Metadata modifications are only saved by writing the image content
        $imagick = new \Imagick();
        $imagick->readImageBlob($result->getImagick()->getImageBlob());

        $this->assertSame($dpi, $imagick->getImageResolution());
        $this->assertSame($unit, $imagick->getImageUnits());
    }

    public function fileNameProvider(): array
    {
        return [
            'jpg' => ['300_dpi.jpg'],
            'png' => ['300_dpi.png'],
            'tif' => ['300_dpi.tif'],
        ];
    }
}
