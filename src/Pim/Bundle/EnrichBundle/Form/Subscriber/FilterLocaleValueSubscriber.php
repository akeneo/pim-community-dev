<?php

namespace Pim\Bundle\EnrichBundle\Form\Subscriber;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
        );
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
                && $this->isLocalizable($value->getAttribute())
                && !$this->isInCurrentLocale($value)
                && !$this->isInComparisonLocale($value)
            ) {
                $form->remove($name);
            }

            if ($this->isInComparisonLocale($value)) {
                $form->add(
                    $name,
                    'pim_product_value',
                    array(
                        'disabled'     => true,
                        'block_config' => array(
                            'mode' => 'comparison'
                        )
                    )
                );
            }
        }
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return boolean
     */
    protected function isLocalizable(AttributeInterface $attribute = null)
    {
        return $attribute && $attribute->isLocalizable();
    }

    /**
     * @param ProductValueInterface $value
     *
     * @return boolean
     */
    protected function isInCurrentLocale(ProductValueInterface $value)
    {
        return $value->getLocale() && $value->getLocale() === $this->currentLocale;
    }

    /**
     * @param ProductValueInterface $value
     *
     * @return boolean
     */
    protected function isInComparisonLocale(ProductValueInterface $value)
    {
        return $value->getLocale() && $value->getLocale() === $this->comparisonLocale;
    }
}
