<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\UserManagement\Bundle\Notification;

use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifierInterface;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class MailResetNotifierSpec extends ObjectBehavior
{
    function let(
        LoggerInterface       $logger,
        Environment           $twig,
        MailNotifierInterface $mailer
    )
    {
        $this->beConstructedWith($logger, $twig, $mailer, 'null://localhost?encryption=tls&auth_mode=login&username=foo&password=bar');
    }

    function it_notifies(User $user, $mailer, $twig)
    {
        // Given
        $this->given($user, $twig);

        // When
        $this->notify($user);

        // Then
        $mailer->notify(
            ['email'],
            'Reset password',
            'textBody',
            'htmlBody'
        )->shouldBeCalled();
    }

    function it_should_log_error_if_notification_failed(User $user, $mailer, $twig, $logger)
    {
        // Given
        $this->given($user, $twig);

        // When
        $mailer->notify(
            Argument::any(),
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->willThrow(\Throwable::class);

        $this->notify($user);

        // Then
        $logger->error(Argument::any(), Argument::any())->shouldBeCalled();
    }

    private function given(User $user, Environment $twig)
    {
        $user->getEmail()->willReturn('email');
        $twig->render('@PimUser/Mail/reset.txt.twig', ['user' => $user])->willReturn('textBody');
        $twig->render('@PimUser/Mail/reset.html.twig', ['user' => $user])->willReturn('htmlBody');
    }

}
