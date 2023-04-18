<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Get;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Get\GetGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ReadModelIdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\ReadModelIdentifierGeneratorRepository;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetGeneratorHandlerSpec extends ObjectBehavior
{
    public function let(ReadModelIdentifierGeneratorRepository $identifierGeneratorRepository)
    {
        $this->beConstructedWith($identifierGeneratorRepository);
    }

    public function it_normalizes_an_identifier_generator(ReadModelIdentifierGeneratorRepository $identifierGeneratorRepository)
    {
        $identifierGeneratorRepository->get('foo')->willReturn(
            new ReadModelIdentifierGenerator(
                IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
                IdentifierGeneratorCode::fromString('my_generator'),
                Conditions::fromArray([]),
                Structure::fromArray([
                    FreeText::fromString('AKN'),
                ]),
                LabelCollection::fromNormalized([]),
                Target::fromString('sku'),
                Delimiter::fromString('-'),
                TextTransformation::fromString('no'),
            )
        );
        $command = GetGeneratorCommand::fromCode('foo');
        $this->__invoke($command)->shouldReturn(
            [
                'uuid' => "2038e1c9-68ff-4833-b06f-01e42d206002",
                'code' => "my_generator",
                'conditions' => [],
                'structure' => [[
                    'type' => "free_text",
                    'string' => "AKN",
                ]],
                'labels' => [],
                'target' => "sku",
                'delimiter' => "-",
                'text_transformation' => "no",
            ]
        );
    }
}
