<?php
namespace Pim\Bundle\ConfigBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * Locale fallback subscriber
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleFallbackSubscriber implements EventSubscriberInterface
{

    /**
     * Form factory
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * Locales already used as fallback
     * @var array $fallbackLocales
     */
    protected $fallbackLocales;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $factory         Form factory
     * @param array                $fallbackLocales Fallback locales
     */
    public function __construct(FormFactoryInterface $factory = null, $fallbackLocales = null)
    {
        $this->factory = $factory;
        $this->fallbackLocales = $fallbackLocales;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
        );
    }

    /**
     * Method called before set data
     * @param DataEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        if (in_array($data->getCode(), $this->fallbackLocales)) {
            $formField = $form->get('fallback');
            $options   = $formField->getConfig()->getOptions();
            $options['disabled']  = true;
            $options['read_only'] = true;

            $form->add($this->factory->createNamed(
                'fallback',
                $formField->getConfig()->getType(),
                null,
                $options
            ));
        }
    }
}
