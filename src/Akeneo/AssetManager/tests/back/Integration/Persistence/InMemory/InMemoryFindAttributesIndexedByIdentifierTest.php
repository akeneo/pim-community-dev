<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Persistence\InMemory;

use Akeneo\AssetManager\Common\Fake\InMemoryAttributeRepository;
use Akeneo\AssetManager\Common\Fake\InMemoryFindAttributesIndexedByIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryFindAttributesIndexedByIdentifierTest extends TestCase
{
    private InMemoryFindAttributesIndexedByIdentifier $query;

    private InMemoryAttributeRepository $attributeRepository;

    public function setUp(): void
    {
        $this->attributeRepository = new InMemoryAttributeRepository(new EventDispatcher());
        $this->query = new InMemoryFindAttributesIndexedByIdentifier($this->attributeRepository);
    }

    /**
     * @test
     */
    public function it_returns_a_list_of_indexed_attribute()
    {
        $nonScopableNonLocalizable = $this->getNonScopableNonLocalizable();
        $nonScopableLocalizable = $this->getNonScopableLocalizable();
        $scopableLocalizable = $this->getScopableLocalizable();

        $this->attributeRepository->create($nonScopableNonLocalizable);
        $this->attributeRepository->create($nonScopableLocalizable);
        $this->attributeRepository->create($scopableLocalizable);

        $indexedAttributes = $this->query->find(AssetFamilyIdentifier::fromString('designer'));
        Assert::assertSame([
            'name_designer_fingerprint' => $nonScopableNonLocalizable,
            'description_designer_fingerprint' => $nonScopableLocalizable,
            'bio_designer_fingerprint' => $scopableLocalizable,
        ], $indexedAttributes);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_list()
    {
        $indexedAttributes = $this->query->find(AssetFamilyIdentifier::fromString('designer'));
        Assert::assertSame([], $indexedAttributes);
    }

    private function getNonScopableNonLocalizable(): AbstractAttribute
    {
        return TextAttribute::createTextarea(
            AttributeIdentifier::fromString('name_designer_fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::fromInteger(255),
            AttributeIsRichTextEditor::fromBoolean(false)
        );
    }

    private function getNonScopableLocalizable(): AbstractAttribute
    {
        return TextAttribute::createTextarea(
            AttributeIdentifier::fromString('description_designer_fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(255),
            AttributeIsRichTextEditor::fromBoolean(false)
        );
    }

    private function getScopableLocalizable(): AbstractAttribute
    {
        return TextAttribute::createTextarea(
            AttributeIdentifier::fromString('bio_designer_fingerprint'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('bio'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(255),
            AttributeIsRichTextEditor::fromBoolean(false)
        );
    }
}
