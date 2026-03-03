<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\tests\Integration;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\FileStorageBundle\Validator\Constraints;
use Symfony\Component\HttpFoundation\File;

final class UploadImageIntegration extends TestCase
{
    /** @test */
    public function it_does_not_trigger_any_violation(): void
    {
        $fileInfo = new \SplFileInfo($this->getFixturePath('akeneo.png'));

        $fileToUpload = new File\UploadedFile(
            $fileInfo->getPathname(),
            $fileInfo->getFilename(),
            'image/png'
        );

        $constraint = new Constraints\UploadedFile([
            'types' => [
                'png' => ['image/png'],
            ]
        ]);

        $violations = $this->get('validator')->validate($fileToUpload, $constraint);

        $this->assertCount(0, $violations);
    }

    /**
     * @test
     * @dataProvider unsupportedFilesProvider
     */
    public function it_triggers_violation_when_type_is_not_supported(string $fileName): void
    {
        $fileInfo = new \SplFileInfo($this->getSecurityFixturePath($fileName));

        $fileToUpload = new File\UploadedFile(
            $fileInfo->getPathname(),
            $fileInfo->getFilename(),
            $fileInfo->getType(),
        );

        $constraint = new Constraints\UploadedFile([
            'types' => [
                'png' => ['image/png'],
                'jpg' => ['image/jpg', 'image/jpeg'],
            ]
        ]);

        $violations = $this->get('validator')->validate($fileToUpload, $constraint);

        $this->assertTrue(count($violations) > 0);
    }

    private function unsupportedFilesProvider(): array
    {
        return [
            ['example.html'],
            ['example.html%00.png'],
            ['example.jpg.php'],
            ['example.php'],
            ['example.php.jpg'],
            ['example_magic_bytes.jpg'],
        ];
    }

    private function getSecurityFixturePath(string $fileName): string
    {
        return __DIR__.'/fixtures/'.$fileName;
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
