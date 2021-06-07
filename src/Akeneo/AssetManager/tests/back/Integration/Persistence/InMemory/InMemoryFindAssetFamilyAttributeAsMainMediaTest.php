<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\Persistence\InMemory;

use Akeneo\AssetManager\Common\Fake\InMemoryAssetFamilyRepository;
use Akeneo\AssetManager\Common\Fake\InMemoryFindAssetFamilyAttributeAsMainMedia;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InMemoryFindAssetFamilyAttributeAsMainMediaTest extends TestCase
{
    private InMemoryFindAssetFamilyAttributeAsMainMedia $findAssetFamilyAttributeAsMainMedia;

    private InMemoryAssetFamilyRepository $assetFamilyRepository;

    public function setUp(): void
    {
        $this->assetFamilyRepository = new InMemoryAssetFamilyRepository(
            new EventDispatcher()
        );
        $this->findAssetFamilyAttributeAsMainMedia = new InMemoryFindAssetFamilyAttributeAsMainMedia($this->assetFamilyRepository);
    }

    /**
     * @test
     */
    public function it_finds_the_attribute_as_main_media_of_an_asset_family()
    {
        $expectedAttributeAsMainMedia = AttributeAsMainMediaReference::fromAttributeIdentifier(
            AttributeIdentifier::fromString('image')
        );
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $assetFamily = AssetFamily::createWithAttributes(
            $assetFamilyIdentifier,
            [],
            Image::createEmpty(),
            AttributeAsLabelReference::noReference(),
            $expectedAttributeAsMainMedia,
            RuleTemplateCollection::empty()
        );
        $this->assetFamilyRepository->create($assetFamily);

        $attributeAsMainMedia = $this->findAssetFamilyAttributeAsMainMedia->find($assetFamilyIdentifier);

        $this->assertSame($expectedAttributeAsMainMedia, $attributeAsMainMedia);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_attribute_as_main_media_if_the_asset_family_was_not_found()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('unknown');
        $attributeAsMainMedia = $this->findAssetFamilyAttributeAsMainMedia->find($assetFamilyIdentifier);

        $this->assertTrue($attributeAsMainMedia->isEmpty());
    }
}
