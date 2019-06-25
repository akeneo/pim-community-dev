<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\UrlAttribute;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\BinaryImageGenerator;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\AssetManager\Integration\PreviewGeneratorIntegrationTestCase;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class BinaryImageGeneratorTest extends PreviewGeneratorIntegrationTestCase
{
    /** @var PreviewGeneratorInterface */
    private $binaryImageGenerator;

    /** @var ImageAttribute */
    private $imageAttribute;

    public function setUp(): void
    {
        parent::setUp();

        $this->binaryImageGenerator = $this->get('akeneo_assetmanager.infrastructure.generator.binary_image_generator');
        $this->loadFixtures();
    }
    /**
     * @test
     */
    public function it_can_support_only_image_attribute()
    {
        $isSupported = $this->binaryImageGenerator->supports(self::FILENAME, $this->imageAttribute, BinaryImageGenerator::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);
    }

    /**
     * @test
     */
    public function it_can_support_only_supported_types_of_an_image_attribute()
    {
        $isSupported = $this->binaryImageGenerator->supports(self::FILENAME, $this->imageAttribute, BinaryImageGenerator::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->binaryImageGenerator->supports(self::FILENAME, $this->imageAttribute, BinaryImageGenerator::THUMBNAIL_SMALL_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->binaryImageGenerator->supports(self::FILENAME, $this->imageAttribute, BinaryImageGenerator::PREVIEW_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->binaryImageGenerator->supports(self::FILENAME, $this->imageAttribute, 'wrong_type');

        $this->assertFalse($isSupported);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_attribute()
    {
        $this->binaryImageGenerator->supports('google-logo.png', $this->imageAttribute, BinaryImageGenerator::THUMBNAIL_TYPE);
        $previewImage = $this->binaryImageGenerator->generate(self::FILENAME, $this->imageAttribute, BinaryImageGenerator::THUMBNAIL_TYPE);

        $this->assertStringContainsString('media/cache/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_an_image_attribute_from_the_cache()
    {
        $this->binaryImageGenerator->supports('akeneo.png', $this->imageAttribute, BinaryImageGenerator::THUMBNAIL_TYPE);
        $previewImage = $this->binaryImageGenerator->generate(self::FILENAME, $this->imageAttribute, BinaryImageGenerator::THUMBNAIL_TYPE);

        $this->assertStringContainsString('media/cache/', $previewImage);

        $previewImage = $this->binaryImageGenerator->generate(self::FILENAME, $this->imageAttribute, BinaryImageGenerator::THUMBNAIL_TYPE);

        $this->assertStringContainsString('media/cache/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_default_preview_for_an_unknown_image_url()
    {
        $this->binaryImageGenerator->supports('test', $this->imageAttribute, BinaryImageGenerator::THUMBNAIL_TYPE);
        $previewImage = $this->binaryImageGenerator->generate('test', $this->imageAttribute, BinaryImageGenerator::THUMBNAIL_TYPE);

        $this->assertStringContainsString(
            sprintf('media/cache/%s/pim_asset_manager.default_image.image', BinaryImageGenerator::THUMBNAIL_TYPE),
            $previewImage
        );
    }

    private function loadFixtures(): void
    {
        $fixtures = $this->fixturesLoader
            ->assetFamily('designer')
            ->withAttributes([
                 'main_image'
            ])
            ->load();
        $this->imageAttribute = $fixtures['attributes']['main_image'];

        $this->fixturesLoader
            ->asset('designer', 'starck')
            ->withValues([
                 'main_image' => [
                     [
                         'channel' => null,
                         'locale' => null,
                         'data' => [
                             'filePath' => self::FILENAME,
                             'originalFilename' => self::FILENAME,
                             'size' => 12,
                             'mimeType' => 'image/png',
                             'extension' => '.png'
                         ],
                     ]
                 ]
            ])
            ->load();
    }
}
