<?php

namespace Pim\Bundle\BatchBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Symfony\Component\Validator\ValidatorInterface;
use Pim\Bundle\BatchBundle\Entity\Job;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Job entity subscriber to update its status given its validation
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateJobStatusSubscriber implements EventSubscriber
{
    protected $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function getSubscribedEvents()
    {
        return array('prePersist', 'preUpdate');
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $this->updateStatus($event);
    }

    public function preUpdate(LifecycleEventArgs $event)
    {
        $this->updateStatus($event);
    }

    private function updateStatus(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if (!$entity instanceof Job) {
            return;
        }

        if ($violations = $this->validator->validate($entity, array('Default', 'Configuration'))->count() === 0) {
            $entity->setStatus(Job::STATUS_READY);
        } else {
            $entity->setStatus(Job::STATUS_DRAFT);
        }
    }
}

