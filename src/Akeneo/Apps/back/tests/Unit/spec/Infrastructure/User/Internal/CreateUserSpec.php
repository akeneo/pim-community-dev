<?php
declare(strict_types=1);

namespace spec\Akeneo\Apps\Infrastructure\User\Internal;

use Akeneo\Apps\Application\Service\CreateUserInterface;
use Akeneo\Apps\Domain\Model\ValueObject\UserId;
use Akeneo\Apps\Infrastructure\User\Internal\CreateUser;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateUserSpec extends ObjectBehavior
{
    public function let(
        SimpleFactoryInterface $userFactory,
        ObjectUpdaterInterface $userUpdater,
        ValidatorInterface $validator,
        SaverInterface $userSaver
    ): void {
        $this->beConstructedWith($userFactory, $userUpdater, $validator, $userSaver);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(CreateUser::class);
        $this->shouldImplement(CreateUserInterface::class);
    }

    function it_creates_a_user($userFactory, $userUpdater, $validator, $userSaver, User $user): void
    {
        $userFactory->create()->willReturn($user);
        $userUpdater->update(
            $user,
            [
                'username' => 'foo',
                'password' => 'foo',
                'first_name' => 'bar',
                'last_name' => 'APP',
                'email' => 'foo.bar@akeneo.com'
            ]
        )->shouldBeCalled();
        $violations = new ConstraintViolationList([]);
        $validator->validate($user)->willReturn($violations);
        $userSaver->save($user)->shouldBeCalled();
        $user->getId()->willReturn(1);

        $userId = $this->execute(
            'foo',
            'bar',
            'APP',
            'pwd123',
            'foo.bar@akeneo.com'
        );
        $userId->shouldBeAnInstanceOf(UserId::class);
        $userId->id()->shouldReturn(1);
    }

    function it_prevents_to_create_a_not_valid_user($userFactory, $userUpdater, $validator, $userSaver, User $user): void
    {
        $userFactory->create()->willReturn($user);
        $userUpdater->update(
            $user,
            [
                'username' => 'foo',
                'password' => 'foo',
                'first_name' => 'bar',
                'last_name' => 'APP',
                'email' => 'foo.bar@akeneo.com'
            ]
        )->shouldBeCalled();
        $violations = new ConstraintViolationList([
            new ConstraintViolation('wrong', 'wrong', [], 'wrong', 'path', 'wrong'),
            new ConstraintViolation('wrong2', 'wrong2', [], 'wrong2', 'path2', 'wrong2'),
        ]);
        $validator->validate($user)->willReturn($violations);
        $userSaver->save(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(
                new \LogicException(
                    'The user creation failed :' . PHP_EOL .
                    'path: wrong' . PHP_EOL .
                    'path2: wrong2'
                )
            )
            ->during('execute', ['foo', 'bar', 'APP', 'pwd123', 'foo.bar@akeneo.com']);
    }
}
