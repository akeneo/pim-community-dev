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

use Akeneo\AssetManager\Common\Fake\InMemoryAssetFamilyIsLinkedToAtLeastOneProductAttribute;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use PHPUnit\Framework\TestCase;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class InMemoryAssetFamilyIsLinkedToAtLeastOneProductAttributeTest extends TestCase
{
    private InMemoryAssetFamilyIsLinkedToAtLeastOneProductAttribute $query;

    public function setUp(): void
    {
        parent::setUp();
        $assetFamilyattribute = new Attribute();
        $assetFamilyattribute->setCode('main_designer');
        $assetFamilyattribute->setType(AssetCollectionType::ASSET_COLLECTION);
        $assetFamilyattribute->setProperties([
            'reference_data_name' => 'designer'
        ]);

        $textareaAttribute = new Attribute();
        $textareaAttribute->setCode('description');
        $textareaAttribute->setType(AttributeTypes::TEXTAREA);

        $inMemoryAttributeRepository = new InMemoryAttributeRepository();
        $inMemoryAttributeRepository->save($assetFamilyattribute);
        $inMemoryAttributeRepository->save($textareaAttribute);

        $this->query = new InMemoryAssetFamilyIsLinkedToAtLeastOneProductAttribute($inMemoryAttributeRepository);
    }

    /**
     * @test
     */
    public function it_tells_if_an_asset_family_is_linked_to_at_least_one_product_attribute()
    {
        $identifier = AssetFamilyIdentifier::fromString('designer');
        $isLinked = $this->query->isLinked($identifier);
        $this->assertTrue($isLinked);

        $identifier = AssetFamilyIdentifier::fromString('brand');
        $isLinked = $this->query->isLinked($identifier);
        $this->assertFalse($isLinked);
    }
}
