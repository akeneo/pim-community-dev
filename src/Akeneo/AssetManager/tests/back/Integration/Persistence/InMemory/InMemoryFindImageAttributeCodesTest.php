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

use Akeneo\AssetManager\Common\Fake\InMemoryAttributeRepository;
use Akeneo\AssetManager\Common\Fake\InMemoryFindAttributesIndexedByIdentifier;
use Akeneo\AssetManager\Common\Fake\InMemoryFindImageAttributeCodes;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InMemoryFindImageAttributeCodesTest extends TestCase
{
    /** @var InMemoryFindAttributesIndexedByIdentifier */
    private $findImageAttributeCodes;

    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    public function setUp(): void
    {
        $this->attributeRepository = new InMemoryAttributeRepository(new EventDispatcher());
        $this->findImageAttributeCodes = new InMemoryFindImageAttributeCodes($this->attributeRepository);
    }

    /**
     * @test
     */
    public function it_returns_the_codes_of_the_image_attributes_for_a_given_asset_family()
    {
        $this->loadAttributesWithImageType();

        $imageAttributeCodes = $this->findImageAttributeCodes->find(AssetFamilyIdentifier::fromString('designer'));
        $expectedCodes = [
            AttributeCode::fromString('image'),
            AttributeCode::fromString('second_image')
        ];

        $this->assertEquals($expectedCodes, $imageAttributeCodes);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_there_is_no_image_attribute()
    {
        $this->loadAttributesWithoutImageType();

        $imageAttributeCodes = $this->findImageAttributeCodes->find(AssetFamilyIdentifier::fromString('designer'));

        $this->assertSame([], $imageAttributeCodes);
    }

    private function loadAttributesWithImageType(): void
    {
        $this->loadAttributesWithoutImageType();

        $imageAttribute = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'image', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray(['en_US' => 'Portrait']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('1000'),
            AttributeAllowedExtensions::fromList(['pdf'])
        );

        $secondImageAttribute = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'second_image', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('second_image'),
            LabelCollection::fromArray(['en_US' => 'Portrait']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('1000'),
            AttributeAllowedExtensions::fromList(['pdf'])
        );

        $this->attributeRepository->create($imageAttribute);
        $this->attributeRepository->create($secondImageAttribute);
    }

    private function loadAttributesWithoutImageType()
    {
        $name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $email = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'email', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('email'),
            LabelCollection::fromArray(['en_US' => 'Email']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::fromString(AttributeValidationRule::EMAIL),
            AttributeRegularExpression::createEmpty()
        );

        $this->attributeRepository->create($name);
        $this->attributeRepository->create($email);
    }
}
