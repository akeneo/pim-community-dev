<?php

namespace Akeneo\Platform\Bundle\NotificationBundle\Email;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MailNotifier implements MailNotifierInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param string[] $recipients
     */
    public function notify(
        array $recipients,
        string $subject,
        string $txtBody,
        string $htmlBody,
        array $options = []
    ): void {
        if (empty($recipients)) {
            return;
        }

        $email = (new Email())
            ->subject($subject)
            ->text($txtBody)
            ->html($htmlBody);

        foreach ($recipients as $recipient) {
            $email->addBcc($recipient);
        }

        try {
            $this->mailer->send($email);

            $this->logger->info(
                'Mail sent',
                [
                    'Subject' => $email->getSubject(),
                    'Recipients' => $email->getBcc(),
                ]
            );
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Cannot sent mail : %s', $e->getMessage()),
                [
                    'Subject' => $email->getSubject(),
                    'Recipients' => $email->getBcc(),
                ]
            );
        }
    }
}
