<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Notification;

use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifierInterface as MailNotification;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Throwable;
use Twig\Environment;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MailResetNotifier
{
    public function __construct(
        private LoggerInterface  $logger,
        private Environment      $twig,
        private MailNotification $mailer
    ) {
    }

    public function notify(UserInterface $user)
    {
        $parameters = ['user' => $user];

        try {
            $txtBody = $this->twig->render('@PimUser/Mail/reset.txt.twig', $parameters);
            $htmlBody = $this->twig->render('@PimUser/Mail/reset.html.twig', $parameters);
            $this->mailer->notify([$user->getEmail()], 'Reset password', $txtBody, $htmlBody);
        } catch (Throwable $exception) {
            $this->logger->error(
                MailResetNotifier::class . ' - Unable to send email : ' . $exception->getMessage(),
                ['Exception' => $exception]
            );
            return;
        }
    }
}
