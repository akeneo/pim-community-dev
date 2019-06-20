<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\UrlAttribute;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator\PreviewGeneratorRegistry;
use Akeneo\AssetManager\Integration\PreviewGeneratorIntegrationTestCase;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class OtherGeneratorTest extends PreviewGeneratorIntegrationTestCase
{
    /** @var PreviewGeneratorInterface */
    private $otherGenerator;

    /** @var UrlAttribute */
    private $attribute;

    public function setUp(): void
    {
        parent::setUp();

        $this->otherGenerator = $this->get('akeneo_assetmanager.application.generator.other_generator');
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_can_support_only_media_type_other_of_an_url_attribute()
    {
        $isSupported = $this->otherGenerator->supports(self::FILENAME, $this->attribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);
    }

    /**
     * @test
     */
    public function it_can_support_only_supported_type_image_of_an_url_attribute()
    {
        $isSupported = $this->otherGenerator->supports(self::FILENAME, $this->attribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertTrue($isSupported);

        $isSupported = $this->otherGenerator->supports(self::FILENAME, $this->attribute, 'preview');

        $this->assertFalse($isSupported);
    }

    /**
     * @test
     */
    public function it_get_a_default_image()
    {
        $this->otherGenerator->supports('test', $this->attribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);
        $previewImage = $this->otherGenerator->generate('test', $this->attribute, PreviewGeneratorRegistry::THUMBNAIL_TYPE);

        $this->assertStringContainsString(sprintf('media/cache/%s/pim_asset_file_other_default_image', PreviewGeneratorRegistry::THUMBNAIL_TYPE), $previewImage);
    }

    private function loadFixtures(): void
    {
        $fixtures = $this->fixturesLoader
            ->assetFamily('designer')
            ->withAttributes([
                 'video'
             ])
            ->load();
        $this->attribute = $fixtures['attributes']['video'];

        $this->fixturesLoader
            ->asset('designer', 'starck')
            ->withValues([
                 'video' => [
                     [
                         'channel' => null,
                         'locale' => null,
                         'data' => 'the-amazing-video.mov',
                     ]
                 ]
             ])
            ->load();
    }
}
