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
use Akeneo\AssetManager\Common\Fake\InMemoryFindAssetFamilyAttributeAsLabel;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InMemoryFindAssetFamilyAttributeAsLabelTest extends TestCase
{
    private InMemoryFindAssetFamilyAttributeAsLabel $findAssetFamilyAttributeAsLabel;

    private InMemoryAssetFamilyRepository $assetFamilyRepository;

    public function setUp(): void
    {
        $this->assetFamilyRepository = new InMemoryAssetFamilyRepository(
            new EventDispatcher()
        );
        $this->findAssetFamilyAttributeAsLabel = new InMemoryFindAssetFamilyAttributeAsLabel($this->assetFamilyRepository);
    }

    /**
     * @test
     */
    public function it_finds_the_attribute_as_label_of_an_asset_family()
    {
        $expectedAttributeAsLabel = AttributeAsLabelReference::createFromNormalized('label_designer_fingerprint');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $assetFamily = AssetFamily::createWithAttributes(
            $assetFamilyIdentifier,
            [],
            Image::createEmpty(),
            $expectedAttributeAsLabel,
            AttributeAsMainMediaReference::noReference(),
            RuleTemplateCollection::empty()
        );
        $this->assetFamilyRepository->create($assetFamily);

        $attributeAsLabel = $this->findAssetFamilyAttributeAsLabel->find($assetFamilyIdentifier);

        $this->assertSame($expectedAttributeAsLabel, $attributeAsLabel);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_attribute_as_label_if_the_asset_family_was_not_found()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('unknown');
        $attributeAsLabel = $this->findAssetFamilyAttributeAsLabel->find($assetFamilyIdentifier);

        $this->assertTrue($attributeAsLabel->isEmpty());
    }
}
