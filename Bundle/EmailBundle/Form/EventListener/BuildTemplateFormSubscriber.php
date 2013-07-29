<?php
namespace Oro\Bundle\EmailBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\EmailBundle\Entity\Repository\EmailTemplateRepository;
use Oro\Bundle\NotificationBundle\Event\Handler\EmailNotificationHandler;

class BuildTemplateFormSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Form factory.
     *
     * @var FormFactoryInterface
     */
    private $factory;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param FormFactoryInterface $factory
     */
    public function __construct(EntityManager $em, FormFactoryInterface $factory)
    {
        $this->em = $em;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT   => 'preSubmit'
        );
    }

    /**
     * Removes or adds a template field based on the entity set.
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

        $entityName = $notification->getEntityName();

        if ($entityName) {
            if ($form->has('template')) {
                $config = $form->get('template')->getConfig()->getOptions();
                unset($config['choice_list']);
                unset($config['choices']);
            } else {
                $config = array();
            }

            $config['selectedEntity'] = $entityName;
            $config['query_builder'] = $this->getTemplateClosure($entityName);

            if (array_key_exists('auto_initialize', $config)) {
                $config['auto_initialize'] = false;
            }

            $form->add(
                $this->factory->createNamed(
                    'template',
                    'oro_email_template_list',
                    $notification->getTemplate(),
                    $config
                )
            );
        }
    }

    /**
     * Removes or adds a template field based on the entity set on submitted form.
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $entityName = isset($data['entityName']) ? $data['entityName'] : false;

        if ($entityName) {
            $config = $form->get('template')->getConfig()->getOptions();
            unset($config['choice_list']);
            unset($config['choices']);

            $config['selectedEntity'] = $entityName;
            $config['query_builder'] = $this->getTemplateClosure($entityName);

            if (array_key_exists('auto_initialize', $config)) {
                $config['auto_initialize'] = false;
            }

            $form->add(
                $this->factory->createNamed(
                    'template',
                    'oro_email_template_list',
                    null,
                    $config
                )
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
            return $templateRepository->getEntityTemplatesQueryBuilder($entityName);
        };
    }
}
