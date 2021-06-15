<?php


namespace Akeneo\AssetManager\Integration\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\OptimizeJpegOperation;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptimizeJpegOperationApplierTest extends AbstractOperationApplierTest
{
    const INPUT_IMAGE = "src/Akeneo/AssetManager/tests/back/Common/TestFixtures/lardon_rgb.tiff";
    const OUTPUT_JPG_IMAGE = "src/Akeneo/AssetManager/tests/back/Common/TestFixtures/lardon.jpeg";

    protected function getOperationName(): string
    {
        return "Akeneo\AssetManager\Infrastructure\Transformation\Operation\OptimizeJpegOperationApplier";
    }

    /** @test */
    public function it_can_convert_tiff_to_jpg()
    {
        //ColorspaceOperationApplier
        $operation = OptimizeJpegOperation::create(['quality' => 100]);
        $outputFile = $this->applier->apply($this->initSourceFile(self::INPUT_IMAGE), $operation);
        $this->assertImagesDiff($outputFile->getPathname(), self::OUTPUT_JPG_IMAGE);
    }
}
