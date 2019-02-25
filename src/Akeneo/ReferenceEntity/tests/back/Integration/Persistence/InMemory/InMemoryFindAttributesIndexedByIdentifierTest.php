<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryAttributeRepository;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindAttributesIndexedByIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
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
    /** @var InMemoryFindAttributesIndexedByIdentifier */
    private $query;

    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

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

        $indexedAttributes = ($this->query)(ReferenceEntityIdentifier::fromString('designer'));
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
        $indexedAttributes = ($this->query)(ReferenceEntityIdentifier::fromString('designer'));
        Assert::assertSame([], $indexedAttributes);
    }

    private function getNonScopableNonLocalizable(): AbstractAttribute
    {
        return TextAttribute::createTextarea(
            AttributeIdentifier::fromString('name_designer_fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
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
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
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
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('bio'),
            LabelCollection::fromArray(['en_US' => 'Name', 'fr_FR' => 'Nom']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(255),
            AttributeIsRichTextEditor::fromBoolean(false)
        );
    }
}
