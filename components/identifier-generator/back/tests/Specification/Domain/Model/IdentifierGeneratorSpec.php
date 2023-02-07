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
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
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
        $family = FamilyProperty::fromNormalized(['type' => 'family', 'process' => ['type' => 'no']]);
        $enabled = Enabled::fromBoolean(true);
        $structure = Structure::fromArray([$freeText, $family]);
        $conditions = Conditions::fromArray([$enabled]);

        $label = LabelCollection::fromNormalized(['fr' => 'Générateur']);
        $delimiter = Delimiter::fromString('-');
        $target = Target::fromString('sku');
        $textTransformation = TextTransformation::fromString('no');

        $this->beConstructedWith(
            $identifierGeneratorId,
            $identifierGeneratorCode,
            $conditions,
            $structure,
            $label,
            $target,
            $delimiter,
            $textTransformation,
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
        $textTransformation = TextTransformation::fromString('no');

        $this->beConstructedWith(
            $identifierGeneratorId,
            $identifierGeneratorCode,
            $conditions,
            $structure,
            $label,
            $target,
            null,
            $textTransformation,
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
        $this->structure()->shouldBeLike(Structure::fromArray([
            FreeText::fromString('abc'),
            FamilyProperty::fromNormalized(['type' => 'family', 'process' => ['type' => 'no']]),
        ]));
    }

    public function it_sets_a_structure(): void
    {
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
                ], [
                    'type' => 'family',
                    'process' => [
                        'type' => 'no',
                    ],
                ],
            ],
            'labels' => [
                'fr' => 'Générateur',
            ],
            'target' => 'sku',
            'delimiter' => '-',
            'text_transformation' => 'no',
        ]);
    }

    public function it_should_add_conditions_from_structure(): void
    {
        // Structure contains Family property, which imply family should not be empty.
        $this->match(new ProductProjection(
            true,
            'a_family',
            [],
        ))->shouldReturn(true);

        $this->match(new ProductProjection(
            true,
            null,
            [],
        ))->shouldReturn(false);
    }
}
