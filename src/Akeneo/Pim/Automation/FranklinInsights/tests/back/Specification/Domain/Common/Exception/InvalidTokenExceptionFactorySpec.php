<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\InvalidTokenExceptionFactory;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Notifier\InvalidTokenNotifierInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class InvalidTokenExceptionFactorySpec extends ObjectBehavior
{
    public function let(InvalidTokenNotifierInterface $invalidTokenNotifier)
    {
        $this->beConstructedWith($invalidTokenNotifier);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(InvalidTokenExceptionFactory::class);
    }

    public function it_sends_a_notification_when_creating_invalid_token_exception($invalidTokenNotifier)
    {
        $invalidTokenNotifier->notify()->shouldBeCalled();

        $e = new \Exception();
        $this
            ->create($e)
            ->shouldBeAnInstanceOf(DataProviderException::class);
    }
}
