<?php


namespace Akeneo\AssetManager\Integration\Transformation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\OptimizeJpegOperation;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\ColorspaceOperationApplier;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\OperationApplier;
use Akeneo\AssetManager\Infrastructure\Transformation\Operation\OptimizeJpegOperationApplier;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\Transformation\Operation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractOperationApplierTest extends KernelTestCase
{
    const VAR_CACHE_TESTS = "./var/cache/test/integration";
    protected OperationApplier $applier;
    private Filesystem $filesystem;
    private string $testDir;

    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        $this->filesystem = new Filesystem();
        $this->applier = self::$container->get($this->getOperationName());

        $this->testDir =
            self::VAR_CACHE_TESTS . DIRECTORY_SEPARATOR
            . random_int(0, 1000000000) . DIRECTORY_SEPARATOR;
        $this->filesystem->mkdir($this->testDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->testDir);
    }

    public function initSourceFile(string $filePath): File
    {
        $inputFilename = $this->initWorkFile($filePath);
        $this->filesystem->copy($filePath, $inputFilename);
        return new File($inputFilename);
    }

    abstract protected function getOperationName(): string;

    protected function initWorkFile(string $filePath): string
    {
        return $this->testDir . basename($filePath);
    }

    protected function compareImages(string $expectedFileName, string $resultFileName): float
    {
        $expectedImage = new \Imagick($expectedFileName);
        $returnedImage = new \Imagick($resultFileName);
        $comparison =  $expectedImage->compareImages($returnedImage, \Imagick::METRIC_PEAKSIGNALTONOISERATIO);
        return $comparison[1];
    }

    protected function assertImagesDiff(string $expectedFileName, string $resultFileName, float $Noise = 40)
    {
        $diffRate = $this->compareImages($expectedFileName, $resultFileName);
        self::assertTrue($diffRate >  $Noise, "Assertion: $diffRate > $Noise (lower noise bound limit) failed: while running comparing expected {$expectedFileName} to {$resultFileName}'");
    }
}
