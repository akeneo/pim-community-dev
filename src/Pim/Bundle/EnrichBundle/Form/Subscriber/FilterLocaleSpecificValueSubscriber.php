<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Filter locale specific value subscriber to remove value available in only a set of locales
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterLocaleSpecificValueSubscriber implements EventSubscriberInterface
{
    /** @var string $currentLocale */
    protected $currentLocale;

    /**
     * @param string $currentLocale
     */
    public function __construct($currentLocale)
    {
        $this->currentLocale = $currentLocale;
    }

    /**
     * @return array
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
            if ($this->currentLocale && $value->getAttribute()->isLocaleSpecific()) {
                $availableCodes = $value->getAttribute()->getLocaleSpecificCodes();
                if (!in_array($this->currentLocale, $availableCodes)) {
                    $form->remove($name);
                }
            }
        }
    }
}
