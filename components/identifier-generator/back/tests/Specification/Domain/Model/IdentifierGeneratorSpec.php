<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
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
    public function let()
    {
        $identifierGeneratorId = IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002');
        $identifierGeneratorCode = IdentifierGeneratorCode::fromString('abcdef');

        $freeText = FreeText::fromString('abc');
        $structure = Structure::fromArray([$freeText]);
        $conditions = Conditions::fromArray([]);

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

    public function it_is_an_identifier_generator()
    {
        $this->shouldBeAnInstanceOf(IdentifierGenerator::class);
    }

    public function it_can_instantiated_without_delimiter()
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

    public function it_returns_an_indentifier_generator_id()
    {
        $this->id()->shouldBeLike(IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'));
    }

    public function it_returns_an_indentifier_generator_code()
    {
        $this->code()->shouldBeLike(IdentifierGeneratorCode::fromString('abcdef'));
    }

    public function it_returns_a_delimiter()
    {
        $this->delimiter()->shouldBeLike(Delimiter::fromString('-'));
    }

    public function it_returns_a_target()
    {
        $this->target()->shouldBeLike(Target::fromString('sku'));
    }

    public function it_returns_a_conditions()
    {
        $this->conditions()->shouldBeLike(Conditions::fromArray([]));
    }

    public function it_returns_a_structure()
    {
        $this->structure()->shouldBeLike(Structure::fromArray([FreeText::fromString('abc')]));
    }

    public function it_returns_a_labels_collection()
    {
        $this->labelCollection()->shouldBeLike(LabelCollection::fromNormalized(['fr' => 'Générateur']));
    }
}
