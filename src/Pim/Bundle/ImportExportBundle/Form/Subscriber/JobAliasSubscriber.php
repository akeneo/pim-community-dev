<?php

namespace Pim\Bundle\ImportExportBundle\Form\Subscriber;

use Oro\Bundle\BatchBundle\Entity\JobInstance;

use Symfony\Component\Form\FormEvent;

use Oro\Bundle\BatchBundle\Connector\ConnectorRegistry;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 *
 * Enter description here ...
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class JobAliasSubscriber implements EventSubscriberInterface
{
    /**
     * @var ConnectorRegistry $connectorRegistry
     */
    protected $connectorRegistry;

    /**
     * Constructor
     *
     * @param ConnectorRegistry $connectorRegistry
     */
    public function __construct(ConnectorRegistry $connectorRegistry)
    {
        $this->connectorRegistry = $connectorRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_BIND => 'postBind'
        );
    }

    public function postBind(FormEvent $event)
    {
        /** @var JobInstance $jobInstance */
        $jobInstance = $event->getData();

        if (null === $jobInstance || $jobInstance->getId()) {
            return;
        }

        $form = $event->getForm();
        $connector = $form->get('connector');
        var_dump($connector->getData()); die;
        $alias = $form->get('alias');
//         $jobInstance = new JobInstance($connector, 'export', $alias); // FIXME: Hardcoded constant

        $event->setData($jobInstance);
//         var_dump(get_class($jobInstance));
//         $jobAlias = $event->getForm()->get('alias')->getData();
//         var_dump($jobAlias);


//         $jobs = $this->connectorRegistry->getJobs('export'); // FIXME: Hardcoded constant to remove

    }
}
