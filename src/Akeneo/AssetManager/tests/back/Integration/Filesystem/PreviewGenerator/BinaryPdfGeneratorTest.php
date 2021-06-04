<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\BinaryPdfGenerator;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\AssetManager\Integration\PreviewGeneratorIntegrationTestCase;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class BinaryPdfGeneratorTest extends PreviewGeneratorIntegrationTestCase
{
    private PreviewGeneratorInterface $binaryPdfGenerator;

    private MediaFileAttribute $mediaFileAttribute;

    public function setUp(): void
    {
        parent::setUp();

        $this->binaryPdfGenerator = $this->get('akeneo_assetmanager.infrastructure.generator.binary_pdf_generator');
        $this->loadFixtures();
    }
    /**
     * @test
     */
    public function it_can_support_only_media_file_attribute()
    {
        $isSupported = $this->binaryPdfGenerator->supports(self::DOCUMENT_FILENAME, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);
    }

    /**
     * @test
     */
    public function it_can_support_only_supported_types_of_a_media_file_attribute()
    {
        $isSupported = $this->binaryPdfGenerator->supports(self::DOCUMENT_FILENAME, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->binaryPdfGenerator->supports(self::DOCUMENT_FILENAME, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_SMALL_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->binaryPdfGenerator->supports(self::DOCUMENT_FILENAME, $this->mediaFileAttribute, PreviewGeneratorRegistry::PREVIEW_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->binaryPdfGenerator->supports(self::DOCUMENT_FILENAME, $this->mediaFileAttribute, 'wrong_type');

        $this->assertFalse($isSupported);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_a_media_file_attribute()
    {
        $previewImage = $this->binaryPdfGenerator->generate(self::DOCUMENT_FILENAME, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_preview_for_a_media_file_attribute_from_the_cache()
    {
        $previewImage = $this->binaryPdfGenerator->generate(self::DOCUMENT_FILENAME, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/', $previewImage);

        $previewImage = $this->binaryPdfGenerator->generate(self::DOCUMENT_FILENAME, $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString('__root__/thumbnail/asset_manager/', $previewImage);
    }

    /**
     * @test
     */
    public function it_get_a_default_preview_for_an_unknown_document_media_link()
    {
        $previewImage = $this->binaryPdfGenerator->generate('test', $this->mediaFileAttribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString(
            sprintf('__root__/thumbnail/asset_manager/%s/pim_asset_manager.default_image.image', BinaryPdfGenerator::SUPPORTED_TYPES[PreviewGeneratorRegistry::THUMBNAIL_TYPE]),
            $previewImage
        );
    }

    private function loadFixtures(): void
    {
        $fixtures = $this->fixturesLoader
            ->assetFamily('designer')
            ->withAttributes(['main_document'])
            ->load();
        $this->mediaFileAttribute = $fixtures['attributes']['main_document'];

        $this->fixturesLoader
            ->asset('designer', 'starck')
            ->withValues([
                 'main_document' => [
                     [
                         'channel' => null,
                         'locale' => null,
                         'data' => [
                             'filePath' => self::DOCUMENT_FILENAME,
                             'originalFilename' => self::DOCUMENT_FILENAME,
                             'size' => 12,
                             'mimeType' => 'application/pdf',
                             'extension' => '.pdf',
                             'updatedAt' => '2019-11-22T15:16:21+0000',
                         ],
                     ]
                 ]
            ])
            ->load();
    }
}
