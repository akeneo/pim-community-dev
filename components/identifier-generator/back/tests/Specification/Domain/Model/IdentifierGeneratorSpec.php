<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

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

        $label = LabelCollection::fromNormalized(['fr' => 'Générateur']);
        $delimiter = Delimiter::fromString('-');
        $target = Target::fromString('sku');

        $this->beConstructedThrough('fromValues', [
            $identifierGeneratorCode,
            $structure,
            $label,
            $delimiter,
            $target,
        ]);
    }

    function it_is_a_identifier_generator()
    {
        $this->shouldBeAnInstanceOf(IdentifierGenerator::class);
    }

    function it_can_instantiate_without_delimiter()
    {
        $identifierGeneratorCode = IdentifierGeneratorCode::fromString('abcdef');

        $freeText = FreeText::fromString('abc');
        $structure = Structure::fromArray([$freeText]);

        $label = LabelCollection::fromNormalized(['fr' => 'Générateur']);
        $target = Target::fromString('sku');

        $this->beConstructedThrough('fromValues', [
            $identifierGeneratorCode,
            $structure,
            $label,
            null,
            $target,
        ]);
        $this->shouldBeAnInstanceOf(IdentifierGenerator::class);
    }
}
