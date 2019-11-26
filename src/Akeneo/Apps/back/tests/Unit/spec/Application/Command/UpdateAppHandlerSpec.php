<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Command;

use Akeneo\Apps\Application\Command\UpdateAppCommand;
use Akeneo\Apps\Application\Command\UpdateAppHandler;
use Akeneo\Apps\Domain\Exception\ConstraintViolationListException;
use Akeneo\Apps\Domain\Model\ValueObject\ClientId;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Model\ValueObject\UserId;
use Akeneo\Apps\Domain\Model\Write\App;
use Akeneo\Apps\Domain\Persistence\Repository\AppRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateAppHandlerSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $validator,
        AppRepository $repository
    ): void {
        $this->beConstructedWith($validator, $repository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(UpdateAppHandler::class);
    }

    public function it_updates_an_app(
        $validator,
        $repository
    ): void {
        $command = new UpdateAppCommand('magento', 'Pimgento', FlowType::DATA_DESTINATION);

        $violations = new ConstraintViolationList([]);
        $validator->validate($command)->willReturn($violations);

        $app = new App('magento', 'Magento Connector', FlowType::OTHER, 42, new UserId(50));
        $repository->findOneByCode('magento')->willReturn($app);
        $repository->update(Argument::type(App::class))->shouldBeCalled();

        $this->handle($command);
    }

    public function it_throws_a_constraint_exception_when_something_is_invalid(
        $validator,
        $repository,
        ConstraintViolationInterface $violation
    ): void {
        $command = new UpdateAppCommand('magento', 'Pimgento', 'Wrong flow type');

        $violations = new ConstraintViolationList([$violation->getWrappedObject()]);
        $validator->validate($command)->willReturn($violations);

        $repository->findOneByCode('magento')->shouldNotBeCalled();
        $repository->update(Argument::type(App::class))->shouldNotBeCalled();

        $this
            ->shouldThrow(ConstraintViolationListException::class)
            ->during('handle', [$command]);
    }
}
