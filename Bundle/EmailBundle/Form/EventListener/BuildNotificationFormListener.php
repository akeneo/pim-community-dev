<?php
namespace Oro\Bundle\EmailBundle\Form\EventListener;

use Oro\Bundle\EmailBundle\Entity\Repository\EmailTemplateRepository;
use Oro\Bundle\NotificationBundle\Event\Handler\EmailNotificationHandler;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Persistence\ObjectManager;

class BuildNotificationFormListener implements EventSubscriberInterface
{
    private $om;

    /**
     * Form factory.
     *
     * @var FormFactoryInterface
     */
    private $factory;

    /**
     * Constructor.
     *
     * @param ObjectManager $om
     * @param FormFactoryInterface $factory
     */
    public function __construct(ObjectManager $om, FormFactoryInterface $factory)
    {
        $this->om = $om;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_BIND     => 'preBind'
        );
    }

    /**
     * Removes or adds a state field based on the country set.
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $notification = $event->getData();
        $form = $event->getForm();

        if (null === $notification) {
            return;
        }

        /** @var $notification \Oro\Bundle\NotificationBundle\Entity\EmailNotification */
        $entityName = $notification->getEntityName();

//        if (null === $entityName) {
//            return;
//        }

        if ($form->has('template')) {
            $config = $form->get('template')->getConfig()->getOptions();
            $config['query_builder'] = $this->getTemplateClosure($entityName);

            if (array_key_exists('auto_initialize', $config)) {
                $config['auto_initialize'] = false;
            }

            unset($config['em']);
            unset($config['choice_list']);
            if ($entityName == null) {
                unset($config['choices']);
            }
            $form->add(
                $this->factory->createNamed(
                    'template',
                    'entity',
                    $notification->getTemplate(),
                    $config
                )
            );
        }
    }

    /**
     * Removes or adds a state field based on the country set on submitted form.
     *
     * @param FormEvent $event
     */
    public function preBind(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        $entityName = isset($data['entityName']) ? $data['entityName'] : false;

        if ($entityName !== false) {
            $config = $form->get('template')->getConfig()->getOptions();

            $config['query_builder'] = $this->getTemplateClosure($entityName);
            unset($config['em']);

            if (array_key_exists('auto_initialize', $config)) {
                $config['auto_initialize'] = false;
            }

            $form->add(
                'template',
                'entity',
                $config
            );
        }
    }

    /**
     * @param string $entityName
     * @return callable
     */
    protected function getTemplateClosure($entityName)
    {
        return function (EmailTemplateRepository $templateRepository) use ($entityName) {
            if (is_null($entityName)) {
                return null;
            }
            return $templateRepository->getEntityTemplatesQueryBuilder($entityName);
        };
    }
}
