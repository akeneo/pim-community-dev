<?php

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Match;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition\MatchEmptyIdentifierHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition\MatchEnabledHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition\MatchFamilyHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\MatchIdentifierGeneratorQuery;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use PhpSpec\ObjectBehavior;

class MatchIdentifierGeneratorHandlerSpec extends ObjectBehavior
{
    public function let(
    ): void {
        $this->beConstructedWith(new \ArrayIterator([
            new MatchEmptyIdentifierHandler(),
            new MatchFamilyHandler(),
            new MatchEnabledHandler(),
        ]));
    }

    public function it_should_match_all_conditions()
    {
        $trueQuery = new MatchIdentifierGeneratorQuery(
            $this->getIdentifierGenerator(),
            new ProductProjection(true, 'myfamily', [], [])
        );
        $notEnabledQuery = new MatchIdentifierGeneratorQuery(
            $this->getIdentifierGenerator(),
            new ProductProjection(false, 'myfamily', [], [])
        );
        // This command should return false because of the implicit condition from family structure
        $noFamilyQuery = new MatchIdentifierGeneratorQuery(
            $this->getIdentifierGenerator(),
            new ProductProjection(true, null, [], [])
        );

        $this->__invoke($trueQuery)->shouldReturn(true);
        $this->__invoke($notEnabledQuery)->shouldReturn(false);
        $this->__invoke($noFamilyQuery)->shouldReturn(false);
    }

    private function getIdentifierGenerator(): IdentifierGenerator
    {
        return new IdentifierGenerator(
            IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
            IdentifierGeneratorCode::fromString('my_generator'),
            Conditions::fromArray([
                Enabled::fromBoolean(true),
            ]),
            Structure::fromArray([
                FreeText::fromString('AKN'),
                FamilyProperty::fromNormalized([
                    'type' => FamilyProperty::type(),
                    'process' => [
                        'type' => 'no',
                    ]
                ]),
            ]),
            LabelCollection::fromNormalized(['fr' => 'Mon générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
            TextTransformation::fromString('no'),
        );
    }
}
