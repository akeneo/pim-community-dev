<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Notification;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectRepositoryInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Factory\AbstractNotificationFactory;
use Akeneo\Platform\Bundle\NotificationBundle\Factory\NotificationFactoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Factory that creates a notification for project calculation from a job instance.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectCalculationNotificationFactory extends AbstractNotificationFactory implements NotificationFactoryInterface
{
    /** @var string[] */
    protected $notificationTypes;

    /** @var string */
    protected $notificationClass;

    /** @var ProjectRepositoryInterface */
    protected $projectRepository;

    /** @var PresenterInterface */
    protected $datePresenter;

    public function __construct(
        ProjectRepositoryInterface $projectRepository,
        PresenterInterface $datePresenter,
        array $notificationTypes,
        $notificationClass
    ) {
        $this->projectRepository = $projectRepository;
        $this->datePresenter     = $datePresenter;
        $this->notificationTypes = $notificationTypes;
        $this->notificationClass = $notificationClass;
    }

    /**
     * {@inheritdoc}
     */
    public function create($jobExecution)
    {
        if (!$jobExecution instanceof JobExecution) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Akeneo\Tool\Component\Batch\Model\JobExecution, "%s" provided',
                    ClassUtils::getClass($jobExecution)
                )
            );
        }

        $notification = new $this->notificationClass();
        $type = $jobExecution->getJobInstance()->getType();
        $status = $this->getJobStatus($jobExecution);
        $projectCode = $jobExecution->getJobParameters()->get('project_code');

        $project = $this->projectRepository->findOneBy(['code' => $projectCode]);
        $userLocale = $project->getOwner()->getUiLocale();
        $formattedDate = $this->datePresenter->present(
            $project->getDueDate(),
            ['locale' => $userLocale->getCode()]
        );

        $notification
            ->setType($status)
            ->setMessage(sprintf('teamwork_assistant.notification.%s.%s', $type, $status))
            ->setMessageParams(['%project_label%' => $project->getLabel(), '%due_date%' => $formattedDate])
            ->setRoute('pim_enrich_job_tracker_show')
            ->setRouteParams(['id' => $jobExecution->getId()])
            ->setContext(['actionType' => $type]);

        return $notification;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return in_array($type, $this->notificationTypes);
    }
}
