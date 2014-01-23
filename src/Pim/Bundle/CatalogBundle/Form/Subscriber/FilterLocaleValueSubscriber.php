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
    /** @var string $currentLocale */
    protected $currentLocale;

    /** @var string $comparisonLocale */
    protected $comparisonLocale;

    /**
     * @param string $currentLocale
     * @param string $comparisonLocale
     */
    public function __construct($currentLocale, $comparisonLocale)
    {
        $this->currentLocale    = $currentLocale;
        $this->comparisonLocale = $comparisonLocale;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
        ];
    }

    /**
     * @param FormEvent $event
     *
     * @return null
     */
    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        foreach ($data as $name => $value) {
            if ($this->currentLocale
                && $this->isTranslatable($value->getAttribute())
                && !$this->isInCurrentLocale($value)
                && !$this->isInComparisonLocale($value)
            ) {
                $form->remove($name);
            }

            if ($this->isInComparisonLocale($value)) {
                $form->add(
                    $name,
                    'pim_product_value',
                    [
                        'disabled'     => true,
                        'block_config' => [
                            'mode' => 'comparison'
                        ]
                    ]
                );
            }
        }
    }

    /**
     * @param Attribute $attribute
     *
     * @return boolean
     */
    protected function isTranslatable($attribute)
    {
        return $attribute && $attribute->isTranslatable();
    }

    /**
     * @param Value $value
     *
     * @return boolean
     */
    protected function isInCurrentLocale($value)
    {
        return $value->getLocale() && $value->getLocale() === $this->currentLocale;
    }

    /**
     * @param Value $value
     *
     * @return boolean
     */
    protected function isInComparisonLocale($value)
    {
        return $value->getLocale() && $value->getLocale() === $this->comparisonLocale;
    }
}
