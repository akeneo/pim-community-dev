<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Create;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Create\CreateGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\CommandValidatorInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateGeneratorHandlerSpec extends ObjectBehavior
{
    public function let(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        CommandValidatorInterface $validator
    ): void {
        $this->beConstructedWith($identifierGeneratorRepository, $validator);
    }

    public function it_implements_create_generator_handler(): void
    {
        $this->shouldImplement(CreateGeneratorHandler::class);
    }

    public function it_must_call_save_repository(IdentifierGeneratorRepository $identifierGeneratorRepository, CommandValidatorInterface $validator): void
    {
        $command = new CreateGeneratorCommand(
            'abcdef',
            [],
            [['type' => 'free_text', 'string' => 'abcdef']],
            ['fr' => 'Générateur'],
            'sku',
            '-',
            'no',
        );
        $validator->validate($command)
            ->shouldBeCalledOnce()
        ;
        $identifierGeneratorRepository
            ->getNextId()
            ->shouldBeCalled()
            ->willReturn(IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'));

        $this->__invoke($command);

        $identifierGeneratorRepository->save(Argument::type(IdentifierGenerator::class))->shouldHaveBeenCalled();
    }
}
