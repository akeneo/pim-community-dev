<?php

namespace Oro\Bundle\CalendarBundle\Notification;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\EmailBundle\Entity\Repository\EmailTemplateRepository;
use Oro\Bundle\EmailBundle\Model\EmailTemplateInterface;
use Oro\Bundle\NotificationBundle\Processor\EmailNotificationProcessor;

class RemindersSender
{
    const EMAIL_TEMPLATE_NAME = 'calendar_reminder';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EmailNotificationProcessor
     */
    protected $notificationProcessor;

    /**
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * Constructor
     *
     * @param EntityManager              $em
     * @param EmailNotificationProcessor $notificationProcessor
     */
    public function __construct(EntityManager $em, EmailNotificationProcessor $notificationProcessor)
    {
        $this->em                    = $em;
        $this->notificationProcessor = $notificationProcessor;
    }

    /**
     * Sets a logger
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Sends reminders for calendar events
     *
     * @throws \RuntimeException
     */
    public function send()
    {
        if ($this->logger === null) {
            $this->logger = new NullLogger();
        }

        $currentTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $events      = $this->getEventsToRemind($currentTime);
        if (empty($events)) {
            // there are no events require a reminder for now
            $this->logger->notice('Exit because nothing to remind.');

            return;
        }

        $template = $this->getTemplate();
        $processorLogger = new RaiseExceptionLogger($this->logger);
        $failedEventIds = array();
        foreach ($events as $event) {
            try {
                $toEmail = $event->getCalendar()->getOwner()->getEmail();
                $this->notificationProcessor->process(
                    $event,
                    array(new EmailNotificationAdapter($template, $toEmail)),
                    $processorLogger
                );
            } catch (RaiseExceptionLoggerException $processorEx) {
                // we do not need to write this type of exception to a log because it was already done
                $failedEventIds[] = $event->getId();
            } catch (\Exception $ex) {
                $failedEventIds[] = $event->getId();
                $this->logger->error(
                    sprintf(
                        'A reminder sending failed. Calendar event id: %d. Error: %s.',
                        $event->getId(),
                        $ex->getMessage()
                    ),
                    array('exception' => $ex)
                );
            }
        }

        $sentCount = count($events) - count($failedEventIds);
        if ($sentCount > 0) {
            $this->logger->notice(sprintf('Sent %d reminder(s).', $sentCount));
        }

        if (!empty($failedEventIds)) {
            throw new \RuntimeException(
                sprintf(
                    'The sending of reminders failed for the following calendar events: %s.',
                    implode(', ', $failedEventIds)
                )
            );
        }
    }

    /**
     * Returns a list of calendar events for which a remind notification need to be sent.
     *
     * @param \DateTime $currentTime The current date/time in UTC
     * @return CalendarEvent[]
     */
    protected function getEventsToRemind($currentTime)
    {
        $repo = $this->em->getRepository('OroCalendarBundle:CalendarEvent');
        $qb   = $repo->createQueryBuilder('e')
            ->select('e, c, u')
            ->innerJoin('e.calendar', 'c')
            ->innerJoin('c.owner', 'u')
            ->where('e.remindAt <= :current AND e.start > :current AND e.reminded = :reminded')
            ->setParameter('current', $currentTime)
            ->setParameter('reminded', false);

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns an email template should be used to prepare a reminder message
     *
     * @return EmailTemplateInterface
     * @throws \RuntimeException when a template does not exist
     */
    protected function getTemplate()
    {
        /** @var EmailTemplateRepository $repo */
        $repo   = $this->em->getRepository('OroEmailBundle:EmailTemplate');
        $result = $repo->findByName(self::EMAIL_TEMPLATE_NAME);
        if (!$result) {
            throw new \RuntimeException(sprintf('"%s" email template was not found.', self::EMAIL_TEMPLATE_NAME));
        }

        return $result;
    }
}
