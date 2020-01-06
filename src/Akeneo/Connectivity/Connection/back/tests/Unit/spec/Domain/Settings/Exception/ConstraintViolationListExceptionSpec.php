<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Settings\Exception;

use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConstraintViolationListExceptionSpec extends ObjectBehavior
{
    public function let(ConstraintViolationListInterface $constraintViolationList)
    {
        $this->beConstructedWith($constraintViolationList);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ConstraintViolationListException::class);
    }

    public function it_returns_the_constraint_violation_list($constraintViolationList)
    {
        $this->getConstraintViolationList()->shouldReturn($constraintViolationList);
    }
}
