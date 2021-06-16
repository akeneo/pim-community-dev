<?php


namespace Akeneo\AssetManager\Integration\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\OptimizeJpegOperation;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ColorspaceOperationApplierTest extends AbstractOperationApplierTest
{
    const INPUT_TIFF_RGB_IMAGE =  "src/Akeneo/AssetManager/tests/back/Common/TestFixtures/lardon_rgb.tiff";
    const INPUT_TIFF_CMYK_IMAGE =  "src/Akeneo/AssetManager/tests/back/Common/TestFixtures/lardon_cmyk.tiff";


    protected function getOperationName(): string
    {
        return "Akeneo\AssetManager\Infrastructure\Transformation\Operation\ColorspaceOperationApplier";
    }

    /** @test */
    public function it_can_convert_tiff_cmyk_to_rgb()
    {
        //ColorspaceOperationApplier
        $operation = ColorspaceOperation::create(['colorspace' => 'rgb']);
        $outputFile = $this->applier->apply($this->initSourceFile(self::INPUT_TIFF_CMYK_IMAGE), $operation);
        $this->assertImagesDiff(self::INPUT_TIFF_RGB_IMAGE, $this->initWorkFile(self::INPUT_TIFF_CMYK_IMAGE), 30);
    }

    /** @test */
// TODO: fix CMYK=>RGB fails: colorspace is not changed.
//    public function it_can_convert_tiff_rgb_to_cmyk()
//    {
//        //ColorspaceOperationApplier
//        $operation = ColorspaceOperation::create(['colorspace' => 'cmyk']);
//        $outputFile = $this->applier->apply($this->initSourceFile(self::INPUT_TIFF_RGB_IMAGE), $operation);
//        $this->assertImagesDiff(self::INPUT_TIFF_CMYK_IMAGE, $this->initWorkFile(self::INPUT_TIFF_RGB_IMAGE));
//    }
}
