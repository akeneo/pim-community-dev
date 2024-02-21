<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace AkeneoTest\Platform\Acceptance\NotificationBundle\FakeService;

use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifierInterface;
use Webmozart\Assert\Assert;

class FakeMailNotifier implements MailNotifierInterface
{
    private array $emailSent = [];

    public function notify(array $recipients, string $subject, string $txtBody, string $htmlBody, array $options = []): void
    {
        foreach ($recipients as $recipient) {
            $this->emailSent[$recipient][] = $subject;
        }
    }

    public function assertEmailHaveBeenSent(string $addressMail, string $subject): void
    {
        Assert::notEmpty($this->emailSent, 'No email have been sent');
        Assert::keyExists($this->emailSent, $addressMail, 'No email have been sent to "%s"');

        $emailSubjectsSent = $this->emailSent[$addressMail];
        Assert::inArray(
            $subject,
            $emailSubjectsSent,
            sprintf(
                'No email has been sent to "%s" with subject "%s". Got "%s"',
                $addressMail,
                $subject,
                implode(', ', $emailSubjectsSent)
            ),
        );
    }

    public function assertNoEmailHaveBeenSent(): void
    {
        Assert::isEmpty(
            $this->emailSent,
            sprintf('An email have been sent to %s', implode(',', array_keys($this->emailSent)))
        );
    }
}
