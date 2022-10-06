<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\CreateGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateGeneratorHandlerSpec extends ObjectBehavior
{
    function let(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
    ) {
        $this->beConstructedWith($identifierGeneratorRepository);
    }

    function it_implements_create_generator_handler(): void
    {
        $this->shouldImplement(CreateGeneratorHandler::class);
    }

    function it_must_call_save_repository(IdentifierGeneratorRepository $identifierGeneratorRepository): void
    {
        $command = new CreateGeneratorCommand(
            '2038e1c9-68ff-4833-b06f-01e42d206002',
            'abcdef',
            [],
            [FreeText::fromString('abcdef')],
            ['fr' => 'Générateur'],
            'sku',
            '-'
        );

        $this->__invoke($command);

        $identifierGeneratorRepository->save(Argument::type(IdentifierGenerator::class))->shouldHaveBeenCalled();
    }
}
