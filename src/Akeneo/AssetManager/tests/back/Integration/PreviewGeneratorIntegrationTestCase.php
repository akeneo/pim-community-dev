<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration;

use Akeneo\AssetManager\Domain\Filesystem\Storage;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * This class is used for running integration tests testing the Preview Generators.
 *
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
abstract class PreviewGeneratorIntegrationTestCase extends KernelTestCase
{
    protected const IMAGE_FILENAME = '2016/04/Fred-site-web.jpg';
    protected const DOCUMENT_FILENAME = '2016/04/1_4_user_guide.pdf';

    protected FixturesLoader $fixturesLoader;
    protected FileStorer $fileStorer;
    private CacheManager $cacheManager;

    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        $this->fixturesLoader = $this->get('akeneoasset_manager.tests.helper.fixtures_loader');
        $this->fileStorer = $this->get('akeneo_file_storage.file_storage.file.file_storer');
        $this->resetDB();
    }

    public function tearDown(): void
    {
        $this->cacheManager = $this->get('liip_imagine.cache.manager');
        $this->cacheManager->remove();
    }

    protected function get(string $service)
    {
        return self::$container->get($service);
    }

    protected function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    protected function generateJpegImage(int $size, int $quality): string
    {
        $imageFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'my_image.jpg';
        $image = imagecreate($size, $size);
        self::assertTrue(imagejpeg($image, $imageFilename, $quality));
        $fileInfo = $this->fileStorer->store(new \SplFileInfo($imageFilename), Storage::FILE_STORAGE_ALIAS);

        return base64_encode($fileInfo->getKey());
    }

    protected function generatePngImage(int $size, int $quality): string
    {
        $imageFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'my_image.png';
        $image = imagecreate($size, $size);
        imagecolorallocate($image, 255, 255, 255);
        self::assertTrue(imagepng($image, $imageFilename, $quality));
        $fileInfo = $this->fileStorer->store(new \SplFileInfo($imageFilename), Storage::FILE_STORAGE_ALIAS);

        return base64_encode($fileInfo->getKey());
    }

    protected function uploadPdfFile(): string
    {
        $path = __DIR__ . '/../../../back/Infrastructure/Symfony/Resources/fixtures/files/user_guides/1_4_user_guide.pdf';
        $fileInfo = $this->fileStorer->store(new \SplFileInfo($path), Storage::FILE_STORAGE_ALIAS);

        return base64_encode($fileInfo->getKey());
    }
}
