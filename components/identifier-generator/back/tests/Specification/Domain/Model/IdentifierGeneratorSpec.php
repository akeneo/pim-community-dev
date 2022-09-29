<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
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
    function let()
    {
        $identifierGeneratorCode = IdentifierGeneratorCode::fromString('abcdef');

        $freeText = FreeText::fromString('abc');
        $structure = Structure::fromArray([$freeText]);
        $conditions = Conditions::fromArray([]);

        $label = LabelCollection::fromNormalized(['fr' => 'Générateur']);
        $delimiter = Delimiter::fromString('-');
        $target = Target::fromString('sku');

        $this->beConstructedWith(
            $identifierGeneratorCode,
            $conditions,
            $structure,
            $label,
            $target,
            $delimiter,
        );
    }

    function it_is_an_identifier_generator()
    {
        $this->shouldBeAnInstanceOf(IdentifierGenerator::class);
    }

    function it_can_instantiated_without_delimiter()
    {
        $identifierGeneratorCode = IdentifierGeneratorCode::fromString('abcdef');
        $conditions = Conditions::fromArray([]);
        $freeText = FreeText::fromString('abc');
        $structure = Structure::fromArray([$freeText]);
        $label = LabelCollection::fromNormalized(['fr' => 'Générateur']);
        $target = Target::fromString('sku');

        $this->beConstructedWith(
            $identifierGeneratorCode,
            $conditions,
            $structure,
            $label,
            $target,
            null,
        );
        $this->shouldBeAnInstanceOf(IdentifierGenerator::class);
    }

    function it_returns_an_indentifier_generator_code()
    {
        $this->code()->shouldBeLike(IdentifierGeneratorCode::fromString('abcdef'));
    }
}
