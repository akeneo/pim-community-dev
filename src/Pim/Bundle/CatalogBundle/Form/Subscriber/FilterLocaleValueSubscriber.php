<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * Filter locale value subscriber
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterLocaleValueSubscriber implements EventSubscriberInterface
{
    protected $currentLocale;

    /**
     * @param string $currentLocale
     */
    public function __construct($currentLocale)
    {
        $this->currentLocale = $currentLocale;
    }

    /**
     * @return multitype:string
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
        );
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        foreach ($data as $name => $value) {
            if ($this->currentLocale &&
                $this->isTranslatable($value->getAttribute()) &&
                !$this->isInCurrentLocale($value)) {
                $form->remove($name);
            }
        }
    }

    /**
     * @param Attribute $attribute
     *
     * @return boolean
     */
    private function isTranslatable($attribute)
    {
        return $attribute && $attribute->isTranslatable();
    }

    /**
     * @param Value $value
     *
     * @return boolean
     */
    private function isInCurrentLocale($value)
    {
        return $value->getLocale() && $value->getLocale() === $this->currentLocale;
    }
}
