<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierGeneratorSpec extends ObjectBehavior
{
    public function let(): void
    {
        $identifierGeneratorId = IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002');
        $identifierGeneratorCode = IdentifierGeneratorCode::fromString('abcdef');

        $freeText = FreeText::fromString('abc');
        $enabled = Enabled::fromBoolean(true);
        $structure = Structure::fromArray([$freeText]);
        $conditions = Conditions::fromArray([$enabled]);

        $label = LabelCollection::fromNormalized(['fr' => 'Générateur']);
        $delimiter = Delimiter::fromString('-');
        $target = Target::fromString('sku');

        $this->beConstructedWith(
            $identifierGeneratorId,
            $identifierGeneratorCode,
            $conditions,
            $structure,
            $label,
            $target,
            $delimiter,
        );
    }

    public function it_is_an_identifier_generator(): void
    {
        $this->shouldBeAnInstanceOf(IdentifierGenerator::class);
    }

    public function it_can_instantiated_without_delimiter(): void
    {
        $identifierGeneratorId = IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002');
        $identifierGeneratorCode = IdentifierGeneratorCode::fromString('abcdef');
        $conditions = Conditions::fromArray([]);
        $freeText = FreeText::fromString('abc');
        $structure = Structure::fromArray([$freeText]);
        $label = LabelCollection::fromNormalized(['fr' => 'Générateur']);
        $target = Target::fromString('sku');

        $this->beConstructedWith(
            $identifierGeneratorId,
            $identifierGeneratorCode,
            $conditions,
            $structure,
            $label,
            $target,
            null,
        );
        $this->shouldBeAnInstanceOf(IdentifierGenerator::class);
    }

    public function it_returns_an_indentifier_generator_id(): void
    {
        $this->id()->shouldBeLike(IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'));
    }

    public function it_returns_an_indentifier_generator_code(): void
    {
        $this->code()->shouldBeLike(IdentifierGeneratorCode::fromString('abcdef'));
    }

    public function it_returns_a_delimiter(): void
    {
        $this->delimiter()->shouldBeLike(Delimiter::fromString('-'));
    }

    public function it_sets_a_delimiter(): void
    {
        $this->delimiter()->asString()->shouldBeLike('-');
        $this->setDelimiter(Delimiter::fromString('='));
        $this->delimiter()->asString()->shouldBeLike('=');
    }

    public function it_returns_a_target(): void
    {
        $this->target()->shouldBeLike(Target::fromString('sku'));
    }

    public function it_sets_a_target(): void
    {
        $this->target()->asString()->shouldBeLike('sku');
        $this->setTarget(Target::fromString('gtin'));
        $this->target()->asString()->shouldBeLike('gtin');
    }

    public function it_returns_a_conditions(): void
    {
        $this->conditions()->shouldBeLike(Conditions::fromArray([Enabled::fromBoolean(true)]));
    }

    public function it_returns_a_structure(): void
    {
        $this->structure()->shouldBeLike(Structure::fromArray([FreeText::fromString('abc')]));
    }

    public function it_sets_a_structure(): void
    {
        $this->structure()->shouldBeLike(Structure::fromArray([FreeText::fromString('abc')]));
        $this->setStructure(Structure::fromArray([
            FreeText::fromString('cba'),
            AutoNumber::fromValues(3, 2),
        ]));
        $this->structure()->shouldBeLike(Structure::fromArray([
            FreeText::fromString('cba'),
            AutoNumber::fromValues(3, 2),
        ]));
    }

    public function it_returns_a_labels_collection(): void
    {
        $this->labelCollection()->shouldBeLike(LabelCollection::fromNormalized(['fr' => 'Générateur']));
    }

    public function it_sets_a_labels_collection(): void
    {
        $this->labelCollection()->shouldBeLike(LabelCollection::fromNormalized(['fr' => 'Générateur']));
        $this->setLabelCollection(LabelCollection::fromNormalized([
            'fr' => 'Générateur',
            'en' => 'generator',
        ]));
        $this->labelCollection()->shouldBeLike(LabelCollection::fromNormalized([
            'fr' => 'Générateur',
            'en' => 'generator',
        ]));
    }

    public function it_can_be_normalized(): void
    {
        $this->normalize()->shouldReturn([
            'uuid' => '2038e1c9-68ff-4833-b06f-01e42d206002',
            'code' => 'abcdef',
            'conditions' => [
                [
                    'type' => 'enabled',
                    'value' => true,
                ]
            ],
            'structure' => [
                [
                    'type' => 'free_text',
                    'string' => 'abc',
                ],
            ],
            'labels' => [
                'fr' => 'Générateur',
            ],
            'target' => 'sku',
            'delimiter' => '-',
        ]);
    }

    public function it_should_match_empty_identifier(): void
    {
        $this->match(new ProductProjection(
            '',
            true,
            '',
            [],
        ))->shouldReturn(true);
    }

    public function it_should_match_null_identifier(): void
    {
        $this->match(new ProductProjection(
            null,
            true,
            '',
            [],
        ))->shouldReturn(true);
    }

    public function it_should_not_match_filled_identifier(): void
    {
        $this->match(new ProductProjection(
            'a_product_identifier',
            true,
            '',
            [],
        ))->shouldReturn(false);
    }

    public function it_should_not_match_disabled_product(): void
    {
        $this->match(new ProductProjection(
            null,
            false,
            '',
            [],
        ))->shouldReturn(false);
    }
}
