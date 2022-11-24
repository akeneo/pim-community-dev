<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Apps\Exception;

use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthenticationException;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidAppAuthenticationExceptionSpec extends ObjectBehavior
{
    public function let(ConstraintViolationListInterface $constraintViolationList): void
    {
        $this->beConstructedWith($constraintViolationList);
    }

    public function it_is_initializable(): void
    {
        $this->beAnInstanceOf(InvalidAppAuthenticationException::class);
    }

    public function it_returns_constraint_violation_list(
        ConstraintViolationListInterface $constraintViolationList
    ): void {
        $constraintViolationList->count()->willReturn(1);
        $constraintViolationList->get(0)->willReturn(
            new ConstraintViolation(
                'a_constraint_violation_message',
                '',
                [],
                '',
                'a_path',
                'invalid'
            )
        );
        $this->getConstraintViolationList()->shouldReturn($constraintViolationList);
    }

    public function it_initializes_empty_message(
        ConstraintViolationListInterface $constraintViolationList
    ): void {
        $constraintViolationList->count()->willReturn(0);
        $this->getMessage()->shouldReturn('');
    }

    public function it_initializes_message(ConstraintViolationListInterface $constraintViolationList): void
    {
        $constraintViolationList->count()->willReturn(2);
        $constraintViolationList->get(0)->willReturn(
            new ConstraintViolation(
                'a_constraint_violation_message',
                '',
                [],
                '',
                'a_path',
                'invalid'
            )
        );
        $constraintViolationList->get(1)->willReturn(
            new ConstraintViolation(
                'another_constraint_violation_message',
                '',
                [],
                '',
                'a_path',
                'invalid'
            )
        );

        $this->beConstructedWith($constraintViolationList);

        $this->getMessage()->shouldReturn('a_constraint_violation_message');
    }
}
