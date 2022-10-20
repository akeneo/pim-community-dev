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
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateGeneratorHandlerSpec extends ObjectBehavior
{
    public function let(
        IdentifierGeneratorRepository $identifierGeneratorRepository,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith($identifierGeneratorRepository, $validator);
    }

    public function it_implements_create_generator_handler(): void
    {
        $this->shouldImplement(CreateGeneratorHandler::class);
    }

    public function it_must_call_save_repository(IdentifierGeneratorRepository $identifierGeneratorRepository, ValidatorInterface $validator): void
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
        $validator->validate($command)
            ->shouldBeCalledOnce()
            ->willReturn(new ConstraintViolationList())
        ;

        $this->__invoke($command);

        $identifierGeneratorRepository->save(Argument::type(IdentifierGenerator::class))->shouldHaveBeenCalled();
    }
}
