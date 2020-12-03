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

use Akeneo\AssetManager\Common\Fake\InMemoryFindActivatedLocales;
use Akeneo\AssetManager\Common\Fake\InMemoryFindAttributesDetails;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeDetails;
use PHPUnit\Framework\TestCase;

class InMemoryFindAttributesDetailsTest extends TestCase
{
    /** @var InMemoryFindAttributesDetails */
    private $query;

    /** @var InMemoryFindActivatedLocales */
    private $activatedLocaleQuery;

    public function setUp(): void
    {
        $this->activatedLocaleQuery = new InMemoryFindActivatedLocales();
        $this->query = new InMemoryFindAttributesDetails($this->activatedLocaleQuery);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_there_are_no_attributes_for_the_given_asset_family_identifier()
    {
        $manufacturerIdentifier = AssetFamilyIdentifier::fromString('manufacturer');
        $this->assertEmpty($this->query->find($manufacturerIdentifier));
    }

    private function createAssetFamilyDetails(string $assetFamilyIdentifier, string $attributeCode): AttributeDetails
    {
        $textAttributeDetails = new AttributeDetails();
        $textAttributeDetails->assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        $textAttributeDetails->code = AttributeCode::fromString($attributeCode);

        return $textAttributeDetails;
    }
}
