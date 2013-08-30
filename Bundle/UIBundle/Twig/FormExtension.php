<?php

namespace Oro\Bundle\UIBundle\Twig;

use Twig_Environment;
use Symfony\Component\Form\FormView;
use Symfony\Component\EventDispatcher\EventDispatcher;

use Oro\Bundle\UIBundle\Event\Events;
use Oro\Bundle\UIBundle\Event\BeforeFormRenderEvent;

class FormExtension extends \Twig_Extension
{
    const EXTENSION_NAME = 'oro_form_process';

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'oro_form_process' => new \Twig_Function_Method(
                $this,
                'process',
                array(
                    'needs_environment' => true
                )
            )
        );
    }

    /**
     * @param \Twig_Environment $environment
     * @param array $data
     * @param FormView $form
     * @return array
     */
    public function process(Twig_Environment $environment, array $data, FormView $form)
    {
        $event = new BeforeFormRenderEvent($form, $data, $environment);
        $this->eventDispatcher->dispatch(Events::BEFORE_UPDATE_FORM_RENDER, $event);

        return $event->getFormData();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::EXTENSION_NAME;
    }
}
